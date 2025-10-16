<?php

namespace App\Parasut;

use App\Support\HttpClient;
use App\Support\Logger;
use RuntimeException;

class ParasutClient
{
    private HttpClient $httpClient;
    private Logger $logger;
    private array $config;
    private ?string $accessToken = null;

    public function __construct(HttpClient $httpClient, Logger $logger, array $config)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->config = $config;
    }

    public function createInvoice(array $invoiceData): array
    {
        $token = $this->getAccessToken();
        $url = rtrim($this->config['base_url'], '/') . '/v4/' . $this->config['company_id'] . '/sales_invoices';

        $response = $this->httpClient->postJson($url, [
            'Authorization: Bearer ' . $token,
        ], $invoiceData);

        if ($response['status'] >= 400) {
            $this->logger->error('Failed to create invoice', $response);
            throw new RuntimeException('Paraşüt invoice creation failed with status ' . $response['status']);
        }

        $this->logger->info('Invoice created in Paraşüt', $response['body']);

        return $response['body'];
    }

    private function getAccessToken(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $url = rtrim($this->config['base_url'], '/') . '/oauth/token';

        $payload = [
            'grant_type' => 'password',
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'username' => $this->config['username'],
            'password' => $this->config['password'],
        ];

        $response = $this->httpClient->postJson($url, [], $payload);

        if ($response['status'] >= 400 || empty($response['body']['access_token'])) {
            $this->logger->error('Failed to obtain Paraşüt access token', $response);
            throw new RuntimeException('Unable to authenticate with Paraşüt');
        }

        $this->accessToken = $response['body']['access_token'];

        return $this->accessToken;
    }
}