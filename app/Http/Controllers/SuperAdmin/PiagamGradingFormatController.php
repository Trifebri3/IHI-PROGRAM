<?php
namespace App\Http\Controllers\SuperAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class PiagamGradingFormatController extends Controller {
    public function index() { return view('superadmin.piagam.grading_formats.index'); }
    public function create() { return view('superadmin.piagam.grading_formats.create'); }
    public function store(Request $request) {}
    public function show($id) {}
    public function edit($id) { return view('superadmin.piagam.grading_formats.edit', compact('id')); }
    public function update(Request $request, $id) {}
    public function destroy($id) {}
}
