<?php

namespace App\Http\Controllers;

class ProfilePageController extends Controller
{
    public function show()
    {
        return view('profile.show');
    }
}