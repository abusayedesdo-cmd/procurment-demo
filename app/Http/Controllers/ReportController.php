<?php

namespace App\Http\Controllers;

use App\Models\ProcurementCase;
use App\Models\PurchaseRequisition;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports', [
            'totalPrs'   => PurchaseRequisition::count(),
            'completed'  => ProcurementCase::where('current_step', '>=', 23)->count(),
            'contracted' => ProcurementCase::where('current_step', '>=', 17)->sum('amount'),
            'cases'      => ProcurementCase::latest()->get(),
        ]);
    }
}
