<?php

namespace App\Models;

use App\Infrastructures\Badan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Firma extends Model implements Badan
{

    protected $table = 'firma';

    public function getNamaAttribute(): string
    {
        return strtoupper($this->nama_firma);
    }

    public function getAlamatFormattedAttribute(): string
    {
        $data = self::select([
            'urbans.name as kelurahan',
            'districts.name as kecamatan',
            'regencies.name as kotkab',
            'provinces.name as provinsi'
        ])
            ->leftJoin('provinces', 'provinces.id', 'firma.provinsi')
            ->leftJoin('regencies', 'regencies.id', 'firma.kotkab')
            ->leftJoin('districts', 'districts.id', 'firma.kecamatan')
            ->leftJoin('urbans', 'urbans.id', 'firma.kelurahan')
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
            'firma_pemegang.nama as pemegang_utama',
            'firma_pemegang.kedudukan'
        ])
            ->leftJoin('firma_pemegang', function ($join) {
                $join->whereRaw('firma_pemegang.id = (
                    SELECT
                    MIN(firma_pemegang.id) pemegang_id
                    FROM firma_pemegang
                    WHERE firma_pemegang.firma_id = firma.id
                )');
            })
            ->find($this->id);

        return ucwords(strtolower($data->pemegang_utama)) . ' (' . ucwords(strtolower($data->kedudukan)) . ')';
    }

    public function getKategoriAttribute(): string
    {
        return 'Firma';
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
        $data = DB::table('firma_bidang')
            ->select([
                'firma_bidang.bidang as kode_bidang',
                'bidang_usaha.kategori as kategori_bidang',
                'kategori_bidang_usaha.nama as kategori_nama',
                'bidang_usaha.nama as nama_bidang',
                'bidang_usaha.deskripsi as deskripsi_bidang',
            ])
            ->join('bidang_usaha', 'bidang_usaha.kode', 'firma_bidang.bidang')
            ->join('kategori_bidang_usaha', 'kategori_bidang_usaha.kode', 'bidang_usaha.kategori')
            ->where('firma_bidang.firma_id', $this->id)
            ->get();

        return $data->toArray();
    }

    public function getPemegangsAttribute(): array
    {
        $data = DB::table('firma_pemegang')
            ->where('firma_pemegang.firma_id', $this->id)
            ->orderBy('tipe')
            ->get();

        return $data->toArray();
    }
}
