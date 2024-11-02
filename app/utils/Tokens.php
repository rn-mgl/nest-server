<?php

namespace App\Utils;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

class Tokens {

    protected string $key;
    protected bool $isAdmin;

    public function __construct(bool $isAdmin=false) {
        $this->key = $isAdmin ? env("ADMIN_VERIFICATION_KEY") : env("VERIFICATION_KEY");
        $this->isAdmin = $isAdmin;
    }

    public function createVerificationToken(int $identifier, string $name, string $email, string $role) {

        $payload = [
            ($this->isAdmin  ? "admin" : "user") => $identifier,
            "name" => $name,
            "email" => $email,
            "role" => $role,
            "iss" => env("TOKEN_ISSUER"),
            "aud" => env("TOKEN_AUDIENCE"),
            "iat" => Carbon::now()->timestamp,
            "exp" => Carbon::now()->addDay()->timestamp,
        ];

        $token = JWT::encode($payload, $this->key, "HS256");

        return $token;
    }

    public function decodeVerificationToken(string $token) {

        $decoded = JWT::decode($token, new Key($this->key, "HS256"));
        return $decoded;

    }

    public function verifyTokenMetadata(stdClass $decodedToken) {

        // check if expired
        $expiration = Carbon::createFromTimestamp($decodedToken->exp);

        if (Carbon::now()->greaterThanOrEqualTo($expiration)) {
            return false;
        }

        $correctMetadata = $decodedToken->iss === env("TOKEN_ISSUER") && $decodedToken->aud === env("TOKEN_AUDIENCE");

        return $correctMetadata;

    }

}
