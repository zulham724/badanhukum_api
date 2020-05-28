<?php

namespace App\Models;

use App\Infrastructures\Badan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Perkumpulan extends Model implements Badan
{

    protected $table = 'perkumpulan';

    public function getNamaAttribute(): string
    {
        return strtoupper($this->nama_perkumpulan);
    }

    public function getAlamatFormattedAttribute(): string
    {
        $data = self::select([
            'urbans.name as kelurahan',
            'districts.name as kecamatan',
            'regencies.name as kotkab',
            'provinces.name as provinsi'
        ])
            ->leftJoin('provinces', 'provinces.id', 'perkumpulan.provinsi')
            ->leftJoin('regencies', 'regencies.id', 'perkumpulan.kotkab')
            ->leftJoin('districts', 'districts.id', 'perkumpulan.kecamatan')
            ->leftJoin('urbans', 'urbans.id', 'perkumpulan.kelurahan')
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
            'perkumpulan_pemegang.nama as pemegang_utama',
            'perkumpulan_pemegang.kedudukan'
        ])
            ->leftJoin('perkumpulan_pemegang', function ($join) {
                $join->whereRaw('perkumpulan_pemegang.id = (
                    SELECT
                    MIN(perkumpulan_pemegang.id) pemegang_id
                    FROM perkumpulan_pemegang
                    WHERE perkumpulan_pemegang.perkumpulan_id = perkumpulan.id
                )');
            })
            ->find($this->id);

        return ucwords(strtolower($data->pemegang_utama)) . ' (' . ucwords(strtolower($data->kedudukan)) . ')';
    }

    public function getKategoriAttribute(): string
    {
        return 'Perkumpulan';
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
        return ['bidang'=>$this->kegiatan];
    }

    public function getPemegangsAttribute(): array
    {
        $data = DB::table('perkumpulan_pemegang')
            ->where('perkumpulan_pemegang.perkumpulan_id', $this->id)
            ->orderBy('tipe')
            ->get();

        return $data->toArray();
    }
}
