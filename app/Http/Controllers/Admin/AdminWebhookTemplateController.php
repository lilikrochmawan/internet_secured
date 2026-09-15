<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WebhookAutoreply;
use Illuminate\Support\Facades\Storage;

class AdminWebhookTemplateController extends Controller
{
    public function index()
    {
        // Separating built-in and custom templates for the view
        $allTemplates = WebhookAutoreply::all();
        $templates = $allTemplates->whereIn('tipe', ['halo', 'paket_internet', 'tagihan_lunas', 'tagihan_tunggak'])->keyBy('tipe');
        $customTemplates = $allTemplates->whereNotIn('tipe', ['halo', 'paket_internet', 'tagihan_lunas', 'tagihan_tunggak']);
        
        return view('admin.custom-pesan.webhook', compact('templates', 'customTemplates'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'tipe' => 'required|string',
            'keyword' => 'nullable|string',
            'pesan' => 'required|string',
            'media' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $template = WebhookAutoreply::where('tipe', $request->tipe)->first();
        
        if (!$template) {
            return back()->with('error', 'Template tidak ditemukan.');
        }

        $template->keyword = $request->keyword;
        $template->pesan = $request->pesan;

        if ($request->hasFile('media')) {
            // Delete old media if exists
            if ($template->media_path && file_exists(public_path($template->media_path))) {
                unlink(public_path($template->media_path));
            }

            $file = $request->file('media');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/webhook'), $filename);
            
            $template->media_path = 'uploads/webhook/' . $filename;
        }

        // Feature to remove media
        if ($request->has('remove_media') && $request->remove_media == '1') {
            if ($template->media_path && file_exists(public_path($template->media_path))) {
                unlink(public_path($template->media_path));
            }
            $template->media_path = null;
        }

        $template->save();

        // Make the redirect message look better by converting custom_xxxx to just xxxx
        $tipeDisplay = str_replace(['_', 'custom '], [' ', ''], $request->tipe);
        return redirect()->route('admin.webhook_template.index')->with('success', 'Template ' . ucwords($tipeDisplay) . ' berhasil diperbarui!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_template' => 'required|string|max:100',
            'keyword' => 'required|string',
            'pesan' => 'required|string',
            'media' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $slug = 'custom_' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $request->nama_template));
        
        // Ensure unique
        $exists = WebhookAutoreply::where('tipe', $slug)->exists();
        if ($exists) {
            $slug .= '_' . time();
        }

        $mediaPath = null;
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/webhook'), $filename);
            $mediaPath = 'uploads/webhook/' . $filename;
        }

        WebhookAutoreply::create([
            'tipe' => $slug,
            'keyword' => $request->keyword,
            'pesan' => $request->pesan,
            'media_path' => $mediaPath,
            'status_aktif' => true,
        ]);

        return redirect()->route('admin.webhook_template.index')->with('success', 'Template baru berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $template = WebhookAutoreply::findOrFail($id);
        
        // Prevent deleting core templates just in case
        if (in_array($template->tipe, ['halo', 'paket_internet', 'tagihan_lunas', 'tagihan_tunggak'])) {
            return back()->with('error', 'Template bawaan sistem tidak boleh dihapus.');
        }

        if ($template->media_path && file_exists(public_path($template->media_path))) {
            unlink(public_path($template->media_path));
        }
        
        $template->delete();

        return redirect()->route('admin.webhook_template.index')->with('success', 'Template berhasil dihapus!');
    }
}
