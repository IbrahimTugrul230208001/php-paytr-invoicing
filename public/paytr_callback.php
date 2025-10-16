<?php

use App\Invoice\PurchaseBillFactory;
use App\Parasut\HtmlInvoiceClient;
use App\Paytr\DummySignatureValidator;
use App\Paytr\CallbackHandler;
use App\Support\Logger;

require __DIR__ . '/../bootstrap.php';

$invoiceConfig = array_merge([
    'supplier_id' => 'DUMMY-SUPPLIER-ID',
    'vat_rate' => 18,
    'currency' => 'TRY',
    'description_prefix' => 'Dummy PAYTR purchase bill',
    'item_description' => 'Dummy item',
    'log_path' => null,
], $config['invoice'] ?? []);

$logger = new Logger($invoiceConfig['log_path']);

try {
    $payload = require dirname(__DIR__) . '/tests/fixtures/paytr_callback_sample.php';

    $handler = new CallbackHandler(
        new DummySignatureValidator(),
        new HtmlInvoiceClient($logger, ['brand' => 'Demo Ön Fatura']),
        new PurchaseBillFactory(),
        $logger,
        $invoiceConfig,
        ['use_dummy_payload' => true]
    );

    $result = $handler->handle($payload);

    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    echo $result['purchase_bill']['html'] ?? '<p>Render error</p>';
} catch (Throwable $exception) {
    $logger->error('Unhandled exception', ['message' => $exception->getMessage()]);
    http_response_code(500);
    echo "<h1 style='color:#ef4444'>Hata</h1><p>{$exception->getMessage()}</p><p><a href='/index.php'>Geri dön</a></p>";
}
