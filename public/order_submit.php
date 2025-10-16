<?php

use App\Invoice\PurchaseBillFactory;
use App\Parasut\HtmlInvoiceClient;
use App\Support\Logger;

require __DIR__ . '/../bootstrap.php';

$logger = new Logger($config['invoice']['log_path'] ?? null);

$required = ['supplier_id','item_description','quantity','unit_price_tl','vat_rate','currency','issue_date','due_date','merchant_oid'];
foreach ($required as $k) {
    if (!isset($_POST[$k]) || $_POST[$k] === '') {
        http_response_code(422);
        echo "<h3>Hata</h3><p>Eksik alan: {$k}</p><p><a href='/index.php'>Geri dön</a></p>";
        exit;
    }
}

try {
    // Emulate a PAYTR-like payload so we can reuse the factory
    $payload = [
        'merchant_oid'   => (string) $_POST['merchant_oid'],
        'product_name'   => (string) $_POST['item_description'],
        'payment_amount' => (int) round(((float) $_POST['unit_price_tl']) * 100),
        'status'         => 'success',
    ];

    $defaults = [
        'supplier_id'        => (string) $_POST['supplier_id'],
        'vat_rate'           => (int) $_POST['vat_rate'],
        'currency'           => (string) $_POST['currency'],
        'quantity'           => (float) $_POST['quantity'],
        'unit_price'         => (float) $_POST['unit_price_tl'],
        'issue_date'         => (string) $_POST['issue_date'],
        'due_date'           => (string) $_POST['due_date'],
        'product_id'         => $_POST['product_id']   ?: null,
        'warehouse_id'       => $_POST['warehouse_id'] ?: null,
        'category_id'        => $_POST['category_id']  ?: null,
        'description'        => $_POST['description']  ?: null,
        'description_prefix' => 'Web Form Siparişi',
        'item_description'   => (string) $_POST['item_description'],
    ];

    $factory = new PurchaseBillFactory();
    $purchaseBillPayload = $factory->fromPaytrPayload($payload, $defaults);

    $client = new HtmlInvoiceClient($logger, ['brand' => 'Web Form Ön Fatura']);
    $result = $client->createPurchaseBill($purchaseBillPayload);

    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    echo $result['html'];
} catch (Throwable $e) {
    $logger->error('HTML invoice render failed', ['error' => $e->getMessage()]);
    http_response_code(500);
    echo "<h1 style='color:#ef4444'>Hata</h1><p>{$e->getMessage()}</p><p><a href='/index.php'>Geri dön</a></p>";
}