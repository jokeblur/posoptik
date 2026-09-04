<?php

namespace App\Http\Controllers;

use App\Models\DisplayMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DisplayMediaController extends Controller
{
    public function index()
    {
        return view('display.media', ['media' => DisplayMedia::orderBy('urutan')->latest()->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:120'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
            'media' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm,ogg', 'max:51200'],
        ], [
            'media.required' => 'File wajib dipilih.',
            'media.mimes' => 'File harus berupa gambar (JPG, PNG, WEBP) atau video (MP4, WEBM, OGG).',
            'media.max' => 'Ukuran file maksimal 50 MB.',
        ]);

        $file = $request->file('media');
        $type = str_starts_with((string) $file->getMimeType(), 'video/') ? 'video' : 'image';

        $item = DisplayMedia::create([
            'judul' => $validated['judul'],
            'tipe' => $type,
            'path' => $file->store('display-media', 'public'),
            'is_active' => true,
            'urutan' => $validated['urutan'] ?? 0,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Media promosi berhasil diupload.',
                'media' => $item,
            ]);
        }

        return back()->with('success', 'Media promosi berhasil diupload.');
    }

    public function toggle(DisplayMedia $displayMedia)
    {
        $displayMedia->update(['is_active' => !$displayMedia->is_active]);

        return back()->with('success', 'Status media berhasil diperbarui.');
    }

    public function destroy(DisplayMedia $displayMedia)
    {
        Storage::disk('public')->delete($displayMedia->path);
        $displayMedia->delete();

        return back()->with('success', 'Media promosi berhasil dihapus.');
    }
}