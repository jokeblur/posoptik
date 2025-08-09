<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class SimpleTemplateExport implements FromArray
{
    private $type;

    public function __construct($type = 'frame')
    {
        $this->type = $type;
    }

    public function array(): array
    {
        if ($this->type === 'lensa') {
            return [
                ['Kode Lensa', 'Merk Lensa', 'Type', 'Index', 'Coating', 'Harga Beli', 'Harga Jual', 'Stok', 'Cabang', 'Sales', 'Tipe Stok', 'Catatan'],
                ['L00001', 'Essilor', 'Single Vision', '1.56', 'Anti-Reflective', 200000, 300000, 20, 'Cabang Utama', 'John Sales', 'Ready Stock', 'Lensa premium kualitas tinggi'],
                ['L00002', 'Hoya', 'Progressive', '1.67', 'Blue Cut', 400000, 600000, 15, 'Cabang Utama', 'Jane Sales', 'Custom Order', 'Lensa progresif untuk presbyopia'],
                ['', '', '', '', '', '', '', '', '', '', '', '']
            ];
        } else {
            return [
                ['Kode Frame', 'Merk Frame', 'Jenis Frame', 'Harga Beli', 'Harga Jual', 'Stok', 'Cabang', 'Sales'],
                ['FR000001', 'Ray-Ban', 'Sunglasses', 500000, 750000, 10, 'Cabang Utama', 'John Sales'],
                ['FR000002', 'Oakley', 'Sport', 300000, 450000, 15, 'Cabang Utama', 'Jane Sales'],
                ['', '', '', '', '', '', '', '']
            ];
        }
    }
} 