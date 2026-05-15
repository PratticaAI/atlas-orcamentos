<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user    = Auth::user();
        $profile = $user->profile ?? new UserProfile(['user_id' => $user->id]);

        return view('profile.edit', compact('user', 'profile'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'crea_cau'     => 'nullable|string|max:50',
            'phone'        => 'nullable|string|max:20',
            'accent_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|size:2',
            'about'        => 'nullable|string|max:500',
        ]);

        Auth::user()->profile()->updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return back()->with('success', 'Perfil atualizado com sucesso.');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|file|mimes:jpeg,png|max:2048', // 2MB máximo — AEGIS
        ]);

        // Valida MIME real (não apenas extensão) — AEGIS
        $file     = $request->file('logo');
        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
            return back()->with('error', 'Arquivo inválido. Envie apenas JPEG ou PNG.');
        }

        $path = $file->store("logos/user_" . Auth::id(), 'private');

        Auth::user()->profile()->updateOrCreate(
            ['user_id' => Auth::id()],
            ['logo_path' => $path]
        );

        return back()->with('success', 'Logo atualizado com sucesso.');
    }
}
