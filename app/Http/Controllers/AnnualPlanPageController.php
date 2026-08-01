<?php

namespace App\Http\Controllers;

class AnnualPlanPageController extends Controller
{
    public function index()
    {
        return view('annual-plan.index');
    }

    public function show($id)
    {
        return view('annual-plan.show', ['id' => (int) $id]);
    }
}