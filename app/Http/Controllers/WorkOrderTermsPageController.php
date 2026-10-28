<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;

/** Page shell for editing a Work Order's Terms & Conditions (data comes from /api/work-orders/{id}/terms). */
class WorkOrderTermsPageController extends Controller
{
    public function edit(WorkOrder $workOrder)
    {
        return view('work-orders.terms', ['workOrder' => $workOrder]);
    }
}