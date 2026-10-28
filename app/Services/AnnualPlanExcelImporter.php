<?php

namespace App\Services;

use App\Models\ProcurementAnnualPlan;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Reads the "Compile Annual Procurement Plan" Excel (one row per package, many projects in one sheet)
 * and turns the rows of ONE project into Annual-Plan packages.
 *
 * Reading (readRows) and mapping (build) are separate: build() only needs plain arrays, so it can be
 * tested without a database or a spreadsheet file.
 */
class AnnualPlanExcelImporter
{
    /** Excel unit spellings -> the name used in the Units master list. */
    protected const UNIT_SYNONYMS = [
        'piece' => 'Piece', 'pieces' => 'Piece', 'pcs' => 'Piece', 'pc' => 'Piece',
        'number' => 'Number', 'no' => 'Number', 'nos' => 'Number',
        'pair' => 'Pair', 'packet' => 'Packet', 'set' => 'Set', 'job' => 'Job', 'service' => 'Service',
        'box' => 'Box', 'kilogram' => 'Kilogram', 'kg' => 'Kilogram', 'gram' => 'Gram', 'lot' => 'Lot',
        'ls' => 'Lot', 'ream' => 'Ream', 'bundle' => 'Bundle', 'carton' => 'Carton', 'bag' => 'Bag',
        'bottle' => 'Bottle', 'dozen' => 'Dozen', 'dazen' => 'Dozen', 'liter' => 'Liter', 'roll' => 'Roll',
        'meter' => 'Meter',
    ];

    public static function clean(mixed $value): string
    {
        $s = (string) ($value ?? '');
        $s = str_replace(["\u{00A0}", "\u{200B}", "\u{200C}", "\u{200D}", "\r", "\n", "\t"], ' ', $s);
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

        return trim($s, " \t,");
    }

    public static function norm(mixed $value): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower(self::clean($value))) ?? '';
    }

    protected static function num(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $s = preg_replace('/[^0-9.\-]/', '', (string) $value);

        return is_numeric($s) ? (float) $s : 0.0;
    }

    /**
     * @return array<int,array<string,mixed>> one entry per data row of the first sheet
     */
    public function readRows(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $grid = $spreadsheet->getSheet(0)->toArray(null, true, true, false);

        // Header row = the first row (within the top 10) that has a "Project Name" cell.
        $headerAt = null;
        foreach (array_slice($grid, 0, 10, true) as $i => $r) {
            foreach ($r as $cell) {
                if (self::norm($cell) === 'projectname') {
                    $headerAt = $i;
                    break 2;
                }
            }
        }
        if ($headerAt === null) {
            throw new \RuntimeException('Could not find the "Project Name" header row in the first sheet.');
        }

        $col = [];
        foreach ($grid[$headerAt] as $c => $label) {
            $k = self::norm($label);
            if ($k !== '' && ! isset($col[$k])) {
                $col[$k] = $c;     // first occurrence wins (the second "Total Cost" is the monthly sum)
            }
            if ($k === 'remarks') {
                $col['remarks'] = $c;
            }
        }
        foreach (['projectname', 'itemdescription', 'unitcost', 'procurementmethod'] as $need) {
            if (! isset($col[$need])) {
                throw new \RuntimeException("Column \"{$need}\" not found in the header row.");
            }
        }

        $rows = [];
        for ($i = $headerAt + 1, $n = count($grid); $i < $n; $i++) {
            $r = $grid[$i];
            if (! is_numeric($r[$col['sl'] ?? 0] ?? null)) {
                continue;     // blank / total / note rows
            }
            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $months[] = self::num($r[$col['procurementmethod'] + $m] ?? null);
            }
            $rows[] = [
                'excel_row' => $i + 1,
                'project' => self::clean($r[$col['projectname']] ?? ''),
                'donor' => self::clean($r[$col['donorname'] ?? -1] ?? ''),
                'district' => self::clean($r[$col['district'] ?? -1] ?? ''),
                'upazila' => self::clean($r[$col['upazila'] ?? -1] ?? ''),
                'category' => self::clean($r[$col['category'] ?? -1] ?? ''),
                'sub_category' => self::clean($r[$col['subcategory'] ?? -1] ?? ''),
                'item' => self::clean($r[$col['itemdescription']] ?? ''),
                'unit' => self::clean($r[$col['unit'] ?? -1] ?? ''),
                'qty' => self::num($r[$col['qty'] ?? -1] ?? null),
                'unit_cost' => self::num($r[$col['unitcost']] ?? null),
                'total' => self::num($r[$col['totalcost'] ?? -1] ?? null),
                'method' => self::clean($r[$col['procurementmethod']] ?? ''),
                'months' => $months,
                'remarks' => self::clean($r[$col['remarks'] ?? -1] ?? ''),
            ];
        }

        return $rows;
    }

    /**
     * Projects found in the file (rows with no project name are not listed — they are counted separately).
     *
     * @return array{projects: array<int,array<string,mixed>>, skipped_no_project: int}
     */
    public function projects(array $rows): array
    {
        $byKey = [];
        $skipped = 0;
        foreach ($rows as $r) {
            $key = self::norm($r['project']);
            if ($key === '' || $key === '0') {
                $skipped++;
                continue;
            }
            $byKey[$key]['names'][$r['project']] = ($byKey[$key]['names'][$r['project']] ?? 0) + 1;
            $byKey[$key]['rows'] = ($byKey[$key]['rows'] ?? 0) + 1;
            $byKey[$key]['total'] = ($byKey[$key]['total'] ?? 0) + $r['total'];
            foreach (['donor', 'district', 'upazila'] as $f) {
                if ($r[$f] !== '' && $r[$f] !== '0') {
                    $byKey[$key][$f][$r[$f]] = ($byKey[$key][$f][$r[$f]] ?? 0) + 1;
                }
            }
        }

        $projects = [];
        foreach ($byKey as $key => $p) {
            arsort($p['names']);
            $top = function (string $f) use ($p): string {
                if (empty($p[$f])) {
                    return '';
                }
                arsort($p[$f]);

                return (string) array_key_first($p[$f]);
            };
            $full = (string) array_key_first($p['names']);
            $projects[] = [
                'key' => $key,
                'name' => $full,
                'short_name' => self::shortName($full),
                'donor' => $top('donor'),
                'district' => $top('district'),
                'upazila' => $top('upazila'),
                'rows' => $p['rows'],
                'total' => round($p['total'], 2),
            ];
        }
        usort($projects, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        return ['projects' => $projects, 'skipped_no_project' => $skipped];
    }

    /** "ALO — inclusive education and learning outcomes" -> "ALO" (text before a spaced dash). */
    public static function shortName(string $full): string
    {
        $parts = preg_split('/\s+[—–-]\s+/u', $full, 2);
        $short = trim($parts[0] ?? '');

        return $short !== '' ? $short : $full;
    }

    /**
     * Which project already in the system is this file project? Exact name first, then a system name
     * the file name starts with (e.g. system "ALO" <-> file "ALO — inclusive ...").
     *
     * @param  array<int,array{id:int,name:string}>  $systemProjects
     */
    public function suggestSystemProject(string $fileName, array $systemProjects): ?int
    {
        $full = self::norm($fileName);
        $short = self::norm(self::shortName($fileName));
        $best = null;
        $bestLen = 0;
        foreach ($systemProjects as $sp) {
            $n = self::norm($sp['name']);
            if ($n === '') {
                continue;
            }
            if ($n === $full || $n === $short) {
                return (int) $sp['id'];
            }
            if (strlen($n) >= 2 && (str_starts_with($full, $n) || str_starts_with($n, $short)) && strlen($n) > $bestLen) {
                $best = (int) $sp['id'];
                $bestLen = strlen($n);
            }
        }

        return $best;
    }

    /** Best guess of which project in the file belongs to this plan (by name). */
    public function suggestProject(array $projects, ?string $planProjectName): ?string
    {
        $want = self::norm($planProjectName);
        if ($want === '') {
            return null;
        }
        foreach ($projects as $p) {
            if ($p['key'] === $want) {
                return $p['key'];
            }
        }
        foreach ($projects as $p) {
            if (str_starts_with($p['key'], $want) || str_starts_with($want, $p['key'])) {
                return $p['key'];
            }
        }

        return null;
    }

    /**
     * Saves built rows as packages of $plan (same shape the "Add New Package" form saves:
     * Current Year, monthly entries July -> June). Call inside a DB transaction.
     */
    public function savePackages(ProcurementAnnualPlan $plan, array $builtRows, NumberGeneratorService $numbers): int
    {
        foreach ($builtRows as $r) {
            $package = $plan->packages()->create([
                'sl_no' => $r['sl_no'],
                'procurement_category_id' => $r['category_id'],
                'chart_of_account_id' => $r['group_id'],
                'budgeted_head' => $r['item'],
                'item_id' => $r['item_id'],
                'unit' => $r['unit'] !== '' ? mb_substr($r['unit'], 0, 100) : null,
                'remarks' => $r['remarks'] !== '' ? $r['remarks'] : null,
                'package_number' => $numbers->nextUnique('package', 'PKG-', 'procurement_plan_packages', 'package_number'),
            ]);

            $package->syncPeriods([
                'current_year' => [
                    'granularity' => 'month',
                    'entries' => array_map(fn ($u) => ['no_of_unit' => $u, 'rate' => $r['rate']], $r['units']),
                ],
            ]);
        }

        return count($builtRows);
    }

    /**
     * @param  array  $lookups ['categories'=>[['id','name']], 'groups'=>[['id','code','name','category_id']],
     *                         'units'=>['Piece',...], 'items'=>[['id','name','chart_of_account_id']]]
     * @param  array  $groupMap  user's choice for Sub-Category texts that could not be matched:
     *                           [norm(text) => chart_of_account_id | '' (= no group)]
     * @return array{rows: array, unmatched_groups: array, summary: array}
     */
    public function build(array $rows, string $projectKey, array $lookups, array $groupMap = [], int $startSl = 1): array
    {
        $categories = [];
        foreach ($lookups['categories'] as $c) {
            $categories[strtolower(trim($c['name']))] = $c;
        }
        $groupsById = [];
        $groupsByCode = [];
        foreach ($lookups['groups'] as $g) {
            $groupsById[$g['id']] = $g;
            $groupsByCode[strtoupper(trim($g['code']))] = $g;
        }
        $unitNames = [];
        foreach ($lookups['units'] as $u) {
            $unitNames[strtolower(trim($u))] = trim($u);
        }
        $itemIndex = [];
        foreach ($lookups['items'] as $it) {
            $itemIndex[$it['chart_of_account_id'] . '|' . self::norm($it['name'])] = $it['id'];
        }

        $out = [];
        $unmatched = [];
        $sl = $startSl;
        $skippedNoItem = 0;
        $grand = 0.0;

        foreach ($rows as $r) {
            if (self::norm($r['project']) !== $projectKey) {
                continue;
            }
            if ($r['item'] === '') {
                $skippedNoItem++;
                continue;
            }

            $warn = [];

            // ---- Category (anything other than Goods / Works / Services is treated as Services) ----
            $catKey = strtolower(trim($r['category']));
            $category = $categories[$catKey] ?? null;
            if (! $category) {
                $category = $categories['services'] ?? reset($categories);
                $warn[] = 'Category "' . ($r['category'] ?: 'blank') . '" treated as ' . $category['name'];
            }

            // ---- Procurement Group (Sub-Category) ----
            $sub = $r['sub_category'];
            $subKey = self::norm($sub);
            $group = null;
            if (array_key_exists($subKey, $groupMap)) {
                $mapped = $groupMap[$subKey];
                $group = ($mapped !== '' && $mapped !== null) ? ($groupsById[(int) $mapped] ?? null) : null;
                $decided = true;
            } else {
                $decided = false;
                if (preg_match('/^([A-Za-z]\d{2})\b/', $sub, $m)) {
                    $group = $groupsByCode[strtoupper($m[1])] ?? null;
                }
                if (! $group && strlen($subKey) >= 4) {
                    foreach ($groupsById as $g) {
                        if (self::norm($g['name']) === $subKey) {
                            $group = $g;
                            break;
                        }
                    }
                    if (! $group) {
                        foreach ($groupsById as $g) {
                            $gk = self::norm($g['name']);
                            if (strlen($subKey) >= 6 && (str_contains($gk, $subKey) || str_contains($subKey, $gk))) {
                                $group = $g;
                                break;
                            }
                        }
                    }
                }
            }
            if ($group) {
                if ((int) $group['category_id'] !== (int) $category['id']) {
                    foreach ($categories as $c) {
                        if ((int) $c['id'] === (int) $group['category_id']) {
                            $warn[] = 'Category changed from ' . $category['name'] . ' to ' . $c['name'] . ' to match group ' . $group['code'];
                            $category = $c;
                            break;
                        }
                    }
                }
            } elseif (! $decided) {
                $unmatched[$subKey]['text'] = $sub;
                $unmatched[$subKey]['count'] = ($unmatched[$subKey]['count'] ?? 0) + 1;
                $unmatched[$subKey]['category'] = $unmatched[$subKey]['category'] ?? $category['name'];
                $unmatched[$subKey]['category_id'] = $unmatched[$subKey]['category_id'] ?? $category['id'];
                $warn[] = 'Procurement group not matched';
            }

            // ---- Unit ----
            $unitRaw = $r['unit'];
            $unit = '';
            if ($unitRaw !== '' && ! is_numeric($unitRaw)) {
                $canon = self::UNIT_SYNONYMS[strtolower($unitRaw)] ?? $unitRaw;
                $unit = $unitNames[strtolower($canon)] ?? $canon;
            } elseif ($unitRaw !== '') {
                $warn[] = 'Unit "' . $unitRaw . '" is not a name — left blank';
            }

            // ---- Months: units = month amount / unit cost (same shape the entry form saves) ----
            $monthSum = array_sum($r['months']);
            $rate = $r['unit_cost'];
            if ($rate <= 0) {
                $rate = 0;
                foreach ($r['months'] as $a) {
                    if ($a > 0) {
                        $rate = $a;
                        break;
                    }
                }
            }
            $units = [];
            $importTotal = 0.0;
            foreach ($r['months'] as $a) {
                $u = $rate > 0 ? round($a / $rate, 2) : 0.0;
                $units[] = $u;
                $importTotal += round($u * round($rate, 2), 2);
            }
            if ($monthSum <= 0) {
                $warn[] = 'No month amount in the file — imported with 0';
            } elseif (abs($importTotal - $monthSum) > 1) {
                $warn[] = 'Total after import ' . number_format($importTotal, 2) . ' differs from the file ' . number_format($monthSum, 2);
            }
            if ($monthSum > 0 && abs($monthSum - $r['total']) > 1) {
                $warn[] = 'Months add up to ' . number_format($monthSum, 2) . ' but Total Cost says ' . number_format($r['total'], 2);
            }

            $itemId = $group ? ($itemIndex[$group['id'] . '|' . self::norm($r['item'])] ?? null) : null;

            $grand += $importTotal;
            $out[] = [
                'sl_no' => $sl++,
                'excel_row' => $r['excel_row'],
                'item' => mb_substr($r['item'], 0, 255),
                'item_id' => $itemId,
                'category_id' => $category['id'],
                'category' => $category['name'],
                'group_id' => $group['id'] ?? null,
                'group' => $group ? ($group['code'] . ' — ' . $group['name']) : null,
                'sub_category_text' => $sub,
                'unit' => $unit,
                'qty' => $r['qty'],
                'rate' => round($rate, 2),
                'units' => $units,
                'excel_total' => $r['total'],
                'import_total' => round($importTotal, 2),
                'remarks' => $r['remarks'],
                'warnings' => $warn,
            ];
        }

        return [
            'rows' => $out,
            'unmatched_groups' => array_map(fn ($k, $v) => ['key' => (string) $k] + $v, array_keys($unmatched), $unmatched),
            'summary' => [
                'rows' => count($out),
                'skipped_no_item' => $skippedNoItem,
                'grand_total' => round($grand, 2),
                'with_warnings' => count(array_filter($out, fn ($x) => $x['warnings'])),
            ],
        ];
    }
}
