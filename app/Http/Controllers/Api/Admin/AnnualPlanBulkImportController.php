<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Item;
use App\Models\ProcurementAnnualPlan;
use App\Models\ProcurementCategory;
use App\Models\ProcurementDistrict;
use App\Models\Project;
use App\Models\PurchaseCommittee;
use App\Models\Unit;
use App\Services\AnnualPlanExcelImporter;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin: import the whole "Compile Annual Procurement Plan" Excel in one go.
 *
 * For every project in the file the Admin chooses: use a project that already exists, create a new
 * one (with its empty Sub-Committee, exactly like Projects -> New Project), or skip it. Each chosen
 * project gets one Annual Plan for the fiscal year (an existing one is reused) and its packages.
 * preview saves nothing; commit saves everything in a single transaction.
 */
class AnnualPlanBulkImportController extends Controller
{
    public function preview(Request $request, AnnualPlanExcelImporter $importer)
    {
        [$rows, $found] = $this->readUpload($request, $importer);
        $fyStart = $this->fiscalStartYear($request);
        $systemProjects = Project::orderBy('name')->get(['id', 'name'])->toArray();
        $config = $this->config($request);
        $groupMap = $this->groupMap($request);
        $lookups = $this->lookups();

        $projects = [];
        $unmatched = [];
        $totals = ['projects' => 0, 'packages' => 0, 'grand_total' => 0.0];

        foreach ($found['projects'] as $p) {
            $setting = $config[$p['key']] ?? $this->defaultSetting($p, $importer, $systemProjects);
            $entry = $p + ['setting' => $setting, 'existing_plan' => null, 'import_rows' => 0, 'import_total' => 0, 'with_warnings' => 0];

            if ($setting['action'] !== 'skip') {
                $built = $importer->build($rows, $p['key'], $lookups, $groupMap);
                $entry['import_rows'] = $built['summary']['rows'];
                $entry['import_total'] = $built['summary']['grand_total'];
                $entry['with_warnings'] = $built['summary']['with_warnings'];
                foreach ($built['unmatched_groups'] as $u) {
                    $k = $u['key'];
                    $unmatched[$k] ??= ['key' => $k, 'text' => $u['text'], 'count' => 0, 'category' => $u['category'], 'category_id' => $u['category_id']];
                    $unmatched[$k]['count'] += $u['count'];
                }
                $totals['projects']++;
                $totals['packages'] += $built['summary']['rows'];
                $totals['grand_total'] += $built['summary']['grand_total'];

                if ($setting['action'] === 'existing' && ! empty($setting['project_id'])) {
                    $plan = $this->findPlan((int) $setting['project_id'], $fyStart);
                    $entry['existing_plan'] = $plan ? ['id' => $plan->id, 'packages' => $plan->packages()->count()] : null;
                }
            }
            $projects[] = $entry;
        }

        $totals['grand_total'] = round($totals['grand_total'], 2);

        return response()->json([
            'success' => true,
            'projects' => $projects,
            'system_projects' => $systemProjects,
            'skipped_no_project' => $found['skipped_no_project'],
            'unmatched_groups' => array_values($unmatched),
            'groups' => ChartOfAccount::orderBy('code')->get(['id', 'code', 'name', 'category_id']),
            'totals' => $totals,
            'fiscal_year' => "{$fyStart}-" . ($fyStart + 1),
        ]);
    }

    public function commit(Request $request, AnnualPlanExcelImporter $importer)
    {
        [$rows, $found] = $this->readUpload($request, $importer);
        $fyStart = $this->fiscalStartYear($request);
        $config = $this->config($request);
        $groupMap = $this->groupMap($request);
        $lookups = $this->lookups();
        $numbers = app(NumberGeneratorService::class);
        $districts = ProcurementDistrict::get(['id', 'name'])->keyBy(fn ($d) => AnnualPlanExcelImporter::norm($d->name));
        $systemProjects = Project::orderBy('name')->get(['id', 'name'])->toArray();

        $result = ['projects_created' => 0, 'plans_created' => 0, 'plans_reused' => 0, 'packages' => 0, 'skipped' => 0];
        $userId = $request->user()->id;

        DB::transaction(function () use ($found, $rows, $config, $groupMap, $lookups, $numbers, $districts, $systemProjects, $importer, $fyStart, $userId, &$result) {
            foreach ($found['projects'] as $p) {
                $setting = $config[$p['key']] ?? $this->defaultSetting($p, $importer, $systemProjects);
                if ($setting['action'] === 'skip') {
                    $result['skipped']++;
                    continue;
                }

                // ---- project ----
                if ($setting['action'] === 'existing') {
                    $project = Project::findOrFail((int) ($setting['project_id'] ?? 0));
                } else {
                    $name = trim((string) ($setting['name'] ?? ''));
                    if ($name === '') {
                        abort(422, 'Project "' . $p['name'] . '" has no name for the new project.');
                    }
                    // Never create a second project with the same name — reuse it.
                    $project = Project::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])->first();
                    if (! $project) {
                        $project = Project::create(['name' => mb_substr($name, 0, 255), 'is_active' => true]);
                        PurchaseCommittee::create([
                            'name' => $project->name . ' — Sub-Committee',
                            'type' => 'sub',
                            'project_id' => $project->id,
                        ]);
                        $result['projects_created']++;
                    }
                }

                // ---- plan (reuse the project's plan for this fiscal year if there is one) ----
                $plan = $this->findPlan($project->id, $fyStart);
                if ($plan) {
                    $result['plans_reused']++;
                } else {
                    $plan = ProcurementAnnualPlan::create([
                        'plan_type' => 'annual',
                        'title' => $project->name,
                        'project_name' => $project->name,
                        'project_id' => $project->id,
                        'district_id' => $districts[AnnualPlanExcelImporter::norm($p['district'])]->id ?? null,
                        'working_area' => $p['upazila'] !== '' ? $p['upazila'] : null,
                        'fiscal_year_start' => "{$fyStart}-07-01",
                        'fiscal_year_end' => ($fyStart + 1) . '-06-30',
                        'donor_name' => $p['donor'] !== '' ? $p['donor'] : null,
                        'prepared_by' => $userId,
                        'status' => 'draft',
                    ]);
                    $result['plans_created']++;
                }

                // ---- packages ----
                $start = ((int) $plan->packages()->max('sl_no')) + 1;
                $built = $importer->build($rows, $p['key'], $lookups, $groupMap, $start);
                $result['packages'] += $importer->savePackages($plan, $built['rows'], $numbers);
            }
        });

        return response()->json(['success' => true] + $result, 201);
    }

    /** What to do with a project when the Admin has not chosen yet. */
    protected function defaultSetting(array $p, AnnualPlanExcelImporter $importer, array $systemProjects): array
    {
        $id = $importer->suggestSystemProject($p['name'], $systemProjects);

        return $id
            ? ['action' => 'existing', 'project_id' => $id, 'name' => $p['short_name']]
            : ['action' => 'new', 'project_id' => null, 'name' => $p['short_name']];
    }

    protected function findPlan(int $projectId, int $fyStart): ?ProcurementAnnualPlan
    {
        return ProcurementAnnualPlan::withoutGlobalScopes()
            ->where('project_id', $projectId)
            ->where('plan_type', 'annual')
            ->whereDate('fiscal_year_start', "{$fyStart}-07-01")
            ->orderBy('id')
            ->first();
    }

    protected function fiscalStartYear(Request $request): int
    {
        $y = (int) $request->input('fy_start_year', 2026);

        return ($y >= 2000 && $y <= 2100) ? $y : 2026;
    }

    /** @return array{0: array, 1: array} */
    protected function readUpload(Request $request, AnnualPlanExcelImporter $importer): array
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:20480']);

        try {
            $rows = $importer->readRows($request->file('file')->getRealPath());
        } catch (\Throwable $e) {
            abort(422, 'Could not read the Excel file: ' . $e->getMessage());
        }

        return [$rows, $importer->projects($rows)];
    }

    /** @return array<string, array{action:string, project_id:?int, name:?string}> */
    protected function config(Request $request): array
    {
        $raw = $request->input('config');
        $cfg = is_string($raw) ? json_decode($raw, true) : $raw;
        if (! is_array($cfg)) {
            return [];
        }

        $clean = [];
        foreach ($cfg as $key => $c) {
            $action = in_array($c['action'] ?? '', ['existing', 'new', 'skip'], true) ? $c['action'] : 'skip';
            $clean[(string) $key] = [
                'action' => $action,
                'project_id' => isset($c['project_id']) && $c['project_id'] !== '' ? (int) $c['project_id'] : null,
                'name' => isset($c['name']) ? trim((string) $c['name']) : null,
            ];
        }

        return $clean;
    }

    protected function groupMap(Request $request): array
    {
        $raw = $request->input('group_map');
        $map = is_string($raw) ? json_decode($raw, true) : $raw;

        return is_array($map) ? $map : [];
    }

    protected function lookups(): array
    {
        return [
            'categories' => ProcurementCategory::get(['id', 'name'])->toArray(),
            'groups' => ChartOfAccount::get(['id', 'code', 'name', 'category_id'])->toArray(),
            'units' => Unit::pluck('name')->all(),
            'items' => Item::get(['id', 'name', 'chart_of_account_id'])->toArray(),
        ];
    }
}
