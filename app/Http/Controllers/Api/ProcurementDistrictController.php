<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcurementDistrict;

class ProcurementDistrictController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => ProcurementDistrict::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
