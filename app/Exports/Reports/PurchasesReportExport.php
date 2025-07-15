<?php

namespace App\Exports\Reports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PurchasesReportExport implements FromArray, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data['purchases'];
    }

    public function headings(): array
    {
        return [
            ['LAPORAN PEMBELIAN'],
            ["Periode: " . $this->data['startDate']->format('d/m/Y') . " - " . $this->data['endDate']->format('d/m/Y')],
            [],
            [
                'NO INVOICE',
                'SUPPLIER',
                'TANGGAL',
                'PRODUK',
                'JUMLAH',
                'TOTAL'
            ]
        ];
    }

    public function map($purchase): array
    {
        $productDetails = collect($purchase['purchase_details'] ?? [])->map(function($detail) {
            return ($detail['product']['name'] ?? 'Produk dihapus') . ' (' . $detail['quantity'] . ')';
        })->implode(', ');

        return [
            $purchase['invoice_number'],
            $purchase['supplier_name'],
            \Carbon\Carbon::parse($purchase['date'])->format('d/m/Y'),
            $productDetails,
            collect($purchase['purchase_details'] ?? [])->sum('quantity'),
            'Rp ' . number_format($purchase['total_price'], 0, ',', '.')
        ];
    }

    public function title(): string
    {
        return 'Pembelian';
    }
}