<?php

namespace App\Models;

use App\Infrastructures\Badan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cv extends Model implements Badan
{

    protected $table = 'cv';

    public function getNamaAttribute(): string
    {
        return strtoupper($this->nama_perusahaan);
    }

    public function getAlamatFormattedAttribute(): string
    {
        $data = self::select([
            'urbans.name as kelurahan',
            'districts.name as kecamatan',
            'regencies.name as kotkab',
            'provinces.name as provinsi'
        ])
            ->leftJoin('provinces', 'provinces.id', 'cv.provinsi')
            ->leftJoin('regencies', 'regencies.id', 'cv.kotkab')
            ->leftJoin('districts', 'districts.id', 'cv.kecamatan')
            ->leftJoin('urbans', 'urbans.id', 'cv.kelurahan')
            ->find($this->id);

        return $this->alamat . ', RT.' . $this->rt . ' RW.' . $this->rw . ', Kelurahan ' . $data->kelurahan . ', Kecamatan ' . $data->kecamatan . ', ' . $data->kotkab . ', ' . $data->provinsi . ', ' . $this->kodepos;
    }

    public function getKontakAttribute(): array
    {
        return [
            'kode_telpon' => $this->kode_telpon,
            'telpon' => $this->nomor_telpon,
            'handphone' => $this->nomor_handphone,
            'email' => $this->email
        ];
    }

    public function getPemimpinAttribute(): string
    {
        $data = self::select([
            'direktur.nama as nama_pimpinan',
            'direktur.kedudukan',
        ])
            ->leftJoin('cv_pemegang as direktur', function ($join) {
                $join->on('cv.id', 'direktur.cv_id')
                    ->where('direktur.tipe', '1')
                    ->where('direktur.kedudukan', 'direktur');
            })
            ->find($this->id);

        return ucwords(strtolower($data->nama_pimpinan)) . ' (' . ucwords(strtolower($data->kedudukan)) . ')';
    }

    public function getKategoriAttribute(): string
    {
        return 'CV';
    }

    public function getSummarizedAttribute(): array
    {
        return [
            'nama' => $this->nama,
            'alamat' => $this->alamat_formatted,
            'kontak' => $this->kontak,
            'pemimpin' => $this->pemimpin,
            'kategori' => $this->kategori,
            'rekening_bank' => env('REKENING_BANK'),
            'rekening_nomor' => env('REKENING_NOMOR'),
            'rekening_nama' => env('REKENING_NAMA'),
            'nomor_konfirmasi' => env('NOMOR_KONFIRMASI')
        ];
    }

    public function getBidangsAttribute(): array
    {
        $data = DB::table('cv_bidang')
            ->select([
                'cv_bidang.bidang as kode_bidang',
                'bidang_usaha.kategori as kategori_bidang',
                'kategori_bidang_usaha.nama as kategori_nama',
                'bidang_usaha.nama as nama_bidang',
                'bidang_usaha.deskripsi as deskripsi_bidang',
            ])
            ->join('bidang_usaha', 'bidang_usaha.kode', 'cv_bidang.bidang')
            ->join('kategori_bidang_usaha', 'kategori_bidang_usaha.kode', 'bidang_usaha.kategori')
            ->where('cv_bidang.cv_id', $this->id)
            ->get();

        return $data->toArray();
    }

    public function getPemegangsAttribute(): array
    {
        $data = DB::table('cv_pemegang')
            ->where('cv_pemegang.cv_id', $this->id)
            ->orderBy('tipe')
            ->get();

        return $data->toArray();
    }
}
