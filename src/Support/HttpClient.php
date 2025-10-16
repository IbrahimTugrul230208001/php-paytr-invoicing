<?php

namespace App\Support;

class HttpClient
{
    public function postJson(string $url, array $headers = [], array $payload = []): array
    {
        $headers = array_merge(['Content-Type: application/json','Accept: application/json'], $headers);
        $body = json_encode($payload);

        // Use cURL if available
        if (\function_exists('curl_init')) {
            $ch = \curl_init($url);
            \curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
            ]);
            $response = \curl_exec($ch);
            $headerSize = \curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $status = \curl_getinfo($ch, CURLINFO_HTTP_CODE);
            \curl_close($ch);

            $rawBody = $response !== false ? substr($response, (int)$headerSize) : '';
            return ['status' => (int)$status, 'body' => json_decode($rawBody, true) ?? []];
        }

        // Fallback without cURL
        $context = \stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => \implode("\r\n", $headers),
                'content' => $body,
                'ignore_errors' => true,
            ]
        ]);
        $raw = \file_get_contents($url, false, $context);
        $statusLine = $http_response_header[0] ?? 'HTTP/1.1 0';
        \preg_match('#\s(\d{3})\s#', $statusLine, $m);
        $status = isset($m[1]) ? (int)$m[1] : 0;
        return ['status' => $status, 'body' => json_decode($raw ?: '', true) ?? []];
    }
}