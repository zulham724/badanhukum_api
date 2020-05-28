<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Cv;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CVService
{

  private function fill($cv, $request): Cv
  {
    $cv->nama_perusahaan = $request->input('nama_perusahaan');
    $nama_alternatif = $request->input('nama_alternatif');
    $cv->nama_alternatif_1 = isset($nama_alternatif[0]) ? $nama_alternatif[0] : '';
    $cv->nama_alternatif_2 = isset($nama_alternatif[1]) ? $nama_alternatif[1] : '';
    $cv->nama_alternatif_3 = isset($nama_alternatif[2]) ? $nama_alternatif[2] : '';

    $cv->kategori_modal = $request->input('kategori_modal');
    $cv->biaya = $request->input('biaya');
    $cv->modal_dasar = $request->input('modal_dasar');
    $cv->modal_ditempatkan = $request->input('modal_ditempatkan');

    $cv->alamat = $request->input('alamat');
    $cv->provinsi = $request->input('provinsi');
    $cv->kotkab = $request->input('kotkab');
    $cv->kecamatan = $request->input('kecamatan');
    $cv->kelurahan = $request->input('kelurahan');
    $cv->kodepos = $request->input('kodepos');
    $cv->rt = $request->input('rt');
    $cv->rw = $request->input('rw');
    $cv->kode_telpon = $request->input('kode_telpon');
    $cv->nomor_telpon = $request->input('nomor_telpon');
    $cv->nomor_handphone = $request->input('nomor_handphone');
    $cv->email = $request->input('email');

    return $cv;
  }
  public function store(Request $request): Cv
  {
    $cv = new Cv;
    $cv = $this->fill($cv, $request);

    $cv->save();

    return $cv;
  }

  public function addBidangs($id, Collection $bidangs)
  {
    $bidangs->transform(function ($item, $key) use ($id) {
      return [
        'cv_id' => $id,
        'bidang' => $item,
      ];
    });

    DB::table('cv_bidang')->insert($bidangs->toArray());
  }

  public function updateBidangs($id, Collection $bidangs)
  {
    DB::table('cv_bidang')->where('cv_id', $id)->delete();
    $this->addBidangs($id, $bidangs);
  }

  public function addPemegangTambahan($id, Collection $pemegangs, $tipe = '1')
  {

    $pemegangs->transform(function ($item, $key) use ($id, $tipe) {
      $ktp_path = $item['ktp']->store('cv/ktp');
      $npwp_path = isset($item['npwp']) ? $item['npwp']->store('cv/npwp') : '';

      return [
        'cv_id' => $id,
        'tipe' => $tipe,
        'nama' => $item['nama'],
        'kedudukan' => strtolower($item['kedudukan']),
        'saham' => $item['saham'],
        'ktp' => $ktp_path,
        'npwp' => $npwp_path,
      ];
    });

    DB::table('cv_pemegang')->insert($pemegangs->toArray());
  }

  public function updatePemegangTambahan($id, Collection $pemegangs, $tipe = '1')
  {
    $query = DB::table('cv_pemegang')
      ->where('cv_id', $id)
      ->where('tipe', $tipe);

    $current_pemegangs = $query->get();

    $current_pemegangs->each(function ($item, $key) {
      Storage::exists($item->ktp) and Storage::delete($item->ktp);
      Storage::exists($item->npwp) and Storage::delete($item->npwp);
    });

    $query->delete();

    return $this->addPemegangTambahan($id, $pemegangs, $tipe);
  }

  public function clearPemegangTambahan($id, $tipe = null)
  {
    $query = DB::table('cv_pemegang')
      ->where('cv_id', $id);
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
    $data = Cv::find($id);

    DB::table('cv_bidang')
      ->where('cv_id', $id)->delete();
    DB::table('cv_pemegang')
      ->where('cv_id', $id)->delete();
    $this->clearPemegangTambahan($id);
    $data->delete();
  }

  public function find($id)
  {
    $cv = null;
    $cv_bidang = [];
    $cv_pemegang = [];

    try {
      $cv = Cv::select([
        'cv.*',
        'provinces.name as nama_provinsi',
        'regencies.name as nama_kotkab',
        'districts.name as nama_kecamatan',
        'urbans.name as nama_kelurahan',
      ])
        ->leftJoin('provinces', 'cv.provinsi', 'provinces.id')
        ->leftJoin('regencies', 'cv.kotkab', 'regencies.id')
        ->leftJoin('districts', 'cv.kecamatan', 'districts.id')
        ->leftJoin('urbans', 'cv.kelurahan', 'urbans.id')
        ->findOrFail($id);

      $cv_bidang = DB::table('cv_bidang')
        ->select(['cv_bidang.*'])
        ->where('cv_bidang.cv_id', $id)
        ->get();

      $cv_pemegang = DB::table('cv_pemegang')
        ->select(['cv_pemegang.*'])
        ->where('cv_pemegang.cv_id', $id)
        ->orderBy('tipe')
        ->get();
    } catch (ModelNotFoundException $e) {
      return null;
    } catch (Exception $e) {
      return null;
    };

    return ['cv' => $cv, 'cv_bidang' => $cv_bidang, 'cv_pemegang' => $cv_pemegang];
  }

  public function getFileContent($id, $path)
  {
    if (Storage::exists($path)) {
      return Storage::download($path);
    } else {
      return false;
    }
  }

  public function downloadFile($id, $file, $path)
  {
    if (Storage::exists($path)) {
      return Storage::download($path);
    } else {
      return false;
    }
  }

  public function update($id, $request)
  {
    $cv = null;
    $cv_bidang = [];
    $cv_pemegang = [];
    try {
      $cv = Cv::findOrFail($id);
      $cv = $this->fill($cv, $request);
      $cv->save();
    } catch (ModelNotFoundException $e) {
      return null;
    } catch (Exception $e) {
      return null;
    };

    return $cv;
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
