<?php

namespace App;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Coinbase
{
    public static $JWT_LIFETIME = '100';

    /**
     * Get a new JWT directly from Coinbase.
     * @return string The JWT token.
     */
    public function issueJWT()
    {
        $keyName = config('coinbase.key');
        $keySecret = str_replace('\\n', "\n", config('coinbase.secret'));

        $request_method = 'GET';
        $url = 'api.coinbase.com';
        $request_path = '/api/v3/brokerage/accounts';

        $uri = $request_method . ' ' . $url . $request_path;
        $privateKeyResource = openssl_pkey_get_private($keySecret);
        if (!$privateKeyResource) {
            throw new \Exception('Private key is not valid');
        }
        $time = time();
        $nonce = bin2hex(random_bytes(16));  // Generate a 32-character hexadecimal nonce
        $jwtPayload = [
            'sub' => $keyName,
            'iss' => 'cdp',
            'nbf' => $time,
            'exp' => $time + 120,  // Token valid for 120 seconds from now
            'uri' => $uri,
        ];
        $headers = [
            'typ' => 'JWT',
            'alg' => 'ES256',
            'kid' => $keyName,  // Key ID header for JWT
            'nonce' => $nonce  // Nonce included in headers for added security
        ];
        $jwtToken = JWT::encode($jwtPayload, $privateKeyResource, 'ES256', $keyName, $headers);
        return $jwtToken;
    }

    /**
     * Get a Coinbase JWT, and check the cache.
     * @return string The JWT token.
     */
    public function getJWT()
    {
        $cache_key = 'coinbase_jwt';

        if (Cache::has($cache_key)) {
            return Cache::get($cache_key);
        }

        Log::Info('JWT fetch miss, getting a new one');

        $jwt = $this->issueJWT();

        Cache::add($cache_key, $jwt, self::$JWT_LIFETIME);
        return $jwt;
    }
}
