<?php

namespace App\Services;

use App\Models\Yayasan;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class YayasanService
{

  private function fill($yayasan, $request): Yayasan
  {
    $yayasan->nama_yayasan = $request->input('nama_yayasan');
    $nama_alternatif = $request->input('nama_alternatif');
    $yayasan->nama_alternatif_1 = isset($nama_alternatif[0]) ? $nama_alternatif[0] : '';
    $yayasan->nama_alternatif_2 = isset($nama_alternatif[1]) ? $nama_alternatif[1] : '';
    $yayasan->nama_alternatif_3 = isset($nama_alternatif[2]) ? $nama_alternatif[2] : '';

    $yayasan->kategori_modal = $request->input('kategori_modal');
    $yayasan->biaya = $request->input('biaya');
    $yayasan->modal_dasar = $request->input('modal_dasar');

    $yayasan->alamat = $request->input('alamat');
    $yayasan->provinsi = $request->input('provinsi');
    $yayasan->kotkab = $request->input('kotkab');
    $yayasan->kecamatan = $request->input('kecamatan');
    $yayasan->kelurahan = $request->input('kelurahan');
    $yayasan->kodepos = $request->input('kodepos');
    $yayasan->rt = $request->input('rt');
    $yayasan->rw = $request->input('rw');
    $yayasan->kode_telpon = $request->input('kode_telpon');
    $yayasan->nomor_telpon = $request->input('nomor_telpon');
    $yayasan->nomor_handphone = $request->input('nomor_handphone');
    $yayasan->email = $request->input('email');

    $yayasan->bidang = $request->input('bidang');

    return $yayasan;
  }

  public function store(Request $request): Yayasan
  {
    $yayasan = new Yayasan;
    $yayasan = $this->fill($yayasan, $request);

    $yayasan->save();

    return $yayasan;
  }

  public function updateBidangs($id, Collection $bidangs)
  {
    DB::table('yayasan_bidang')->where('yayasan_id', $id)->delete();
    $this->addBidangs($id, $bidangs);
  }

  public function addBidangs($id, Collection $bidangs)
  {
    $bidangs->transform(function ($item, $key) use ($id) {
      return [
        'yayasan_id' => $id,
        'bidang' => $item,
      ];
    });

    DB::table('yayasan_bidang')->insert($bidangs->toArray());
  }

  public function addPemegangTambahan($id, Collection $pemegangs, $tipe = '1')
  {

    $pemegangs->transform(function ($item, $key) use ($id, $tipe) {
      $ktp_path = $item['ktp']->store('yayasan/ktp');
      $npwp_path = isset($item['npwp']) ? $item['npwp']->store('yayasan/npwp') : '';

      return [
        'yayasan_id' => $id,
        'tipe' => $tipe,
        'nama' => $item['nama'],
        'kedudukan' => strtolower($item['kedudukan']),
        'hp' => $item['hp'],
        'ktp' => $ktp_path,
        'npwp' => $npwp_path,
      ];
    });

    DB::table('yayasan_pemegang')->insert($pemegangs->toArray());
  }

  public function updatePemegangTambahan($id, Collection $pemegangs, $tipe = '1')
  {
    $query = DB::table('yayasan_pemegang')
      ->where('yayasan_id', $id)
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
    $query = DB::table('yayasan_pemegang')
      ->where('yayasan_id', $id);
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
    $data = Yayasan::find($id);

    DB::table('yayasan_bidang')
      ->where('yayasan_id', $id)->delete();
    DB::table('yayasan_pemegang')
      ->where('yayasan_id', $id)->delete();
    $this->clearPemegangTambahan($id);
    $data->delete();
  }

  public function find($id)
  {
    $yayasan = null;
    $yayasan_bidang = [];
    $yayasan_pemegang = [];

    try {
      $yayasan = Yayasan::select([
        'yayasan.*',
        'provinces.name as nama_provinsi',
        'regencies.name as nama_kotkab',
        'districts.name as nama_kecamatan',
        'urbans.name as nama_kelurahan',
      ])
        ->leftJoin('provinces', 'yayasan.provinsi', 'provinces.id')
        ->leftJoin('regencies', 'yayasan.kotkab', 'regencies.id')
        ->leftJoin('districts', 'yayasan.kecamatan', 'districts.id')
        ->leftJoin('urbans', 'yayasan.kelurahan', 'urbans.id')
        ->findOrFail($id);

      $yayasan_bidang = DB::table('yayasan_bidang')
        ->select(['yayasan_bidang.*'])
        ->where('yayasan_bidang.yayasan_id', $id)
        ->get();

      $yayasan_pemegang = DB::table('yayasan_pemegang')
        ->select(['yayasan_pemegang.*'])
        ->where('yayasan_pemegang.yayasan_id', $id)
        ->orderBy('tipe')
        ->get();
    } catch (ModelNotFoundException $e) {
      return null;
    } catch (Exception $e) {
      return null;
    };

    return ['yayasan' => $yayasan, 'yayasan_bidang' => $yayasan_bidang, 'yayasan_pemegang' => $yayasan_pemegang];
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
    $yayasan = null;
    
    try {
      $yayasan = Yayasan::findOrFail($id);
      $yayasan = $this->fill($yayasan, $request);
      $yayasan->save();
    } catch (ModelNotFoundException $e) {
      return null;
    } catch (Exception $e) {
      return null;
    };

    return $yayasan;
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
