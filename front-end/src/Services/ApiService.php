<?php
namespace Services;

class ApiService {
    private $apiUrl;

    public function __construct() {
        $this->apiUrl = getenv('API_URL');
    }

    public function fetch($endpoint, $method = 'GET', $data = null) : array {
        $url = $this->apiUrl . $endpoint;
        $options = [
            'http' => [
                'method' => strtoupper($method),
                'header' => 'Content-Type: application/json',
            ]
        ];
        if ($data && strtoupper($method) !== 'GET') {
            $options['http']['content'] = json_encode($data);
        }
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return [
                'success' => false,
                'error' => 'API request failed or endpoint not found.'
            ];
        }
        $json = json_decode($response, true);
        if ($json === null) {
            return [
                'success' => false,
                'error' => 'Invalid API response.'
            ];
        }
        return $json;
    }
}
