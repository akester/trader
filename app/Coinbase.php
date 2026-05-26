<?php

namespace App;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Coinbase
{
    public static $JWT_LIFETIME = '100';

    public static $API_HOST = 'api.coinbase.com';

    /**
     * GuzzleHTTP client
     * @var $client GuzzleHTTP client instance
     */
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => "https://" . self::$API_HOST,
            'timeout' => 5,
        ]);
    }

    /**
     * Do a request to Coinbase, appending the URI needed and our auth headers
     * @param string $path The path to the API endpoint
     * @param string $method The HTTP method (default: GET)
     * @param array $params Additional parameters for the request
     * @return mixed JSON
     */
    private function doRequest($path, $method = 'get', $params = [])
    {
        $path = "/api/v3/brokerage" . $path;
        $token = $this->issueJWT($method, $path);

        $all_params = array_merge($params, [
            'headers' => [
                'Authorization' => "Bearer $token",
            ]
        ]);

        $resp = $this->client->request($method, $path, $all_params);
        return json_decode((string) $resp->getBody(), true);
    }

    /**
     * Get a new JWT directly from Coinbase.
     * @return string The JWT token.
     */
    private function issueJWT($method = 'get', $path = '/api/v3/brokerage/accounts')
    {
        $keyName = config('coinbase.key');
        $keySecret = str_replace('\\n', "\n", config('coinbase.secret'));

        $uri = strtoupper($method) . ' ' . self::$API_HOST . $path;
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
     * Get accounts (wallets) on the Coinbase account.
     * @param string $token Token type to get wallet for, default get all
     * @return array The list of wallets
     */
    public function getAccounts($token = '')
    {
        $accounts = $this->doRequest('/accounts')['accounts'];
        if ($token) {
            $accounts = array_filter($accounts, function ($account) use ($token) {
                return $account['currency'] == $token;
            });

            // If we filter, downstream logic expects just one so catch if we're doing something wrong here
            if (count($accounts) > 1) {
                throw new \Exception('Expected exactly one account with the specified token');
            }
        }

        return $accounts;
    }

    public function getOrders()
    {
        $orders = $this->doRequest('/orders/historical/batch');
        return $orders;
    }

    public function getFills()
    {
        $fills = $this->doRequest('/orders/historical/fills');
        return $fills;
    }
}
