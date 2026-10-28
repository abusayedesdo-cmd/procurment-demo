<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Item;
use App\Models\ProcurementAnnualPlan;
use App\Models\ProcurementCategory;
use App\Models\Unit;
use App\Services\AnnualPlanExcelImporter;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Import the "Compile Annual Procurement Plan" Excel into ONE annual plan.
 * preview = read-only (nothing is saved); commit = saves the packages.
 * The file is sent again with the commit request, so no temp file has to be kept on the server.
 */
class AnnualPlanImportController extends Controller
{
    public function preview(Request $request, ProcurementAnnualPlan $procurementAnnualPlan, AnnualPlanExcelImporter $importer)
    {
        [$rows, $projects] = $this->readUpload($request, $importer);

        $response = [
            'projects' => $projects['projects'],
            'skipped_no_project' => $projects['skipped_no_project'],
            'suggested' => $importer->suggestProject($projects['projects'], $procurementAnnualPlan->project_name),
            'existing_packages' => $procurementAnnualPlan->packages()->count(),
        ];

        $projectKey = (string) $request->input('project', '');
        if ($projectKey !== '') {
            $built = $importer->build(
                $rows,
                $projectKey,
                $this->lookups(),
                $this->groupMap($request),
                $this->nextSl($procurementAnnualPlan)
            );
            $response += $built;
            $response['groups'] = ChartOfAccount::orderBy('code')->get(['id', 'code', 'name', 'category_id']);
        }

        return response()->json(['success' => true] + $response);
    }

    public function commit(Request $request, ProcurementAnnualPlan $procurementAnnualPlan, AnnualPlanExcelImporter $importer)
    {
        $request->validate(['project' => 'required|string']);
        [$rows] = $this->readUpload($request, $importer);

        $built = $importer->build(
            $rows,
            (string) $request->input('project'),
            $this->lookups(),
            $this->groupMap($request),
            $this->nextSl($procurementAnnualPlan)
        );

        if (! count($built['rows'])) {
            return response()->json(['success' => false, 'message' => 'No rows to import for this project.'], 422);
        }

        DB::transaction(fn () => $importer->savePackages($procurementAnnualPlan, $built['rows'], app(NumberGeneratorService::class)));

        return response()->json([
            'success' => true,
            'imported' => count($built['rows']),
            'grand_total' => $built['summary']['grand_total'],
        ], 201);
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

    protected function groupMap(Request $request): array
    {
        $raw = $request->input('group_map');
        $map = is_string($raw) ? json_decode($raw, true) : $raw;

        return is_array($map) ? $map : [];
    }

    protected function nextSl(ProcurementAnnualPlan $plan): int
    {
        return ((int) $plan->packages()->max('sl_no')) + 1;
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
