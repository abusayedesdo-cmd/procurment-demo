<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequisition;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $stageIdx = $user->prStage();

        $rows = $stageIdx === null
            ? collect()
            : PurchaseRequisition::with('items')
                ->where('rejected', false)->where('stage', $stageIdx)->latest('pr_date')->get();

        return view('approvals', ['rows' => $rows, 'roleLabel' => $user->roleLabel()]);
    }
}
