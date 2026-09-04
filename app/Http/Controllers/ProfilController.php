<?php

namespace App\Http\Controllers;

use App\Helpers\ImageSyncHelper;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    public function __construct(private ImageService $imageService)
    {
    }

    public function edit()
    {
        return view('profil.edit', [
            'utilisateur' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $utilisateur = Auth::user();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:191|unique:utilisateurs,email,' . $utilisateur->id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'photo_profil.image' => 'Le fichier doit être une image.',
            'photo_profil.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ]);

        $utilisateur->nom = $validated['nom'];
        $utilisateur->prenom = $validated['prenom'];
        $utilisateur->email = $validated['email'];
        $utilisateur->telephone = $validated['telephone'] ?? null;
        $utilisateur->adresse = $validated['adresse'] ?? null;

        if ($request->hasFile('photo_profil')) {
            if ($utilisateur->photo_profil) {
                Storage::disk('public')->delete($utilisateur->photo_profil);
            }

            $photoPath = $this->imageService->resizeAndSaveImage(
                $request->file('photo_profil'),
                'profile_images',
                300,
                300
            );

            $utilisateur->photo_profil = $photoPath;
            ImageSyncHelper::syncImage($photoPath);
        }

        $utilisateur->save();

        return redirect()->route('profil.edit')
            ->with('success', 'Votre profil a été mis à jour avec succès.');
    }
}
