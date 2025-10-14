<?php

namespace App\Utils;

use Carbon\Carbon;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

class Tokens
{

    protected string $key;
    protected bool $isAdmin;

    /**
     * Initialize the Tokens class
     * @param string $key The type of key to be used. VERIFICATION | SESSION | RESET
     */
    public function __construct(string $key)
    {

        if (!env("{$key}_KEY")) {
            throw new Exception("The {$key} is not defined in the environment.");
        }

        $this->key = env("{$key}_KEY");
    }

    public function createToken(int $identifier, string $name, string $email, array $role)
    {

        $payload = [
            "user" => $identifier,
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

    public function decodeToken(string $token)
    {

        $decoded = JWT::decode($token, new Key($this->key, "HS256"));
        return $decoded;

    }

    public function verifyMetadata(stdClass $decodedToken)
    {

        // check if expired
        $expiration = Carbon::createFromTimestamp($decodedToken->exp);

        if (Carbon::now()->greaterThanOrEqualTo($expiration)) {
            return false;
        }

        $correctMetadata = $decodedToken->iss === env("TOKEN_ISSUER") && $decodedToken->aud === env("TOKEN_AUDIENCE");

        return $correctMetadata;

    }

}
