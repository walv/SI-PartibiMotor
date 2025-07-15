<?php

namespace App\Exports\Reports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromArray, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $startDate;
    protected $endDate;

    public function __construct($data)
    {
        $this->data = $data;
        $this->startDate = $data['startDate']->format('d/m/Y');
        $this->endDate = $data['endDate']->format('d/m/Y');
    }

    public function array(): array
    {
        return $this->data['sales']->toArray();
    }

    public function headings(): array
    {
        return [
            ['LAPORAN PENJUALAN'],
            ["Periode: {$this->startDate} - {$this->endDate}"],
            [],
            [
                'NO INVOICE',
                'TANGGAL',
                'PELANGGAN',
                'PRODUK',
                'JUMLAH',
                'TOTAL'
            ]
        ];
    }

    public function map($sale): array
{
    // Konversi ke array jika belum
    $saleArray = is_array($sale) ? $sale : $sale->toArray();
    
    // Handle saleDetails
    $productDetails = '';
    $totalQuantity = 0;
    
    if (isset($saleArray['sale_details'])) {
        $productDetails = collect($saleArray['sale_details'])->map(function($detail) {
            return ($detail['product']['name'] ?? 'Produk tidak ditemukan') . ' (' . $detail['quantity'] . ')';
        })->implode(', ');
        
        $totalQuantity = collect($saleArray['sale_details'])->sum('quantity');
    }

    return [
        $saleArray['invoice_number'] ?? '',
        isset($saleArray['date']) ? \Carbon\Carbon::parse($saleArray['date'])->format('d/m/Y') : '',
        $saleArray['customer_name'] ?? '',
        $productDetails,
        $totalQuantity,
        'Rp ' . number_format($saleArray['total_price'] ?? 0, 0, ',', '.')
    ];
}

    public function title(): string
    {
        return 'Penjualan';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['italic' => true]],
            4 => ['font' => ['bold' => true]],
        ];
    }
}