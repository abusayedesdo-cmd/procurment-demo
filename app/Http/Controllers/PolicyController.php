<?php

namespace App\Http\Controllers;

use App\Models\ProcurementPolicy;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function index()
    {
        $policies = ProcurementPolicy::orderBy('group')->orderBy('id')->get()->groupBy('group');
        return view('settings.policies', ['policies' => $policies]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'values'   => 'required|array',
            'values.*' => 'required|numeric|min:0',
        ]);

        foreach ($data['values'] as $key => $value) {
            ProcurementPolicy::set($key, (float) $value);
        }

        return back()->with('ok', 'Policy values updated.');
    }
}
