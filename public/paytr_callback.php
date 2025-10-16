<?php

use App\Invoice\PurchaseBillFactory;
use App\Parasut\ParasutClient;
use App\Paytr\CallbackHandler;
use App\Paytr\SignatureValidator;
use App\Support\HttpClient;
use App\Support\Logger;

require __DIR__ . '/../bootstrap.php';

$logger = new Logger($config['invoice']['log_path']);

try {
    $payload = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];

    $validator = new SignatureValidator(
        $config['paytr']['merchant_key'],
        $config['paytr']['merchant_salt']
    );
    $httpClient = new HttpClient();
    $parasutClient = new ParasutClient($httpClient, $logger, $config['parasut']);
    $purchaseBillFactory = new PurchaseBillFactory();

    $handler = new CallbackHandler(
        $validator,
        $parasutClient,
        $purchaseBillFactory,
        $logger,
        $config['invoice'],
        $config['paytr']
    );

    $result = $handler->handle($payload);

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Throwable $exception) {
    $logger->error('Unhandled exception', [
        'message' => $exception->getMessage(),
    ]);

    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $exception->getMessage()]);
}
