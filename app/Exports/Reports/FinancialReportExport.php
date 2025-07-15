<?php

namespace App\Exports\Reports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;

class FinancialReportExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
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
        return $this->data['financialData'];
    }

    public function headings(): array
    {
        return [
            ['LAPORAN KEUANGAN'],
            ["Periode: {$this->startDate} - {$this->endDate}"],
            [],
            [
                'TANGGAL',
                'PENDAPATAN',
                'PENGELUARAN',
                'LABA'
            ]
        ];
    }

    public function map($row): array
    {
        return [
            $row['date'],
            'Rp ' . number_format($row['income'], 0, ',', '.'),
            'Rp ' . number_format($row['expense'], 0, ',', '.'),
            'Rp ' . number_format($row['profit'], 0, ',', '.')
        ];
    }

    public function title(): string
    {
        return 'Keuangan';
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