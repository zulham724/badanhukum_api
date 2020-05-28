<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class HargaController extends Controller
{
    public function index(Request $request, $tipe = null)
    {
        $query = DB::table('harga');
        if (isset($tipe)) {
            $query->where('tipe', $tipe);
        }
        
        $harga = $query->get();

        return ['result' => true, 'data' => $harga];
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'normal' => 'required|numeric|min:0',
            'khusus' => 'required|numeric|min:0',
        ]);

        DB::table('harga')
            ->where('id', $id)
            ->update([
                'normal' => $request->input('normal'),
                'khusus' => $request->input('khusus'),
            ]);

        return ['result' => true];
    }

}
