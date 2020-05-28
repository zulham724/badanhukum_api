<?php

namespace App\Models;

use App\Infrastructures\Badan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Koperasi extends Model implements Badan
{
    protected $table = 'koperasi';

    public function getNamaAttribute(): string
    {
        return strtoupper($this->nama_koperasi);
    }

    public function getAlamatFormattedAttribute(): string
    {
        $data = self::select([
            'urbans.name as kelurahan',
            'districts.name as kecamatan',
            'regencies.name as kotkab',
            'provinces.name as provinsi'
        ])
            ->leftJoin('provinces', 'provinces.id', 'koperasi.provinsi')
            ->leftJoin('regencies', 'regencies.id', 'koperasi.kotkab')
            ->leftJoin('districts', 'districts.id', 'koperasi.kecamatan')
            ->leftJoin('urbans', 'urbans.id', 'koperasi.kelurahan')
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
            'ketua.nama as nama_ketua',
            'ketua.kedudukan'
        ])
            ->leftJoin('koperasi_pemegang as ketua', function ($join) {
                $join->on('koperasi.id', 'ketua.koperasi_id')
                    ->where('ketua.tipe', '1')
                    ->where('ketua.kedudukan', 'ketua');
            })
            ->find($this->id);

        return ucwords(strtolower($data->nama_ketua)) . ' (' . ucwords(strtolower($data->kedudukan)) . ')';
    }

    public function getKategoriAttribute(): string
    {
        return 'Koperasi';
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
        $data = DB::table('koperasi_bidang')
            ->select([
                'koperasi_bidang.bidang as kode_bidang',
                'bidang_usaha.kategori as kategori_bidang',
                'kategori_bidang_usaha.nama as kategori_nama',
                'bidang_usaha.nama as nama_bidang',
                'bidang_usaha.deskripsi as deskripsi_bidang',
            ])
            ->join('bidang_usaha', 'bidang_usaha.kode', 'koperasi_bidang.bidang')
            ->join('kategori_bidang_usaha', 'kategori_bidang_usaha.kode', 'bidang_usaha.kategori')
            ->where('koperasi_bidang.koperasi_id', $this->id)
            ->get();

        return $data->toArray();
    }

    public function getPemegangsAttribute(): array
    {
        $data = DB::table('koperasi_pemegang')
            ->where('koperasi_pemegang.koperasi_id', $this->id)
            ->orderBy('tipe')
            ->get();

        return $data->toArray();
    }
}
