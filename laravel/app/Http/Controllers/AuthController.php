<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     * body: name, email, password, password_confirmation, role (client|provider)
     * opcioni profil: headline, bio, github_url, portfolio_url
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required','string','min:2','max:100'],
            'email'                 => ['required','email:rfc,dns','max:255','unique:users,email'],
            'password'              => ['required','string','min:8','confirmed'],
            'role'                  => ['required','in:client,provider'],

            // opcioni profil podaci:
            'headline'              => ['nullable','string','max:120'],
            'bio'                   => ['nullable','string'],
            'github_url'            => ['nullable','url','max:255'],
            'portfolio_url'         => ['nullable','url','max:255'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => $data['role'],
            ]);

            Profile::create([
                'user_id'       => $user->id,
                'headline'      => $data['headline'] ?? null,
                'bio'           => $data['bio'] ?? null,
                'github_url'    => $data['github_url'] ?? null,
                'portfolio_url' => $data['portfolio_url'] ?? null,
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
     * body: email, password
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
        $user = $request->user()->load('profile');
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
