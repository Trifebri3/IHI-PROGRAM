<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BiodataController extends Controller
{
    public function index()
    {
        // Pastikan hanya Super Admin yang bisa mengakses via Middleware/Gate nanti
        return view('superadmin.biodata.index');
    }
}
