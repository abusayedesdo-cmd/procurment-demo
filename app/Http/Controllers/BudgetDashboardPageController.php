<?php

namespace App\Http\Controllers;

class BudgetDashboardPageController extends Controller
{
    public function index()
    {
        return view('budget.dashboard');
    }
}