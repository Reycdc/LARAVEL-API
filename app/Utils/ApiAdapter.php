<?php

namespace App\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiAdapter
{
    protected $http;

    public function __construct()
    {
        $this->http = new Client();
    }

    public function request($method, $url, $body = [], $headers = [])
    {
        $defaultHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $headers = array_merge($defaultHeaders, $headers);

        $options = ['headers' => $headers];

        if (!in_array(strtoupper($method), ['GET', 'HEAD'])) {
            $options['json'] = $body;
        } else {
            $options['query'] = $body;
        }

        try {
            $response = $this->http->request($method, $url, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'response' => $e->hasResponse()
                    ? json_decode($e->getResponse()->getBody()->getContents(), true)
                    : null,
            ];
        }
    }

    public function get($url, $params = [], $headers = [])
    {
        return $this->request('GET', $url, $params, $headers);
    }

    public function post($url, $body = [], $headers = [])
    {
        return $this->request('POST', $url, $body, $headers);
    }

    public function put($url, $body = [], $headers = [])
    {
        return $this->request('PUT', $url, $body, $headers);
    }

    public function delete($url, $body = [], $headers = [])
    {
        return $this->request('DELETE', $url, $body, $headers);
    }
}
