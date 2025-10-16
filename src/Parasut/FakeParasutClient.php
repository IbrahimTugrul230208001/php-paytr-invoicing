<?php
namespace App\Parasut;

use App\Support\Logger;

class FakeParasutClient
{
    public function __construct(private Logger $logger) {}

    public function createPurchaseBill(array $purchaseBillData): array
    {
        $this->logger->info('FAKE createPurchaseBill called', $purchaseBillData);

        return [
            'data' => [
                'id' => 'PB-FAKE-0001',
                'type' => 'purchase_bills',
                'attributes' => [
                    'invoice_no' => 'PB-FAKE-2025-0001',
                    'status' => 'draft',
                    'issue_date' => date('Y-m-d'),
                ],
            ],
            'meta' => ['dummy' => true],
        ];
    }
}
