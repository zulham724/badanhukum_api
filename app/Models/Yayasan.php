<?php

namespace App\Models;

use App\Infrastructures\Badan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Yayasan extends Model implements Badan
{
    protected $table = 'yayasan';

    public function getNamaAttribute(): string
    {
        return strtoupper($this->nama_yayasan);
    }

    public function getAlamatFormattedAttribute(): string
    {
        $data = self::select([
            'urbans.name as kelurahan',
            'districts.name as kecamatan',
            'regencies.name as kotkab',
            'provinces.name as provinsi'
        ])
            ->leftJoin('provinces', 'provinces.id', 'yayasan.provinsi')
            ->leftJoin('regencies', 'regencies.id', 'yayasan.kotkab')
            ->leftJoin('districts', 'districts.id', 'yayasan.kecamatan')
            ->leftJoin('urbans', 'urbans.id', 'yayasan.kelurahan')
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
            ->leftJoin('yayasan_pemegang as ketua', function ($join) {
                $join->on('yayasan.id', 'ketua.yayasan_id')
                    ->where('ketua.tipe', '1')
                    ->where('ketua.kedudukan', 'ketua');
            })
            ->find($this->id);

        return ucwords(strtolower($data->nama_ketua)) . ' (' . ucwords(strtolower($data->kedudukan)) . ')';
    }

    public function getKategoriAttribute(): string
    {
        return 'Yayasan';
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
        $data = DB::table('yayasan_bidang')
            ->select([
                'yayasan_bidang.bidang',
            ])
            ->where('yayasan_bidang.yayasan_id', $this->id)
            ->get();

        return $data->toArray();
    }

    public function getPemegangsAttribute(): array
    {
        $data = DB::table('yayasan_pemegang')
            ->where('yayasan_pemegang.yayasan_id', $this->id)
            ->orderBy('tipe')
            ->get();

        return $data->toArray();
    }
}
