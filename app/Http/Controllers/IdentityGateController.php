<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IdentityGateController extends Controller
{
    public function showIdentityForm() {
        return view('identity.gate'); // Kita pakai folder 'identity' biar tidak tabrakan
    }

    public function storeIdentity(Request $request) {
        $request->validate([
            'negara' => 'required',
            'provinsi' => 'required',
            'kabupaten' => 'required',
            'kecamatan' => 'required',
            'desa' => 'required',
            'kampung' => 'nullable',
            'detail_alamat' => 'nullable',
            'photo' => 'required|image|max:2048'
        ]);

        $path = $request->file('photo')->store('profiles', 'public');

        auth()->user()->profile()->updateOrCreate(['user_id' => auth()->id()], ['profile_photo_path' => $path]);

        $addressData = $request->except(['photo', '_token']);
        // Kampung tidak boleh null di database, berikan default '-' jika kosong
        $addressData['kampung'] = $addressData['kampung'] ?? '-';

        auth()->user()->address()->updateOrCreate(['user_id' => auth()->id()], $addressData);

        return redirect()->route('dashboard')->with('success', 'Identitas berhasil diverifikasi.');
    }

    // Proxy API Wilayah Indonesia dari file lokal (Ibnux data-indonesia) dengan Fallback API Publik & Caching
    public function getProvinces() {
        try {
            $path = base_path('dataalamat/data-indonesia/provinsi.json');
            if (file_exists($path)) {
                return response()->json(json_decode(file_get_contents($path), true));
            }
            
            // Fallback ke API Publik jika submodule git tidak terkloning di produksi
            $data = Cache::remember('wilayah_provinces', 86400, function() {
                $response = Http::get('https://ibnux.github.io/data-indonesia/provinsi.json');
                return $response->ok() ? $response->json() : [];
            });

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Proxy Wilayah Gagal (Provinces): ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    public function getRegencies($provinceId) {
        try {
            $path = base_path("dataalamat/data-indonesia/kabupaten/{$provinceId}.json");
            if (file_exists($path)) {
                return response()->json(json_decode(file_get_contents($path), true));
            }

            // Fallback ke API Publik
            $data = Cache::remember("wilayah_regencies_{$provinceId}", 86400, function() use ($provinceId) {
                $response = Http::get("https://ibnux.github.io/data-indonesia/kabupaten/{$provinceId}.json");
                return $response->ok() ? $response->json() : [];
            });

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Proxy Wilayah Gagal (Regencies): ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    public function getDistricts($regencyId) {
        try {
            $path = base_path("dataalamat/data-indonesia/kecamatan/{$regencyId}.json");
            if (file_exists($path)) {
                return response()->json(json_decode(file_get_contents($path), true));
            }

            // Fallback ke API Publik
            $data = Cache::remember("wilayah_districts_{$regencyId}", 86400, function() use ($regencyId) {
                $response = Http::get("https://ibnux.github.io/data-indonesia/kecamatan/{$regencyId}.json");
                return $response->ok() ? $response->json() : [];
            });

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Proxy Wilayah Gagal (Districts): ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    public function getVillages($districtId) {
        try {
            $path = base_path("dataalamat/data-indonesia/kelurahan/{$districtId}.json");
            if (file_exists($path)) {
                return response()->json(json_decode(file_get_contents($path), true));
            }

            // Fallback ke API Publik
            $data = Cache::remember("wilayah_villages_{$districtId}", 86400, function() use ($districtId) {
                $response = Http::get("https://ibnux.github.io/data-indonesia/kelurahan/{$districtId}.json");
                return $response->ok() ? $response->json() : [];
            });

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Proxy Wilayah Gagal (Villages): ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}
