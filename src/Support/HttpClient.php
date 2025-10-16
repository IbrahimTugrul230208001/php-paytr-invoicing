<?php

namespace App\Support;

use RuntimeException;

class HttpClient
{
    public function postJson(string $url, array $headers, array $payload): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Unable to initialize cURL');
        }

        $headers = array_merge(['Content-Type: application/json'], $headers);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $responseBody = curl_exec($ch);
        if ($responseBody === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('HTTP request failed: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($responseBody, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Unable to decode JSON response: ' . json_last_error_msg());
        }

        return ['status' => $statusCode, 'body' => $decoded];
    }
}