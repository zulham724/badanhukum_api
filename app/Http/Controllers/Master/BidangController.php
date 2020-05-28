<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BidangController extends Controller
{
    public function getCategories()
    {
        $categories = DB::table('kategori_bidang_usaha')->get();
        
        return ['result' => true, 'data' => $categories];
    }

    public function addCategory(Request $request)
    {
        $request->validate([
            'kode' => 'required|unique:kategori_bidang_usaha',
            'nama' => 'required|unique:kategori_bidang_usaha',
        ]);
        
        $lastInsertId = DB::table('kategori_bidang_usaha')->insertGetId([
            'kode' => $request->input('kode'),
            'nama' => $request->input('nama'),
        ]);

        if ($lastInsertId) {
            $data = DB::table('kategori_bidang_usaha')->where('id', $lastInsertId)->first();
            return ['result' => true, 'data' => $data];
        } else {
            return response()->json(['message' => 'Gagal menambah kategori'], 422);
        }
        
    }

    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'kode' => 'required|unique:kategori_bidang_usaha,kode,'. $id ,
            'nama' => 'required|unique:kategori_bidang_usaha,nama,' . $id,
        ]);

        DB::table('kategori_bidang_usaha')
            ->where('id', $id)
            ->update([
                'kode' => $request->input('kode'),
                'nama' => $request->input('nama'),
            ]);
            
        $data = DB::table('kategori_bidang_usaha')->where('id', $id)->first();
        return ['result' => true, 'data' => $data];
    }
    
    public function deleteCategory(Request $request, $id)
    {
        if (DB::table('kategori_bidang_usaha')
            ->where('id', $id)
            ->delete()) {
                
            return ['result' => true, 'message' => "Berhasil menghapus kategori"];
        } else {
            return response()->json(['message' => 'Gagal menghapus kategori'], 422);
        }
    }
    
    public function get()
    {
        $categories = DB::table('bidang_usaha')
            ->select([
                'kategori_bidang_usaha.nama as nama_kategori',
                'bidang_usaha.*'
            ])
            ->leftJoin('kategori_bidang_usaha', 'bidang_usaha.kategori', 'kategori_bidang_usaha.kode')
            ->orderBy('bidang_usaha.kategori')
            ->orderBy('bidang_usaha.kode')
            ->get();
        
        return ['result' => true, 'data' => $categories];
    }

    public function add(Request $request)
    {
        $request->validate([
            'kategori' => 'required',
            'kode' => 'required|unique:bidang_usaha',
            'nama' => 'required|unique:bidang_usaha',
        ]);
        
        $lastInsertId = DB::table('bidang_usaha')->insertGetId([
            'kategori' => $request->input('kategori'),
            'kode' => $request->input('kode'),
            'nama' => $request->input('nama'),
            'deskripsi' => $request->input('deskripsi'),
        ]);

        if ($lastInsertId) {
            $data = DB::table('bidang_usaha')->where('id', $lastInsertId)->first();
            return ['result' => true, 'data' => $data];
        } else {
            return response()->json(['message' => 'Gagal menambah bidang'], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kategori' => 'required',
            'kode' => 'required|unique:bidang_usaha,kode,' . $id,
            'nama' => 'required|unique:bidang_usaha,nama,' . $id,
        ]);

        DB::table('bidang_usaha')
            ->where('id', $id)
            ->update([
                'kategori' => $request->input('kategori'),
                'kode' => $request->input('kode'),
                'nama' => $request->input('nama'),
                'deskripsi' => $request->input('deskripsi'),
            ]);

        $data = DB::table('bidang_usaha')->where('id', $id)->first();
        return ['result' => true, 'data' => $data];
    }

    public function delete(Request $request, $id)
    {
        if (DB::table('bidang_usaha')
            ->where('id', $id)
            ->delete()
        ) {

            return ['result' => true, 'message' => "Berhasil menghapus bidang"];
        } else {
            return response()->json(['message' => 'Gagal menghapus bidang'], 422);
        }
    }
    
}
