<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(){
        return User::all();
    }

    public function login(Request $request) {
        $creds = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $creds['email'])->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response(['error' => 1, 'message' => 'Kredencial te gabuara'], 401);
        }

        $user->tokens()->delete();

        $roles = $user->roles->pluck('name')->all();

        $plainTextToken = $user->createToken('hydra-api-token', $roles)->plainTextToken;

        return response(['error' => 0, 'id' => $user->id, 'token' => $plainTextToken], 200);
    }

    public function show(User $user) {
        return $user;
    }

    public function me(Request $request) {
        return $request->user();
    }

    public function destroy(User $user) {
        $adminRole = Role::where('name', 'admin')->first();
        $userRoles = $user->roles;

        if ($userRoles->contains($adminRole)) {
            //the current user is admin, then if there is only one admin - don't delete
            $numberOfAdmins = Role::where('name', 'admin')->first()->users()->count();
            if (1 == $numberOfAdmins) {
                return response(['error' => 1, 'message' => 'Nuk ka admina te tjere'], 409);
            }
        }

        $user->delete();

        return response(['error' => 0, 'message' => 'Perdoruesi u fshi']);
    }

    public function update(Request $request, User $user) {
        $user->name = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;
        $user->password = $request->password ? Hash::make($request->password) : $user->password;
        $user->email_verified_at = $request->email_verified_at ?? $user->email_verified_at;

        $loggedInUser = $request->user();
        if ($loggedInUser->id == $user->id) {
            $user->update();
        } elseif ($loggedInUser->tokenCan('admin')) {
            $user->update();
        } else {
            throw new MissingAbilityException('Not Authorized');
        }

        return $user;
    }

    public function store(Request $request) {
        $creds = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'name' => 'nullable|string',
        ]);

        $user = User::where('email', $creds['email'])->first();
        if ($user) {
            return response(['error' => 1, 'message' => 'user already exists'], 409);
        }

        $user = User::create([
            'email' => $creds['email'],
            'password' => Hash::make($creds['password']),
            'name' => $creds['name'],
        ]);

        $defaultRoleSlug = config('hydra.default_user_role_slug', 'user');
        $user->roles()->attach(Role::where('slug', $defaultRoleSlug)->first());

        return $user;
    }
}
