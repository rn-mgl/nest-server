<?php

namespace App\Utils;

use Carbon\Carbon;
use Firebase\JWT\JWT;

class Tokens {

    public static function createVerificationToken(string $user, string $name, string $email, string $role) {

        $payload = [
            "user" => $user,
            "name" => $name,
            "email" => $email,
            "role" => $role,
            "iss" => "Nest",
            "aud" => env("APP_URL"),
            "iat" => Carbon::now()->timestamp,
            "exp" => Carbon::now()->addDay()->timestamp,
        ];

        $token = JWT::encode($payload, env("VERIFICATION_KEY"), "HS256");

        return $token;
    }

}
