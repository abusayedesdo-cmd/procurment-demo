<?php

namespace App\Http\Controllers;

class ModulePageController extends Controller
{
    public function index()
    {
        return view('modules.index');
    }

    public function show(string $slug)
    {
        return view('modules.show', ['slug' => $slug]);
    }
}
