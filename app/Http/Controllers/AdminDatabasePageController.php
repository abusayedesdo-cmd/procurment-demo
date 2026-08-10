<?php

namespace App\Http\Controllers;

class AdminDatabasePageController extends Controller
{
    public function index()
    {
        return view('admin.database');
    }
}
