<?php

namespace App\Exports;

use App\Models\DetailJurnal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class JurnalBulananExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $bulan;
    protected $tahun;
    protected $userId;

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->userId = Auth::id();
    }

    public function collection()
    {
        // Ambil semua detail jurnal milik guru ini pada bulan tersebut
        return DetailJurnal::query()
            ->join('jurnal_guru', 'detail_jurnal.jurnal_guru_id', '=', 'jurnal_guru.id')
            ->join('siswa', 'detail_jurnal.siswa_id', '=', 'siswa.id')
            ->join('kelas', 'jurnal_guru.kelas_id', '=', 'kelas.id') // Join kelas dari jurnal
            ->where('jurnal_guru.user_id', $this->userId)
            ->whereMonth('jurnal_guru.tanggal', $this->bulan)
            ->whereYear('jurnal_guru.tanggal', $this->tahun)
            ->select(
                'jurnal_guru.tanggal',
                'kelas.nama_kelas',
                'siswa.nama_lengkap',
                'siswa.nisn',
                'detail_jurnal.status'
            )
            ->orderBy('jurnal_guru.tanggal')
            ->orderBy('kelas.nama_kelas')
            ->orderBy('siswa.nama_lengkap')
            ->get();
    }

    public function headings(): array
    {
        $namaBulan = Carbon::createFromDate($this->tahun, $this->bulan, 1)->translatedFormat('F Y');
        
        return [
            ['REKAP ABSENSI BULANAN'],
            ['Periode', $namaBulan],
            ['Guru', Auth::user()->name],
            [''],
            ['Tanggal', 'Kelas', 'NISN', 'Nama Siswa', 'Status']
        ];
    }

    public function map($row): array
    {
        return [
            $row->tanggal, // Tanggal Jurnal
            $row->nama_kelas, // Nama Kelas
            $row->nisn,
            $row->nama_lengkap,
            $row->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            5 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE0E0E0']]],
        ];
    }
}