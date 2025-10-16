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

    public function createPurchaseBill(array $purchaseBillData): array
    {
        if (empty($this->config['company_id'])) {
            throw new RuntimeException('Paraşüt company ID is not configured.');
        }

        if (empty($this->config['base_url'])) {
            throw new RuntimeException('Paraşüt base URL is not configured.');
        }

        $token = $this->getAccessToken();
        $url = rtrim($this->config['base_url'], '/') . '/v4/' . $this->config['company_id'] . '/purchase_bills';

        $response = $this->httpClient->postJson($url, [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
        ], $purchaseBillData);

        if ($response['status'] >= 400) {
            $this->logger->error('Failed to create purchase bill', $response);
            throw new RuntimeException('Paraşüt purchase bill creation failed with status ' . $response['status']);
        }

        $this->logger->info('Purchase bill created in Paraşüt', $response['body'] ?? []);

        return $response['body'];
    }

    private function getAccessToken(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        foreach (['base_url', 'client_id', 'client_secret', 'username', 'password'] as $key) {
            if (empty($this->config[$key])) {
                throw new RuntimeException(sprintf('Paraşüt configuration value "%s" is missing.', $key));
            }
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
