<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Events\Registered;
use App\Utils\Tokens;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $attributes = $request->validate([
                "first_name" => ["required", "string"],
                "last_name" => ["required", "string"],
                "email" => ["required", "string", "email", "unique:users,email"],
                "password" => ["required", Password::min(8)],
                "role" => ["required", "string"]
            ]);

            $user = User::create($attributes);
            $tokens = new Tokens();
            $token = $tokens->createVerificationToken($user->id, "{$user->first_name} {$user->last_name}", $user->email, $user->role);

            Auth::guard("base")->login($user);

            event(new Registered($user, $token));

            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
