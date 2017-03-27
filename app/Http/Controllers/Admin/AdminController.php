<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reports;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function reports()
    {
        $reports = Reports::latest()->with(['link', 'user'])->paginate(20);

        meta()->setMeta('Reported Links');

        return view('admin.reports', compact('reports'));
    }
}
