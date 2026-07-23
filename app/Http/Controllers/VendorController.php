<?php

namespace App\Http\Controllers;

use App\Models\Vendor;

class VendorController extends Controller
{
    public function index()
    {
        return view('vendors', ['vendors' => Vendor::orderBy('name')->get()]);
    }
}
