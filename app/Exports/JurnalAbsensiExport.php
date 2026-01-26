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

class JurnalAbsensiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $jurnalId;

    public function __construct($jurnalId)
    {
        $this->jurnalId = $jurnalId;
    }

    public function collection()
    {
        // Ambil detail jurnal beserta data siswa
        return DetailJurnal::with('siswa')
            ->where('jurnal_guru_id', $this->jurnalId)
            ->get();
    }

    public function headings(): array
    {
        // Header Excel
        $jurnal = JurnalGuru::with(['kelas', 'user'])->find($this->jurnalId);
        
        return [
            ['LAPORAN ABSENSI KELAS'],
            ['Tanggal', $jurnal->tanggal->format('d-m-Y')],
            ['Kelas', $jurnal->kelas->nama_kelas],
            ['Guru', $jurnal->user->name],
            ['Mata Pelajaran', $jurnal->mata_pelajaran],
            [''], // Baris kosong
            [
                'No',
                'NISN',
                'Nama Siswa',
                'Status Kehadiran',
                'Waktu Input'
            ]
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
            $detail->created_at->format('H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style Judul Utama (Baris 1)
            1 => ['font' => ['bold' => true, 'size' => 14]],
            // Style Header Kolom (Baris 7)
            7 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE0E0E0']]],
        ];
    }
}