<?php

namespace App\Services;

use App\Models\Koperasi;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KoperasiService
{

  private function fill($koperasi, $request): Koperasi
  {
    $koperasi->nama_koperasi = $request->input('nama_koperasi');
    $nama_alternatif = $request->input('nama_alternatif');
    $koperasi->nama_alternatif_1 = isset($nama_alternatif[0]) ? $nama_alternatif[0] : '';
    $koperasi->nama_alternatif_2 = isset($nama_alternatif[1]) ? $nama_alternatif[1] : '';
    $koperasi->nama_alternatif_3 = isset($nama_alternatif[2]) ? $nama_alternatif[2] : '';

    $koperasi->wilayah = $request->input('wilayah');
    $koperasi->jenis = $request->input('jenis');

    $koperasi->kategori_modal = $request->input('kategori_modal');
    $koperasi->biaya = $request->input('biaya');
    $koperasi->modal_dasar = $request->input('modal_dasar');

    $koperasi->wajib = $request->input('wajib');
    $koperasi->pokok = $request->input('pokok');
    $koperasi->sukarela = $request->input('sukarela');

    $koperasi->alamat = $request->input('alamat');
    $koperasi->provinsi = $request->input('provinsi');
    $koperasi->kotkab = $request->input('kotkab');
    $koperasi->kecamatan = $request->input('kecamatan');
    $koperasi->kelurahan = $request->input('kelurahan');
    $koperasi->kodepos = $request->input('kodepos');
    $koperasi->rt = $request->input('rt');
    $koperasi->rw = $request->input('rw');
    $koperasi->kode_telpon = $request->input('kode_telpon');
    $koperasi->nomor_telpon = $request->input('nomor_telpon');
    $koperasi->nomor_handphone = $request->input('nomor_handphone');
    $koperasi->email = $request->input('email');

    $koperasi->unit_simpan_pinjam = $request->input('unit_simpan_pinjam');
    $koperasi->alokasi = $request->input('alokasi');
    $koperasi->jumlah_anggota = $request->input('jumlah_anggota');

    $ktp_anggota_path = $request->file('ktp_anggota')->store('koperasi/ktp_anggota');
    $daftar_hadir_path = $request->file('daftar_hadir')->store('koperasi/daftar_hadir');
    $rekapitulasi_path = $request->file('rekapitulasi')->store('koperasi/rekapitulasi');
    $berita_pendirian_path = $request->file('berita_pendirian')->store('koperasi/berita_pendirian');

    $koperasi->ktp_anggota = $ktp_anggota_path;
    $koperasi->daftar_hadir = $daftar_hadir_path;
    $koperasi->rekapitulasi = $rekapitulasi_path;
    $koperasi->berita_pendirian = $berita_pendirian_path;

    return $koperasi;
  }

  public function store(Request $request): Koperasi
  {
    $koperasi = new Koperasi;
    $koperasi = $this->fill($koperasi, $request);

    $koperasi->save();

    return $koperasi;
  }

  public function updateBidangs($id, Collection $bidangs)
  {
    DB::table('koperasi_bidang')->where('koperasi_id', $id)->delete();
    $this->addBidangs($id, $bidangs);
  }

  public function addBidangs($id, Collection $bidangs)
  {
    $bidangs->transform(function ($item, $key) use ($id) {
      return [
        'koperasi_id' => $id,
        'bidang' => $item,
      ];
    });

    DB::table('koperasi_bidang')->insert($bidangs->toArray());
  }

  public function addPemegangTambahan($id, Collection $pemegangs, $tipe = '1')
  {

    $pemegangs->transform(function ($item, $key) use ($id, $tipe) {
      $ktp_path = $item['ktp']->store('koperasi/ktp');
      $npwp_path = isset($item['npwp']) ? $item['npwp']->store('koperasi/npwp') : '';

      return [
        'koperasi_id' => $id,
        'tipe' => $tipe,
        'nama' => $item['nama'],
        'kedudukan' => strtolower($item['kedudukan']),
        'hp' => $item['hp'],
        'ktp' => $ktp_path,
        'npwp' => $npwp_path,
      ];
    });

    DB::table('koperasi_pemegang')->insert($pemegangs->toArray());
  }

  public function updatePemegangTambahan($id, Collection $pemegangs, $tipe = '1')
  {
    $query = DB::table('koperasi_pemegang')
      ->where('koperasi_id', $id)
      ->where('tipe', $tipe);

    $current_pemegangs = $query->get();

    $current_pemegangs->each(function ($item, $key) {
      Storage::exists($item->ktp) and Storage::delete($item->ktp);
      Storage::exists($item->npwp) and Storage::delete($item->npwp);
    });

    $query->delete();

    return $this->addPemegangTambahan($id, $pemegangs, $tipe);
  }

  public function clearPemegangTambahan($id, $tipe = '1')
  {
    $query = DB::table('koperasi_pemegang')
      ->where('koperasi_id', $id);
    if (!is_null($tipe)) {
      $query->where('tipe', $tipe);
    }

    $current_pemegangs = $query->get();

    $current_pemegangs->each(function ($item, $key) {
      Storage::exists($item->ktp) and Storage::delete($item->ktp);
      Storage::exists($item->npwp) and Storage::delete($item->npwp);
    });

    $query->delete();
  }

  public function delete($id)
  {
    $data = Koperasi::find($id);

    DB::table('koperasi_bidang')
      ->where('koperasi_id', $id)->delete();
    DB::table('koperasi_pemegang')
      ->where('koperasi_id', $id)->delete();
    $this->clearPemegangTambahan($id);
    $data->delete();
  }

  public function find($id)
  {
    $koperasi = null;
    $koperasi_bidang = [];
    $koperasi_pemegang = [];

    try {
      $koperasi = Koperasi::select([
        'koperasi.*',
        'provinces.name as nama_provinsi',
        'regencies.name as nama_kotkab',
        'districts.name as nama_kecamatan',
        'urbans.name as nama_kelurahan',
      ])
        ->leftJoin('provinces', 'koperasi.provinsi', 'provinces.id')
        ->leftJoin('regencies', 'koperasi.kotkab', 'regencies.id')
        ->leftJoin('districts', 'koperasi.kecamatan', 'districts.id')
        ->leftJoin('urbans', 'koperasi.kelurahan', 'urbans.id')
        ->findOrFail($id);

      $koperasi_bidang = DB::table('koperasi_bidang')
        ->select(['koperasi_bidang.*'])
        ->where('koperasi_bidang.koperasi_id', $id)
        ->get();

      $koperasi_pemegang = DB::table('koperasi_pemegang')
        ->select(['koperasi_pemegang.*'])
        ->where('koperasi_pemegang.koperasi_id', $id)
        ->orderBy('tipe')
        ->get();
    } catch (ModelNotFoundException $e) {
      return null;
    } catch (Exception $e) {
      return null;
    };

    return ['koperasi' => $koperasi, 'koperasi_bidang' => $koperasi_bidang, 'koperasi_pemegang' => $koperasi_pemegang];
  }

  public function getFileContent($id, $path)
  {
    if (Storage::exists($path)) {
      return Storage::download($path);
    } else {
      return false;
    }
  }

  public function downloadFile($path)
  {
    if (Storage::exists($path)) {
      return Storage::download($path);
    } else {
      return false;
    }
  }

  public function update($id, $request)
  {
    $koperasi = null;
    $koperasi_bidang = [];
    $koperasi_pemegang = [];
    try {
      $koperasi = Koperasi::findOrFail($id);
      $koperasi = $this->fill($koperasi, $request);
      $koperasi->save();
    } catch (ModelNotFoundException $e) {
      return null;
    } catch (Exception $e) {
      return null;
    };

    return $koperasi;
  }

  public function validateKTP($requestKTP)
  {

    $allowed_types = ['pdf', 'jpg', 'png', 'jpeg', 'bmp'];

    foreach ($requestKTP as $key => $val) {
      if (!$val->isValid()) {
        return response()->json(['message' => 'KTP ' . $val->getClientOriginalName() . ' tidak valid'], 412);
      }
      if (!in_array($val->extension(), $allowed_types)) {
        return response()->json(['message' => 'KTP ' . $val->getClientOriginalName() . ' bukan format yang dibolehkan'], 412);
      }
      if (($val->getClientSize() / 1000) > 2048) {
        return response()->json(['message' => 'KTP ' . $val->getClientOriginalName() . ' lebih dari 2048 kb'], 412);
      }
    }

    return true;
  }
  public function validateNPWP($requestNPWP)
  {
    $allowed_types = ['pdf', 'jpg', 'png', 'jpeg', 'bmp'];

    foreach ($requestNPWP as $key => $val) {
      if (!$val->isValid()) {
        return response()->json(['message' => 'NPWP ' . $val->getClientOriginalName() . ' tidak valid'], 412);
      }
      if (!in_array($val->extension(), $allowed_types)) {
        return response()->json(['message' => 'NPWP ' . $val->getClientOriginalName() . ' bukan format yang dibolehkan'], 412);
      }
      if (($val->getClientSize() / 1000) > 2048) {
        return response()->json(['message' => 'NPWP ' . $val->getClientOriginalName() . ' lebih dari 2048 kb'], 412);
      }
    }

    return true;
  }
}
