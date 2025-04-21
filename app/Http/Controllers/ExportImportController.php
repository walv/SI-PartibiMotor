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

    public function export(Request $request)
    {
        // Validasi input bulan dan tahun
    $request->validate([
        'month' => 'required|numeric|min:1|max:12',
        'year' => 'required|numeric|min:2000|max:' . date('Y'),
    ]);

    $month = $request->input('month');
    $year = $request->input('year');

    // Ekspor data penjualan berdasarkan bulan dan tahun
    return Excel::download(new SalesExport($month, $year), 'laporan_penjualan_' . $month . '_' . $year . '.xlsx');
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
