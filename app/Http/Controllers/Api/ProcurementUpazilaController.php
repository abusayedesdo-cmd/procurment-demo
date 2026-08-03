<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcurementUpazila;
use Illuminate\Http\Request;

class ProcurementUpazilaController extends Controller
{
    public function index(Request $request)
    {
        $query = ProcurementUpazila::query();

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->integer('district_id'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('name')->get(['id', 'name', 'district_id']),
        ]);
    }
}
