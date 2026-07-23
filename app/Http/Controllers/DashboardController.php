<?php

namespace App\Http\Controllers;

use App\Models\ContractAward;
use App\Models\ProcurementPlan;
use App\Models\PurchaseRequisition;
use Illuminate\Support\Facades\Auth;

/**
 * Minimal session-auth shell. The real UI lives in the separate Next.js
 * frontend (consuming the /api/* routes via Sanctum) — this view is just
 * a lightweight status/landing page for anyone hitting the Laravel app
 * directly, and confirms the logged-in session is valid.
 */
class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'user' => Auth::user(),
            'draftPrs' => PurchaseRequisition::where('status', 'draft')->count(),
            'pendingPrs' => PurchaseRequisition::whereIn('status', ['reviewed', 'checked'])->count(),
            'approvedPrs' => PurchaseRequisition::where('status', 'approved')->count(),
            'activePlans' => ProcurementPlan::whereIn('status', ['planned', 'ongoing'])->count(),
            'contractsAwarded' => ContractAward::count(),
        ]);
    }
}
