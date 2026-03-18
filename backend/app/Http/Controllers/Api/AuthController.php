<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:8|confirmed',
            'barangay_id' => 'required|exists:barangays,id',
        ]);

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => $request->password,
            'role'        => 'resident',
            'barangay_id' => $request->barangay_id,
        ]);

        $token = $user->createToken('auth_token', ['role:resident'])->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user'    => $this->userData($user->load('barangay')),
            'token'   => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with('barangay')->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token', ['role:' . $user->role])->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user'    => $this->userData($user),
            'token'   => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->userData($request->user()->load('barangay')),
        ]);
    }

    private function userData(User $user): array
    {
        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => $user->role,
            'barangay' => $user->barangay ? [
                'id'           => $user->barangay->id,
                'name'         => $user->barangay->name,
                'municipality' => $user->barangay->municipality,
            ] : null,
        ];
    }

    // ── Update profile ───────────────────────────────────────
public function updateProfile(Request $request)
{
    $request->validate([
        'name'  => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $request->user()->id,
    ]);

    $request->user()->update([
        'name'  => $request->name,
        'email' => $request->email,
    ]);

    return response()->json([
        'message' => 'Profile updated.',
        'user'    => $this->userData($request->user()->fresh()->load('barangay')),
    ]);
}

// ── Change password ──────────────────────────────────────
public function changePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'password'         => 'required|min:8|confirmed',
    ]);

    if (! \Hash::check($request->current_password, $request->user()->password)) {
        return response()->json([
            'message' => 'Current password is incorrect.',
        ], 422);
    }

    $request->user()->update([
        'password' => $request->password,
    ]);

    return response()->json(['message' => 'Password updated successfully.']);
}
}