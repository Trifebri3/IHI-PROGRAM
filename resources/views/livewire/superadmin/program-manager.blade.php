<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithFileUploads;

    public $programs;
    public $adminPrograms;

    // Form Fields
    public $name, $description, $quota, $start_date, $end_date, $status = 'draft';
    public $banner;
    public $logo;
    public $selected_admin_id;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        // Mengambil master program beserta relasi user pengelolanya (managers)
        $this->programs = Program::with('managers')->latest()->get();
        // Mengambil semua user yang bertindak sebagai Admin Program untuk didelegasikan
        $this->adminPrograms = User::role('Admin Program')->get();
    }

    /**
     * Memproses Penyimpanan Master Program & Arsitektur Delegasi
     */
    public function saveProgram()
    {
        // 1. Validasi Input Form
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quota' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:draft,published',
            'logo' => 'nullable|image|max:2048', // Maksimal 2MB
            'banner' => 'nullable|image|max:2048', // Maksimal 2MB
            'selected_admin_id' => 'required|exists:users,id',
        ], [
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh mendahului tanggal mulai.',
            'selected_admin_id.required' => 'Wajib menunjuk Direktur Pelaksana / Admin Program.',
        ]);

        try {
            // Menggunakan DB Transaction agar jika upload gagal, data DB tidak ikut kotor
            DB::transaction(function () {

                // 2. Proses Upload File Fisik (Jika Ada)
                $logoPath = null;
                $bannerPath = null;

                if ($this->logo) {
                    $logoPath = $this->logo->store('programs/logos', 'public');
                }

                if ($this->banner) {
                    $bannerPath = $this->banner->store('programs/banners', 'public');
                }

                // 3. Simpan data ke tabel programs
                $program = Program::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'quota' => $this->quota,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'status' => $this->status,
                    'logo_path' => $logoPath,
                    'banner_path' => $bannerPath,
                ]);

                // 4. Delegasikan ke Admin Program terpilih (Menyimpan ke Tabel Pivot / many-to-many)
                // Asumsi nama relasi di model Program adalah 'managers' atau sesuaikan dengan table pivot Anda
                if (method_exists($program, 'managers')) {
                    $program->managers()->attach($this->selected_admin_id);
                }
            });

            // 5. Reset input form & Refresh komponen data
            $this->reset(['name', 'description', 'quota', 'start_date', 'end_date', 'status', 'logo', 'banner', 'selected_admin_id']);
            $this->loadData();

            session()->flash('message', 'Master Program & Arsitektur Delegasi berhasil diterbitkan!');

        } catch (\Exception $e) {
            // Menangkap error jika terjadi kegagalan sistem database
            session()->flash('error', 'Gagal menerbitkan program: ' . $e->getMessage());
        }
    }

    /**
     * Likuidasi Program dan berkas fisiknya
     */
    public function deleteProgram($id)
    {
        $program = Program::find($id);

        if ($program) {
            // Hapus file fisik banner dari storage jika ada
            if ($program->banner_path) {
                Storage::disk('public')->delete($program->banner_path);
            }

            // Hapus file fisik logo dari storage jika ada
            if ($program->logo_path) {
                Storage::disk('public')->delete($program->logo_path);
            }

            // Hapus data dari DB (Relasi pivot akan otomatis terhapus jika diset cascade di database)
            $program->delete();

            $this->loadData();
            session()->flash('message', 'Program berhasil dihapus dari sistem.');
        }
    }
}; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="p-6 bg-white rounded-2xl shadow-sm border border-emerald-50">
        <div class="flex items-center space-x-2 pb-4 mb-4 border-b border-gray-100">
            <div class="p-2 bg-emerald-50 rounded-lg text-emerald-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Buat Master Program</h3>
        </div>

        @if (session()->has('message'))
            <div class="p-3 mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center">
                <svg class="w-4 h-4 mr-2 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-3 mb-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium flex items-center">
                <span class="font-bold mr-1">Error:</span> {{ session('error') }}
            </div>
        @endif

        <form wire:submit="saveProgram" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Nama Program</label>
                <input type="text" wire:model="name" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition text-sm" placeholder="Cth: Beasiswa Pemuda Pelopor 2026" required>
                @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Logo Ikon</label>
                    <input type="file" wire:model="logo" accept="image/*" class="w-full p-1 mt-1 border border-slate-200 rounded-xl text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition">
                    @error('logo') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror

                    @if ($logo)
                        <div class="mt-2 text-center">
                            <img src="{{ $logo->temporaryUrl() }}" class="w-12 h-12 object-cover rounded-full mx-auto border border-emerald-200 p-0.5 shadow-sm">
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Banner Sampul</label>
                    <input type="file" wire:model="banner" accept="image/*" class="w-full p-1 mt-1 border border-slate-200 rounded-xl text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition">
                    @error('banner') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror

                    @if ($banner)
                        <div class="mt-2">
                            <img src="{{ $banner->temporaryUrl() }}" class="w-full h-12 object-cover rounded-lg border border-emerald-200 shadow-sm">
                        </div>
                    @endif
                </div>
            </div>

            <div wire:loading wire:target="banner, logo" class="text-xs text-emerald-600 animate-pulse font-medium">Mengunggah berkas ke server aman...</div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Deskripsi Ringkas</label>
                <textarea wire:model="description" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition text-sm" rows="3" placeholder="Tuliskan objektif utama program..."></textarea>
                @error('description') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Kuota Peserta</label>
                    <input type="number" wire:model="quota" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition text-sm" required>
                    @error('quota') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Status Awal</label>
                    <select wire:model="status" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition text-sm bg-white">
                        <option value="draft">Draft (Sembunyi)</option>
                        <option value="published">Published (Publik)</option>
                    </select>
                    @error('status') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Tanggal Mulai</label>
                    <input type="date" wire:model="start_date" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition text-sm" required>
                    @error('start_date') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Tanggal Selesai</label>
                    <input type="date" wire:model="end_date" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition text-sm" required>
                    @error('end_date') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="p-4 bg-gradient-to-br from-emerald-50 to-white rounded-2xl border border-emerald-100 shadow-inner">
                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-900">Tunjuk Direktur Pelaksana</label>
                <select wire:model="selected_admin_id" class="w-full p-2.5 mt-2 border border-emerald-200 rounded-xl bg-white shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" required>
                    <option value="">-- Pilih Admin Program --</option>
                    @foreach($adminPrograms as $admin)
                        <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                    @endforeach
                </select>
                @error('selected_admin_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="w-full py-3 bg-gradient-to-r from-emerald-600 to-green-700 text-white font-bold rounded-xl hover:from-emerald-700 hover:to-green-800 transition shadow-md shadow-emerald-100 flex items-center justify-center space-x-2 cursor-pointer">
                <span>Terbitkan & Delegasikan</span>
            </button>
        </form>
    </div>

    <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-100 lg:col-span-2">
        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2"></span>
            Daftar Master Program & Arsitektur Delegasi
        </h3>

        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 text-slate-600 text-xs uppercase tracking-wider font-bold border-b border-slate-100">
                        <th class="p-3">Identitas Visual</th>
                        <th class="p-3">Nama Program</th>
                        <th class="p-3">Kapasitas & Periode</th>
                        <th class="p-3">Penanggung Jawab</th>
                        <th class="p-3 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @forelse($programs as $program)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-3">
                                <div class="relative w-24 h-14 rounded-lg overflow-hidden border shadow-inner bg-slate-100">
                                    @if($program->banner_path)
                                        <img src="{{ asset('storage/' . $program->banner_path) }}" class="w-full h-full object-cover">
                                    @endif

                                    <div class="absolute bottom-1 right-1 w-7 h-7 rounded-full bg-white p-0.5 border shadow-sm flex items-center justify-center overflow-hidden">
                                        @if($program->logo_path)
                                            <img src="{{ asset('storage/' . $program->logo_path) }}" class="w-full h-full object-cover rounded-full">
                                        @else
                                            <div class="w-full h-full bg-emerald-600 rounded-full flex items-center justify-center text-[8px] text-white font-extrabold">
                                                {{ substr($program->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="p-3">
                                <div class="font-bold text-slate-800">{{ $program->name }}</div>
                                <div class="mt-1">
                                    @if($program->status === 'published')
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">PUBLISHED</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-600 border border-slate-200">DRAFT</span>
                                    @endif
                                </div>
                            </td>

                            <td class="p-3">
                                <div class="text-xs font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-md inline-block">{{ $program->quota }} Kursi Peserta</div>
                                <div class="text-[11px] text-slate-500 mt-1 font-medium flex items-center">
                                    <svg class="w-3 h-3 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ date('d M Y', strtotime($program->start_date)) }} - {{ date('d M Y', strtotime($program->end_date)) }}
                                </div>
                            </td>

                            <td class="p-3">
                                @forelse($program->managers as $manager)
                                    <div class="flex items-center space-x-1.5">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                        <span class="font-bold text-slate-700 text-xs">{{ $manager->name }}</span>
                                    </div>
                                @empty
                                    <span class="text-rose-500 text-xs font-medium italic flex items-center">
                                        ⚠️ Menunggu Otoritas
                                    </span>
                                @endforelse
                            </td>

                            <td class="p-3 text-center">
                                <button wire:click="deleteProgram({{ $program->id }})" wire:confirm="Apakah Anda yakin ingin melikuidasi program ini beserta seluruh dokumen fisik dari server?" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-xl transition cursor-pointer" title="Hapus Program">
                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400 italic">
                                Belum ada kompilasi program yang dibentuk. Silakan buat melalui panel kiri.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
