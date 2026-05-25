<?php

namespace App;

use Firebase\JWT\JWT;

class Coinbase
{
    /**
     * Get a Coinbase JWT token
     */
    public function getJWT()
    {
        $keyName =config('coinbase.key');
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
}
