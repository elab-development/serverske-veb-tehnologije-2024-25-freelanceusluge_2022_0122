<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     * body (multipart/form-data):
     *  - name, email, password, password_confirmation, role (client|provider)
     *  - optional profile: headline, bio, github_url, portfolio_url
     *  - optional files: avatar (image), banner (image)
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required','string','min:2','max:100'],
            'email'                 => ['required','email:rfc,dns','max:255','unique:users,email'],
            'password'              => ['required','string','min:8','confirmed'],
            'role'                  => ['required','in:client,provider'],

            // profil
            'headline'              => ['nullable','string','max:120'],
            'bio'                   => ['nullable','string'],
            'github_url'            => ['nullable','url','max:255'],
            'portfolio_url'         => ['nullable','url','max:255'],

            // fajlovi
            'avatar'                => ['nullable', File::image()->types(['jpg','jpeg','png','webp'])->max(5 * 1024)], // 5MB
            'banner'                => ['nullable', File::image()->types(['jpg','jpeg','png','webp'])->max(8 * 1024)], // 8MB
        ]);

        // --- HIBP (Have I Been Pwned) provera lozinke preko range API-ja (bez ključa) ---
        try {
            $sha1   = strtoupper(sha1($data['password']));
            $prefix = substr($sha1, 0, 5);
            $suffix = substr($sha1, 5);

            $resp = Http::withHeaders(['User-Agent' => 'FreelanceApp/1.0'])
                ->timeout(5)
                ->get("https://api.pwnedpasswords.com/range/{$prefix}");

            if ($resp->ok()) {
                $pwned = collect(explode("\n", trim($resp->body())))
                    ->some(function ($line) use ($suffix) {
                        [$sfx, $count] = array_pad(explode(':', trim($line)), 2, 0);
                        return strtoupper($sfx) === $suffix && (int)$count > 0;
                    });

                if ($pwned) {
                    return response()->json([
                        'message'  => 'Password je kompromitovan u poznatim curenjima podataka. Izaberi drugi.',
                        // klijent može sam da predloži novi; namerno ne nudimo nasumičan ovde
                    ], 422);
                }
            }
        } catch (\Throwable $e) {
            // Ako API ne radi/timeout — ne blokiramo registraciju.
        }

        // --- Kreiranje user + profil (sa uploadom i UI Avatars fallback-om) ---
        $user = DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => $data['role'],
            ]);

            // upload fajlova (ako postoje)
            $avatarPath = null;
            $bannerPath = null;

            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store("profiles/{$user->id}", 'public');
            }
            if ($request->hasFile('banner')) {
                $bannerPath = $request->file('banner')->store("profiles/{$user->id}", 'public');
            }

            // UI Avatars fallback (ako avatar nije poslat)
            if (!$avatarPath) {
                try {
                    $uiUrl = 'https://ui-avatars.com/api/?'.http_build_query([
                        'name'       => $user->name,
                        'background' => 'random',
                        'bold'       => 'true',
                        'format'     => 'png',
                        'size'       => 256,
                    ]);
                    $img = Http::timeout(5)->get($uiUrl);
                    if ($img->ok()) {
                        $avatarPath = "profiles/{$user->id}/avatar.png";
                        Storage::disk('public')->put($avatarPath, $img->body());
                    }
                } catch (\Throwable $e) {
                    // tišina — registracija ide dalje i bez avatara
                }
            }

            Profile::create([
                'user_id'       => $user->id,
                'headline'      => $data['headline'] ?? null,
                'bio'           => $data['bio'] ?? null,
                'github_url'    => $data['github_url'] ?? null,
                'portfolio_url' => $data['portfolio_url'] ?? null,
                'avatar_path'   => $avatarPath,
                'banner_path'   => $bannerPath,
            ]);

            return $user->load('profile');
        });

        // automatski login + token
        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user),
        ], 201);
    }

    /**
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        $creds = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        if (!Auth::attempt($creds)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        /** @var \App\Models\User $user */
        $user  = $request->user()->load('profile');
        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user),
        ]);
    }

    /**
     * GET /api/auth/me   (auth:sanctum)
     */
    public function me(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user()->load('profile');
        return new UserResource($user);
    }

    /**
     * POST /api/auth/logout   (auth:sanctum)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
