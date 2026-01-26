<?php

namespace App\Exports;

use App\Models\DetailJurnal;
use App\Models\JurnalGuru;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class JurnalAbsensiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $jurnalId;

    public function __construct($jurnalId)
    {
        $this->jurnalId = $jurnalId;
    }

    public function collection()
    {
        return DetailJurnal::with('siswa')
            ->where('jurnal_guru_id', $this->jurnalId)
            ->get();
    }

    public function headings(): array
    {
        $jurnal = JurnalGuru::with(['kelas', 'user'])->find($this->jurnalId);
        // Fix tanggal jika belum dicast di model
        $tgl = $jurnal->tanggal instanceof Carbon ? $jurnal->tanggal : Carbon::parse($jurnal->tanggal);
        
        return [
            ['LAPORAN ABSENSI HARIAN'],
            ['Tanggal', $tgl->format('d-m-Y')],
            ['Kelas', $jurnal->kelas->nama_kelas],
            ['Guru', $jurnal->user->name],
            [''], 
            ['No', 'NISN', 'Nama Siswa', 'Status Kehadiran']
        ];
    }

    public function map($detail): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $detail->siswa->nisn ?? '-',
            $detail->siswa->nama_lengkap,
            $detail->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            6 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE0E0E0']]],
        ];
    }
}