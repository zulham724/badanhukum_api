<?php

namespace App\Http\Controllers;

use App\Http\Requests\PTRequest;
use App\Mail\BadanRegistered;
use App\Models\Pt;
use App\Services\PTService;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PTController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Pt::select([
            'pt.*',
            'provinces.name as nama_provinsi',
            'regencies.name as nama_kotkab',
            'direktur.nama as nama_direktur',
        ])
            ->leftJoin('provinces', 'pt.provinsi', 'provinces.id')
            ->leftJoin('regencies', 'pt.kotkab', 'regencies.id')
            ->leftJoin('pt_pemegang as direktur', function ($join) {
                $join->on('pt.id', 'direktur.pt_id')
                    ->where('direktur.tipe', '1')
                    ->where('direktur.kedudukan', 'direktur');
            })
            ->orderBy('pt.id', 'desc')
            ->get();

        return ['result' => true, 'data' => $data];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PTRequest $request, PTService $ptService)
    {
        $request->validated();

        $ktps = $request->file('ktp');
        $npwps = $request->file('npwp');

        $validatedKTP = $ptService->validateKTP($ktps);
        if (!is_bool($validatedKTP)) {
            return $validatedKTP;
        }
        $validatedNPWP = $ptService->validateKTP($npwps);
        if (!is_bool($validatedNPWP)) {
            return $validatedNPWP;
        }

        $pemegang_tambahan = Helper::isJson($request->input('pemegang_tambahan'), true);
        $pemegang_lain = Helper::isJson($request->input('pemegang_lain'), true);
        $pemegang_tambahan = collect($pemegang_tambahan);
        $pemegang_lain = collect($pemegang_lain);

        $newPT = $ptService->store($request);

        $bidangs = collect($request->input('bidangs'));
        $ptService->addBidangs($newPT->id, $bidangs);

        $pemegangs = collect([
            [
                "nama" => $request->input('direktur_utama'),
                "kedudukan" => 'direktur',
                "saham" => $request->input('saham_direktur_utama'),
                "ktp" => $ktps[0],
                "npwp" => $npwps[0],
            ],
            [
                "nama" => $request->input('komisaris_utama'),
                "kedudukan" => 'komisaris',
                "saham" => $request->input('saham_komisaris_utama'),
                "ktp" => $ktps[1],
                "npwp" => isset($npwps[1]) ? $npwps[1] : null,
            ]
        ]);
        $ptService->addPemegangTambahan($newPT->id, $pemegangs, '1');

        if ($pemegang_tambahan->count() > 0) {
            $index = 1;

            $pemegang_tambahan->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                $index = $index + 1;
                $item['ktp'] = $ktps[$index];
                $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                return $item;
            });
            $ptService->addPemegangTambahan($newPT->id, $pemegang_tambahan, '2');
        }

        if ($pemegang_lain->count() > 0) {
            $pemegang_lain->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                $index = $index + 1;
                $item['ktp'] = $ktps[$index];
                $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                return $item;
            });
            $ptService->addPemegangTambahan($newPT->id, $pemegang_lain, '3');
        }

        Mail::to($request->input('email'))
            ->cc(config('mail.from.address'))
            ->send(new BadanRegistered($newPT));

        return [
            'result' => true,
            'data' => $newPT->attributesToArray(),
            'formatted' => $newPT->summarized
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, PTService $ptService)
    {
        $data = $ptService->find($id);

        return ['result' => true, 'data' => $data];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PTRequest $request, $id, PTService $ptService)
    {
        $request->validated();

        $ktps = $request->file('ktp');
        $npwps = $request->file('npwp');

        $validatedKTP = $ptService->validateKTP($ktps);
        if (!is_bool($validatedKTP)) {
            return $validatedKTP;
        }
        $validatedNPWP = $ptService->validateKTP($npwps);
        if (!is_bool($validatedNPWP)) {
            return $validatedNPWP;
        }

        $pemegang_tambahan = Helper::isJson($request->input('pemegang_tambahan'), true);
        $pemegang_lain = Helper::isJson($request->input('pemegang_lain'), true);
        $pemegang_tambahan = collect($pemegang_tambahan);
        $pemegang_lain = collect($pemegang_lain);

        $pt = $ptService->update($id, $request);
        if ($pt) {
            $bidangs = collect($request->input('bidangs'));
            $ptService->updateBidangs($pt->id, $bidangs);

            $pemegangs = collect([
                [
                    "nama" => $request->input('direktur_utama'),
                    "kedudukan" => 'direktur',
                    "saham" => $request->input('saham_direktur_utama'),
                    "ktp" => isset($ktps[0]) ? $ktps[0] : null,
                    "npwp" => isset($npwps[0]) ? $npwps[0] : null,
                ],
                [
                    "nama" => $request->input('komisaris_utama'),
                    "kedudukan" => 'komisaris',
                    "saham" => $request->input('saham_komisaris_utama'),
                    "ktp" => isset($ktps[1]) ? $ktps[1] : null,
                    "npwp" => isset($npwps[1]) ? $npwps[1] : null,
                ]
            ]);
            $ptService->updatePemegangTambahan($pt->id, $pemegangs, '1');

            $ptService->clearPemegangTambahan($pt->id, '2');
            if ($pemegang_tambahan->count() > 0) {
                $index = 1;

                $pemegang_tambahan->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                    $index = $index + 1;
                    $item['ktp'] = $ktps[$index];
                    $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                    return $item;
                });
                $ptService->addPemegangTambahan($pt->id, $pemegang_tambahan, '2');
            }

            $ptService->clearPemegangTambahan($pt->id, '3');
            if ($pemegang_lain->count() > 0) {
                $pemegang_lain->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                    $index = $index + 1;
                    $item['ktp'] = $ktps[$index];
                    $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                    return $item;
                });
                $ptService->addPemegangTambahan($pt->id, $pemegang_lain, '3');
            }

            return ['result' => true, 'data' => $pt];
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
    public function destroy($id, PTService $ptService)
    {
        $ptService->delete($id);

        return ['result' => true, 'messege' => "Sukses menghapus data"];
    }

    public function getFile($id, Request $request, PTService $ptService)
    {
        $result = $ptService->getFileContent($id, $request->input('path'));
        if ($result === false) {
            return response()->json(['message' => 'File tidak ditemukan'], 422);
        } else {
            return $result;
        }
    }

    public function downloadFile($id, $file, Request $request, PTService $ptService)
    {
        $result = $ptService->downloadFile($id, $file, $request->input('path'));
        if ($result === false) {
            return response()->json(['message' => 'File tidak ditemukan'], 422);
        } else {
            return $result;
        }
    }
}
