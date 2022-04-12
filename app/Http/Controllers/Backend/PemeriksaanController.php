<?php

namespace App\Http\Controllers\Backend;

use Carbon\Carbon;
use App\Models\Pasien;
use App\Models\Penyakit;
use App\Models\Pemeriksaan;
use Illuminate\Http\Request;
use App\Exports\PemeriksaanExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class PemeriksaanController extends Controller
{
    public function index()
    {
        $pemeriksaan = Pemeriksaan::with(['pasien', 'penyakit'])->orderBy('created_at', 'desc')->get();
        $penyakits = Penyakit::orderBy('nama', 'asc')->get();

        return view('backend.pemeriksaan.index', [
            'pemeriksaan' => $pemeriksaan,
            'penyakits' => $penyakits
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $pasien = Pasien::where('nik', $data['nik'])->first();

        if (!$pasien) {
            Pasien::create([
                'nama' => $data['pasien'],
                'nik' => $data['nik']
            ]);
        }

        $pasien_baru = Pasien::where('nik', $data['nik'])->first();

        Pemeriksaan::create([
            'penyakit_id' => $data['penyakit'],
            'pasien_id' => $pasien_baru->id,
        ]);

        return redirect()->back();
    }

    public function update($id)
    {
        $data = Pemeriksaan::findOrFail($id);
        $time = Carbon::now()->toDateTimeString();

        $data->update([
            'status' => 'selesai',
            'waktu_selesai' => $time
        ]);

        return redirect()->back();
    }

    public function destroy($id)
    {
        $data = Pemeriksaan::findOrFail($id);

        $data->delete();

        return redirect()->back();
    }

    public function export(Request $request)
    {
        $request->validate([
            'tglMulai' => 'required|date',
            'tglSelesai' => 'required|date|after_or_equal:tglMulai',
        ],
        [
            'tglSelesai.after_or_equal' => 'Tanggal tidak boleh mundur',
        ]);

        $startDate = $request->tglMulai;
        $endDate = $request->tglSelesai;
        // $pemeriksaan = Pemeriksaan::whereBetween('created_at', [$startDate, $endDate])->get();

        return Excel::download(new PemeriksaanExport($startDate, $endDate), 'pemeriksaan.xlsx');
    }
}
