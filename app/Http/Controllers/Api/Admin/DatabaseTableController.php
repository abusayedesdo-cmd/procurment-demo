<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Generic, schema-driven CRUD for the Super Admin > Data Manager screen.
 *
 * Instead of hand-writing a controller per table (68 tables and counting),
 * this reads real column/type/foreign-key metadata straight from
 * information_schema and builds validation + forms from that. All routes
 * are behind 'role:admin' (see routes/api.php).
 *
 * SAFETY NOTES
 * - $table is always checked against Schema::hasTable() before it's used
 *   in any query — never trust the route param directly.
 * - HIDDEN_TABLES blocks framework/system tables (migrations, sessions,
 *   tokens, jobs) and 'users' (which has its own guarded screen — see
 *   Api\UserController — so passwords stay hashed and the "keep at least
 *   one admin" guards stay in force).
 * - created_at / updated_at are managed here (not exposed as editable
 *   fields); the primary key is never accepted from the request body.
 */
class DatabaseTableController extends Controller
{
    /** Tables that never appear in the Data Manager, even to an Admin. */
    private const HIDDEN_TABLES = [
        'migrations',
        'password_reset_tokens',
        'personal_access_tokens',
        'sessions',
        'failed_jobs',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'users', // managed on its own screen — /admin/users
    ];

    /** Preferred columns to use as the human-readable label in FK dropdowns. */
    private const LABEL_CANDIDATES = [
        'name', 'title', 'pr_number', 'rfq_number', 'wo_number', 'noa_number',
        'agreement_number', 'notice_number', 'minutes_number', 'email',
        'label', 'key', 'value',
    ];

    private function database(): string
    {
        return DB::getDatabaseName();
    }

    /** Whitelist check — the only thing that makes it safe to interpolate $table into raw identifiers below. */
    private function assertTableAllowed(string $table): void
    {
        if (in_array($table, self::HIDDEN_TABLES, true) || ! \Illuminate\Support\Facades\Schema::hasTable($table)) {
            abort(404, 'Table not found.');
        }
    }

    private function primaryKey(string $table): string
    {
        $pk = DB::selectOne("
            SELECT k.COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE k
            JOIN information_schema.TABLE_CONSTRAINTS t
              ON t.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND t.TABLE_SCHEMA = k.TABLE_SCHEMA AND t.TABLE_NAME = k.TABLE_NAME
            WHERE t.CONSTRAINT_TYPE = 'PRIMARY KEY' AND k.TABLE_SCHEMA = ? AND k.TABLE_NAME = ?
            LIMIT 1
        ", [$this->database(), $table]);

        return $pk->COLUMN_NAME ?? 'id';
    }

    private function foreignKeys(string $table): array
    {
        $rows = DB::select("
            SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$this->database(), $table]);

        $out = [];
        foreach ($rows as $r) {
            $out[$r->COLUMN_NAME] = [
                'table' => $r->REFERENCED_TABLE_NAME,
                'column' => $r->REFERENCED_COLUMN_NAME,
            ];
        }

        return $out;
    }

    private function columnMeta(string $table): array
    {
        $rows = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        ", [$this->database(), $table]);

        $fks = $this->foreignKeys($table);
        $pk = $this->primaryKey($table);

        return array_map(function ($r) use ($fks, $pk) {
            $isAutoIncrement = str_contains($r->EXTRA, 'auto_increment');
            $isTimestamp = in_array($r->COLUMN_NAME, ['created_at', 'updated_at', 'deleted_at'], true);

            $inputType = 'text';
            $enumOptions = null;

            if (str_starts_with($r->COLUMN_TYPE, 'enum(')) {
                $inputType = 'enum';
                preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $r->COLUMN_TYPE, $m);
                $enumOptions = $m[1];
            } elseif ($r->DATA_TYPE === 'tinyint' && $r->COLUMN_TYPE === 'tinyint(1)') {
                $inputType = 'boolean';
            } elseif (in_array($r->DATA_TYPE, ['int', 'bigint', 'smallint', 'mediumint'], true)) {
                $inputType = isset($fks[$r->COLUMN_NAME]) ? 'select' : 'integer';
            } elseif (in_array($r->DATA_TYPE, ['decimal', 'float', 'double'], true)) {
                $inputType = 'decimal';
            } elseif (in_array($r->DATA_TYPE, ['text', 'longtext', 'mediumtext'], true)) {
                $inputType = 'textarea';
            } elseif ($r->DATA_TYPE === 'date') {
                $inputType = 'date';
            } elseif (in_array($r->DATA_TYPE, ['datetime', 'timestamp'], true)) {
                $inputType = 'datetime';
            }

            return [
                'name' => $r->COLUMN_NAME,
                'data_type' => $r->DATA_TYPE,
                'input_type' => $inputType,
                'enum_options' => $enumOptions,
                'nullable' => $r->IS_NULLABLE === 'YES',
                'has_default' => $r->COLUMN_DEFAULT !== null,
                'is_primary' => $r->COLUMN_NAME === $pk,
                'is_auto_increment' => $isAutoIncrement,
                'is_timestamp' => $isTimestamp,
                'is_editable' => ! $isAutoIncrement && ! $isTimestamp && $r->COLUMN_NAME !== $pk,
                'foreign_key' => $fks[$r->COLUMN_NAME] ?? null,
            ];
        }, $rows);
    }

    private function labelColumn(string $table): string
    {
        $columns = array_column(DB::select("
            SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ", [$this->database(), $table]), 'COLUMN_NAME');

        foreach (self::LABEL_CANDIDATES as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return $this->primaryKey($table);
    }

    /** GET /admin/tables — list every manageable table with a row count. */
    public function index()
    {
        $tables = array_column(DB::select("
            SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'
        ", [$this->database()]), 'TABLE_NAME');

        $tables = array_values(array_diff($tables, self::HIDDEN_TABLES));
        sort($tables);

        $data = array_map(function ($table) {
            return [
                'name' => $table,
                'label' => Str::headline($table),
                'row_count' => DB::table($table)->count(),
            ];
        }, $tables);

        return response()->json(['success' => true, 'data' => $data]);
    }

    /** GET /admin/tables/{table}/columns — column + FK metadata for building the form. */
    public function columns(string $table)
    {
        $this->assertTableAllowed($table);

        return response()->json([
            'success' => true,
            'data' => [
                'table' => $table,
                'primary_key' => $this->primaryKey($table),
                'columns' => $this->columnMeta($table),
            ],
        ]);
    }

    /**
     * GET /admin/tables/{table}/options — {id,label} pairs for a table, used
     * to populate FK <select> fields elsewhere in the Data Manager.
     */
    public function options(string $table, Request $request)
    {
        $this->assertTableAllowed($table);

        $pk = $this->primaryKey($table);
        $label = $this->labelColumn($table);

        $rows = DB::table($table)
            ->select([$pk . ' as id', $label . ' as label'])
            ->orderBy($pk, 'desc')
            ->limit($request->integer('limit', 300))
            ->get();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /** GET /admin/tables/{table}/rows — paginated rows with optional free-text search. */
    public function rows(string $table, Request $request)
    {
        $this->assertTableAllowed($table);

        $pk = $this->primaryKey($table);
        $columns = $this->columnMeta($table);
        $perPage = min($request->integer('per_page', 25), 100);

        $query = DB::table($table);

        if ($request->filled('q')) {
            $term = '%' . $request->string('q') . '%';
            $searchable = array_filter($columns, fn ($c) => in_array($c['input_type'], ['text', 'textarea'], true));

            if (! empty($searchable)) {
                $query->where(function ($qq) use ($searchable, $term) {
                    foreach ($searchable as $c) {
                        $qq->orWhere($c['name'], 'like', $term);
                    }
                });
            }
        }

        $total = (clone $query)->count();
        $rows = $query->orderBy($pk, 'desc')
            ->forPage($request->integer('page', 1), $perPage)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'meta' => [
                'current_page' => $request->integer('page', 1),
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    /** Build Laravel validation rules from live column metadata. */
    private function rulesFor(array $columns, bool $isUpdate): array
    {
        $rules = [];

        foreach ($columns as $c) {
            if (! $c['is_editable']) {
                continue;
            }

            $required = ! $c['nullable'] && ! $c['has_default'];
            $set = [];
            $set[] = $isUpdate ? 'sometimes' : ($required ? 'required' : 'nullable');
            if (! $required && ! $isUpdate) {
                $set[] = 'nullable';
            }

            switch ($c['input_type']) {
                case 'integer':
                    $set[] = 'integer';
                    if ($c['foreign_key']) {
                        $set[] = 'exists:' . $c['foreign_key']['table'] . ',' . $c['foreign_key']['column'];
                    }
                    break;
                case 'select':
                    $set[] = 'integer';
                    if ($c['foreign_key']) {
                        $set[] = 'exists:' . $c['foreign_key']['table'] . ',' . $c['foreign_key']['column'];
                    }
                    break;
                case 'decimal':
                    $set[] = 'numeric';
                    break;
                case 'boolean':
                    $set[] = 'boolean';
                    break;
                case 'date':
                    $set[] = 'date';
                    break;
                case 'datetime':
                    $set[] = 'date';
                    break;
                case 'enum':
                    $set[] = Rule::in($c['enum_options'] ?? []);
                    break;
                default:
                    $set[] = 'string';
            }

            $rules[$c['name']] = $set;
        }

        return $rules;
    }

    /** Cast validated request values into DB-ready values per column type. */
    private function castForStorage(array $validated, array $columns): array
    {
        $byName = [];
        foreach ($columns as $c) {
            $byName[$c['name']] = $c;
        }

        $out = [];
        foreach ($validated as $key => $value) {
            $col = $byName[$key] ?? null;
            if (! $col) {
                continue;
            }

            if ($value === '' || $value === null) {
                $out[$key] = null;
                continue;
            }

            $out[$key] = match ($col['input_type']) {
                'boolean' => (bool) $value,
                'integer', 'select' => (int) $value,
                'decimal' => (float) $value,
                'datetime' => str_replace('T', ' ', $value) . (strlen($value) === 16 ? ':00' : ''),
                default => $value,
            };
        }

        return $out;
    }

    /** POST /admin/tables/{table}/rows */
    public function store(string $table, Request $request)
    {
        $this->assertTableAllowed($table);

        $columns = $this->columnMeta($table);
        $validated = $request->validate($this->rulesFor($columns, false));
        $insert = $this->castForStorage($validated, $columns);

        $hasCreatedAt = collect($columns)->firstWhere('name', 'created_at');
        $hasUpdatedAt = collect($columns)->firstWhere('name', 'updated_at');
        if ($hasCreatedAt) {
            $insert['created_at'] = now();
        }
        if ($hasUpdatedAt) {
            $insert['updated_at'] = now();
        }

        $pk = $this->primaryKey($table);
        $id = DB::table($table)->insertGetId($insert, $pk);

        return response()->json([
            'success' => true,
            'message' => 'Row created successfully',
            'data' => DB::table($table)->where($pk, $id)->first(),
        ], 201);
    }

    /** PUT /admin/tables/{table}/rows/{id} */
    public function update(string $table, string $id, Request $request)
    {
        $this->assertTableAllowed($table);

        $pk = $this->primaryKey($table);
        $columns = $this->columnMeta($table);

        $row = DB::table($table)->where($pk, $id)->first();
        if (! $row) {
            abort(404, 'Row not found.');
        }

        $validated = $request->validate($this->rulesFor($columns, true));
        $update = $this->castForStorage($validated, $columns);

        if (empty($update)) {
            return response()->json(['success' => true, 'message' => 'Nothing to update', 'data' => $row]);
        }

        if (collect($columns)->firstWhere('name', 'updated_at')) {
            $update['updated_at'] = now();
        }

        DB::table($table)->where($pk, $id)->update($update);

        return response()->json([
            'success' => true,
            'message' => 'Row updated successfully',
            'data' => DB::table($table)->where($pk, $id)->first(),
        ]);
    }

    /** DELETE /admin/tables/{table}/rows/{id} */
    public function destroy(string $table, string $id)
    {
        $this->assertTableAllowed($table);
        $pk = $this->primaryKey($table);

        $row = DB::table($table)->where($pk, $id)->first();
        if (! $row) {
            abort(404, 'Row not found.');
        }

        try {
            DB::table($table)->where($pk, $id)->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            // Most likely a foreign-key constraint from another table
            // referencing this row (e.g. deleting a Vendor that already
            // has Quotations). Surface a clear message instead of a raw
            // SQL error.
            return response()->json([
                'success' => false,
                'message' => 'This row can\'t be deleted because other records still reference it.',
            ], 422);
        }

        return response()->json(['success' => true, 'message' => 'Row deleted successfully']);
    }
}
