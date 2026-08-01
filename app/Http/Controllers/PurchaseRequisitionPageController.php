<?php

namespace App\Http\Controllers;

class PurchaseRequisitionPageController extends Controller
{
    public function index()
    {
        return view('purchase-requisitions.index');
    }

    public function create()
    {
        return view('purchase-requisitions.create');
    }

    public function show(int $id)
    {
        return view('purchase-requisitions.show', ['id' => $id]);
    }
}
