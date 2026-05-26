<?php

namespace App;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Coinbase
{

    /**
     * @var string $API_HOST API host for API calls
     */
    public static $API_HOST = 'api.coinbase.com';

    /**
     * @var int $MAX_TRADE_VOLUME Maximum trade volume in STORJ
     */
    public static $MAX_TRADE_VOLUME = 1;

    /**
     * @var int $COOLDOWN_MINS Cooldown in minutes between trades
     */
    public static $COOLDOWN_MINS = 10;

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

    /**
     * Get balance for a token
     * @param string $token Token type to get balance for
     * @return float The balance of the token
     */
    public function getBalance($token) {
        $account = $this->getAccounts($token);
        return (float) $account[1]['available_balance']['value'];
    }

    /**
     * Get the public trade pair product for two tokens or token/currency
     * @param string $token1 First token or currency
     * @param string $token2 Second token or currency
     * @return array Response from API
     */
    public function getTradePair($token1, $token2) {
        $tokenPair = $token1 . '-' . $token2;
        $resp = $this->doRequest('/market/products/' . $tokenPair);
        return $resp;
    }

    /**
     * Get past orders on the account
     * @return array Response from API
     */
    public function getOrders()
    {
        $orders = $this->doRequest('/orders/historical/batch');
        return $orders['orders'];
    }

    /**
     * Make a new Sell order
     * @param string $token1 First token or currency
     * @param string $token2 Second token or currency
     * @param float $volume Volume to sell
     * @param string $uuid Unique identifier for the order
     * @param string $side Side of the order (BUY or SELL) default is SELL
     */
    public function createOrder($token1, $token2, $volume, $uuid, $side = 'SELL') {
        $tokenPair = $token1 . '-' . $token2;

        $resp = $this->doRequest('/orders', 'POST', [
            'json' => [
                'side' => $side,
                'product_id' => $tokenPair,
                'client_order_id' => $uuid,
                'order_configuration' => [
                    'market_market_ioc' => [
                        'base_size' => (string) $volume,
                    ]
                ]
            ]
        ]);

        return $resp;
    }

    public function getOrder($id) {
        $order = $this->doRequest('/orders/historical/' . $id);
        return $order['order'];
    }

    public function getFills()
    {
        $fills = $this->doRequest('/orders/historical/fills');
        return $fills;
    }
}
