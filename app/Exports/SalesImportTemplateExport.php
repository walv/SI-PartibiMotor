<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesImportTemplateExport implements FromArray, WithHeadings
{
    /**
    * Menentukan header tabel di file Excel
    * @return array
    */
    public function headings(): array
    {
        return [
            'Nomor Invoice',
            'Nama Pelanggan',
            'Produk (JSON)',
            'Jasa (JSON)',
        ];
    }

    /**
    * Menentukan contoh data untuk template
    * @return array
    */
    public function array(): array
    {
        return [
            // Contoh data untuk panduan
            ['INV-202504210001', 'Pelanggan A', '[{"id":1,"quantity":2},{"id":2,"quantity":1}]', '[{"id":1,"price":10000}]'],
            ['INV-202504210002', 'Pelanggan B', '[{"id":3,"quantity":1}]', '[{"id":2,"price":20000}]'],
        ];
    }
}
