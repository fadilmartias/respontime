<?php

namespace App\Exports;

use App\Models\Pemeriksaan;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PemeriksaanExport implements FromQuery, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */

    private $startDate;
    private $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return Pemeriksaan::query()->whereBetween('created_at', [$this->startDate, $this->endDate])->where('status', 'selesai');
    }

    public function headings(): array
    {
        return [
            '#',
            'Tanggal Pemeriksaan',
            'Nama Pasien',
            'Penyakit',
            'Waktu Mulai',
            'Waktu Selesai',
        ];
    }

    public function map($pemeriksaan): array
    {
        return [
            $pemeriksaan->invoice_number,
            $pemeriksaan->pasien->nama,
            $pemeriksaan->penyakit->nama,
            $pemeriksaan->mulai,
            $pemeriksaan->selesai,
            Date::dateTimeToExcel($pemeriksaan->created_at),
            Date::dateTimeToExcel($pemeriksaan->updated_at),
        ];
    }
}

