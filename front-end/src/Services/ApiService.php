<?php
namespace Services;

class ApiService
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = getenv('API_URL');
    }

    public function fetch($endpoint, $method = 'GET', $data = null): array
    {
        $url = $this->apiUrl . $endpoint;
        $headers = ['Content-Type: application/json'];
        if (isset($_SESSION['token'])) {
            $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
        }

        $options = [
            'http' => [
                'method' => strtoupper($method),
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
            ]
        ];

        if ($data && strtoupper($method) !== 'GET') {
            $options['http']['content'] = json_encode($data);
        }

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        // Get response headers to check HTTP status
        $responseHeaders = $http_response_header ?? [];
        $statusLine = $responseHeaders[0] ?? '';
        preg_match('{HTTP/\S*\s(\d{3})}', $statusLine, $match);
        if ($response === false) {
            error_log('API request failed');
            return [
                'success' => false,
                'error' => 'API request failed or endpoint not found.',
            ];
        }

        $json = json_decode($response, true);

        if ($json === null) {
            error_log('Invalid API response');
            return [
                'success' => false,
                'error' => 'Invalid API response',
            ];
        }

        return $json;
    }
}
