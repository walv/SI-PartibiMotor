<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Exports\SalesExport;
use App\Imports\SalesImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesImportTemplateExport;

class ExportImportController extends Controller
{
    public function index()
    {
        return view('exportimport.exportimport');
    }

    public function export()
    {
        return Excel::download(new SalesExport, 'sales.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        try {
            Excel::import(new SalesImport, $request->file('file'));
            return redirect()->route('sales.exportimport')->with('success', 'Data berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->route('sales.exportimport')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function downloadTemplate()
    {
        return Excel::download(new SalesImportTemplateExport, 'template_import_sales.xlsx');
    }
}
