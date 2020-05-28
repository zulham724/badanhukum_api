<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerkumpulanRequest;
use App\Mail\BadanRegistered;
use App\Models\Perkumpulan;
use App\Services\PerkumpulanService;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PerkumpulanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Perkumpulan::select([
            'perkumpulan.*',
            'provinces.name as nama_provinsi',
            'regencies.name as nama_kotkab',
            'perkumpulan_pemegang.nama as pemegang_utama'
        ])
            ->leftJoin('provinces', 'perkumpulan.provinsi', 'provinces.id')
            ->leftJoin('regencies', 'perkumpulan.kotkab', 'regencies.id')
            ->leftJoin('perkumpulan_pemegang', function ($join) {
                $join->whereRaw('perkumpulan_pemegang.id = (
                    SELECT
                    MIN(perkumpulan_pemegang.id) pemegang_id
                    FROM perkumpulan_pemegang
                    WHERE perkumpulan_pemegang.perkumpulan_id = perkumpulan.id
                )');
            })
            ->orderBy('perkumpulan.id', 'desc')
            ->get();

        return ['result' => true, 'data' => $data];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PerkumpulanRequest $request, PerkumpulanService $perkumpulanService)
    {
        $request->validated();

        $ktps = $request->file('ktp');
        $npwps = $request->file('npwp');
        $validatedKTP = $perkumpulanService->validateKTP($ktps);
        if (!is_bool($validatedKTP)) {
            return $validatedKTP;
        }
        $validatedNPWP = $perkumpulanService->validateKTP($npwps);
        if (!is_bool($validatedNPWP)) {
            return $validatedNPWP;
        }

        $pemegang_utama = Helper::isJson($request->input('pemegang_utama'), true);
        $pemegang_utama = collect($pemegang_utama);
        $pemegang_tambahan = Helper::isJson($request->input('pemegang_tambahan'), true);
        $pemegang_tambahan = collect($pemegang_tambahan);

        $newPerkumpulan = $perkumpulanService->store($request);

        $pemegang_utama->transform(function ($item, $key) use ($ktps, $npwps) {
            $item['ktp'] = $ktps[$key];
            $item['npwp'] = isset($npwps[$key]) ? $npwps[$key] : null;
            return $item;
        });
        $perkumpulanService->addPemegangTambahan($newPerkumpulan->id, $pemegang_utama, '1');

        if ($pemegang_tambahan->count() > 0) {
            $index = 3;

            $pemegang_tambahan->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                $index = $index + 1;
                $item['ktp'] = $ktps[$index];
                $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                return $item;
            });
            $perkumpulanService->addPemegangTambahan($newPerkumpulan->id, $pemegang_tambahan, '2');
        }

        Mail::to($request->input('email'))
            ->cc(config('mail.from.address'))
            ->send(new BadanRegistered($newPerkumpulan));

        return [
            'result' => true,
            'data' => $newPerkumpulan->attributesToArray(),
            'formatted' => $newPerkumpulan->summarized
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, PerkumpulanService $perkumpulanService)
    {
        $data = $perkumpulanService->find($id);

        return ['result' => true, 'data' => $data];
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PerkumpulanRequest $request, $id, PerkumpulanService $perkumpulanService)
    {
        $request->validated();

        $ktps = $request->file('ktp');
        $npwps = $request->file('npwp');

        $validatedKTP = $perkumpulanService->validateKTP($ktps);
        if (!is_bool($validatedKTP)) {
            return $validatedKTP;
        }
        $validatedNPWP = $perkumpulanService->validateKTP($npwps);
        if (!is_bool($validatedNPWP)) {
            return $validatedNPWP;
        }

        $pemegang_tambahan = Helper::isJson($request->input('pemegang_tambahan'), true);
        $pemegang_utama = Helper::isJson($request->input('pemegang_utama'), true);
        $pemegang_tambahan = collect($pemegang_tambahan);
        $pemegang_utama = collect($pemegang_utama);

        $perkumpulan = $perkumpulanService->update($id, $request);
        if ($perkumpulan) {

            $perkumpulanService->clearPemegangTambahan($perkumpulan->id, '1');
            $index = 0;
            $pemegang_utama->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                $index = $key;
                $item['ktp'] = $ktps[$index];
                $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                return $item;
            });
            $perkumpulanService->addPemegangTambahan($perkumpulan->id, $pemegang_utama, '1');

            $perkumpulanService->clearPemegangTambahan($perkumpulan->id, '2');
            if ($pemegang_tambahan->count() > 0) {

                $pemegang_tambahan->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                    $index = $index + 1;
                    $item['ktp'] = $ktps[$index];
                    $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                    return $item;
                });
                $perkumpulanService->addPemegangTambahan($perkumpulan->id, $pemegang_tambahan, '2');
            }

            return ['result' => true, 'data' => $perkumpulan];
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
    public function destroy($id, PerkumpulanService $perkumpulanService)
    {
        $perkumpulanService->delete($id);

        return ['result' => true, 'messege' => "Sukses menghapus data"];
        //
    }
    
    public function getFile($id, Request $request, PerkumpulanService $perkumpulanService)
    {
        $result = $perkumpulanService->getFileContent($id, $request->input('path'));
        if ($result === false) {
            return response()->json(['message' => 'File tidak ditemukan'], 422);
        } else {
            return $result;
        }
    }

    public function downloadFile(/* $id, $file,  */Request $request, PerkumpulanService $perkumpulanService)
    {
        $result = $perkumpulanService->downloadFile($request->input('path'));
        if ($result === false) {
            return response()->json(['message' => 'File tidak ditemukan'], 422);
        } else {
            return $result;
        }
    }
}
