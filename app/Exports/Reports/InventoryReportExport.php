<?php

namespace App\Exports\Reports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;

class InventoryReportExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $reportDate;

    public function __construct($data)
    {
        $this->data = $data;
        $this->reportDate = now()->format('d/m/Y H:i');
    }

    public function array(): array
    {
        return $this->data['products']->toArray();
    }

    public function headings(): array
    {
        return [
            ['LAPORAN INVENTORI'],
            ["Per Tanggal: {$this->reportDate}"],
            [],
            [
                'KODE',
                'PRODUK',
                'KATEGORI',
                'STOK',
                'HARGA BELI',
                'HARGA JUAL',
                'NILAI INVENTORI'
            ]
        ];
    }

    public function map($product): array
    {
        return [
            $product['id'],
            $product['name'],
            $product['category']['name'] ?? '-',
            $product['stock'],
            'Rp ' . number_format($product['cost_price'], 0, ',', '.'),
            'Rp ' . number_format($product['selling_price'], 0, ',', '.'),
            'Rp ' . number_format($product['stock'] * $product['cost_price'], 0, ',', '.')
        ];
    }

    public function title(): string
    {
        return 'Inventori';
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