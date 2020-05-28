<?php

namespace App\Http\Controllers;

use App\Http\Requests\YayasanRequest;
use App\Mail\BadanRegistered;
use App\Models\Yayasan;
use App\Services\YayasanService;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class YayasanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Yayasan::select([
            'yayasan.*',
            'provinces.name as nama_provinsi',
            'regencies.name as nama_kotkab',
            'ketua.nama as nama_ketua'
        ])
            ->leftJoin('provinces', 'yayasan.provinsi', 'provinces.id')
            ->leftJoin('regencies', 'yayasan.kotkab', 'regencies.id')
            ->leftJoin('yayasan_pemegang as ketua', function ($join) {
                $join->on('yayasan.id', 'ketua.yayasan_id')
                    ->where('ketua.tipe', '1')
                    ->where('ketua.kedudukan', 'ketua');
            })
            ->orderBy('yayasan.id', 'desc')
            ->get();

        return ['result' => true, 'data' => $data];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(YayasanRequest $request, YayasanService $yayasanService)
    {
        $request->validated();

        $ktps = $request->file('ktp');
        $npwps = $request->file('npwp');

        $validatedKTP = $yayasanService->validateKTP($ktps);
        if (!is_bool($validatedKTP)) {
            return $validatedKTP;
        }
        $validatedNPWP = $yayasanService->validateKTP($npwps);
        if (!is_bool($validatedNPWP)) {
            return $validatedNPWP;
        }

        $pemegang_tambahan = Helper::isJson($request->input('pemegang_tambahan'), true);
        $pemegang_tambahan = collect($pemegang_tambahan);
        $pemegang_utama = Helper::isJson($request->input('pemegang_utama'), true);
        $pemegang_utama = collect($pemegang_utama);

        $newYayasan = $yayasanService->store($request);

        $bidangs = collect($request->input('bidangs'));
        $yayasanService->addBidangs($newYayasan->id, $bidangs);

        $pemegang_utama->transform(function ($item, $key) use ($ktps, $npwps) {
            $item['ktp'] = $ktps[$key];
            $item['npwp'] = isset($npwps[$key]) ? $npwps[$key] : null;
            return $item;
        });
        $yayasanService->addPemegangTambahan($newYayasan->id, $pemegang_utama, '1');

        if ($pemegang_tambahan->count() > 0) {
            $index = 4;

            $pemegang_tambahan->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                $index = $index + 1;
                $item['ktp'] = $ktps[$index];
                $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                return $item;
            });
            $yayasanService->addPemegangTambahan($newYayasan->id, $pemegang_tambahan, '2');
        }

        Mail::to($request->input('email'))
            ->cc(config('mail.from.address'))
            ->send(new BadanRegistered($newYayasan));

        return [
            'result' => true,
            'data' => $newYayasan->attributesToArray(),
            'formatted' => $newYayasan->summarized
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, YayasanService $yayasanService)
    {
        $data = $yayasanService->find($id);

        return ['result' => true, 'data' => $data];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(YayasanRequest $request, $id, YayasanService $yayasanService)
    {
        $request->validated();

        $ktps = $request->file('ktp');
        $npwps = $request->file('npwp');

        $validatedKTP = $yayasanService->validateKTP($ktps);
        if (!is_bool($validatedKTP)) {
            return $validatedKTP;
        }
        $validatedNPWP = $yayasanService->validateKTP($npwps);
        if (!is_bool($validatedNPWP)) {
            return $validatedNPWP;
        }

        $pemegang_tambahan = Helper::isJson($request->input('pemegang_tambahan'), true);
        $pemegang_utama = Helper::isJson($request->input('pemegang_utama'), true);
        $pemegang_tambahan = collect($pemegang_tambahan);
        $pemegang_utama = collect($pemegang_utama);

        $yayasan = $yayasanService->update($id, $request);
        if ($yayasan) {
            $bidangs = collect($request->input('bidangs'));
            $yayasanService->updateBidangs($yayasan->id, $bidangs);

            $yayasanService->clearPemegangTambahan($yayasan->id, '1');
            $index = 0;
            $pemegang_utama->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                $index = $key;
                $item['ktp'] = $ktps[$index];
                $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                return $item;
            });
            $yayasanService->addPemegangTambahan($yayasan->id, $pemegang_utama, '1');

            $yayasanService->clearPemegangTambahan($yayasan->id, '2');
            if ($pemegang_tambahan->count() > 0) {

                $pemegang_tambahan->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                    $index = $index + 1;
                    $item['ktp'] = $ktps[$index];
                    $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                    return $item;
                });
                $yayasanService->addPemegangTambahan($yayasan->id, $pemegang_tambahan, '2');
            }

            return ['result' => true, 'data' => $yayasan];
        } else {
            return ['result' => false, 'message' => 'Data tidak ditemukan'];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, YayasanService $yayasanService)
    {
        $yayasanService->delete($id);

        return ['result' => true, 'messege' => "Sukses menghapus data"];
        //
    }

    public function getFile($id, Request $request, YayasanService $yayasanService)
    {
        $result = $yayasanService->getFileContent($id, $request->input('path'));
        if ($result === false) {
            return response()->json(['message' => 'File tidak ditemukan'], 422);
        } else {
            return $result;
        }
    }

    public function downloadFile(/* $id, $file,  */Request $request, YayasanService $yayasanService)
    {
        $result = $yayasanService->downloadFile($request->input('path'));
        if ($result === false) {
            return response()->json(['message' => 'File tidak ditemukan'], 422);
        } else {
            return $result;
        }
    }
}
