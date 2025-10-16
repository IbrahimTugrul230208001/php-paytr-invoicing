<?php

namespace App\Invoice;

use RuntimeException;

class PurchaseBillFactory
{
    public function fromPaytrPayload(array $payload, array $defaults = []): array
    {
        $supplierId = $defaults['supplier_id'] ?? null;
        if (empty($supplierId)) {
            throw new RuntimeException('Paraşüt supplier ID must be configured.');
        }

        $amount = $this->extractAmount($payload);
        if ($amount <= 0) {
            throw new RuntimeException('Unable to determine purchase amount from PayTR payload.');
        }

        $itemDescription = $payload['product_name'] ?? $defaults['item_description'] ?? 'PayTR Purchase';
        $descriptionPrefix = $defaults['description_prefix'] ?? 'PayTR purchase bill';

        $detail = [
            'type' => 'purchase_bill_details',
            'attributes' => [
                'quantity' => 1,
                'unit_price' => $amount,
                'vat_rate' => $defaults['vat_rate'] ?? 18,
                'description' => $itemDescription,
            ],
        ];

        $productId = $defaults['product_id'] ?? null;
        if (!empty($productId)) {
            $detail['relationships']['product'] = [
                'data' => [
                    'type' => 'products',
                    'id' => $productId,
                ],
            ];
        }

        $warehouseId = $defaults['warehouse_id'] ?? null;
        if (!empty($warehouseId)) {
            $detail['relationships']['warehouse'] = [
                'data' => [
                    'type' => 'warehouses',
                    'id' => $warehouseId,
                ],
            ];
        }

        $relationships = [
            'supplier' => [
                'data' => [
                    'type' => 'contacts',
                    'id' => $supplierId,
                ],
            ],
            'details' => [
                'data' => [
                    $detail,
                ],
            ],
        ];

        $categoryId = $defaults['category_id'] ?? null;
        if (!empty($categoryId)) {
            $relationships['category'] = [
                'data' => [
                    'type' => 'categories',
                    'id' => $categoryId,
                ],
            ];
        }

        return [
            'data' => [
                'type' => 'purchase_bills',
                'attributes' => [
                    'description' => sprintf('%s - %s', $descriptionPrefix, $payload['merchant_oid'] ?? 'unknown'),
                    'issue_date' => date('Y-m-d'),
                    'due_date' => date('Y-m-d'),
                    'currency' => $defaults['currency'] ?? 'TRY',
                    'exchange_rate' => 1,
                ],
                'relationships' => $relationships,
            ],
        ];
    }

    private function extractAmount(array $payload): float
    {
        if (isset($payload['payment_amount'])) {
            return round(((float) $payload['payment_amount']) / 100, 2);
        }

        if (isset($payload['total_amount'])) {
            return round((float) $payload['total_amount'], 2);
        }

        if (isset($payload['amount'])) {
            return round((float) $payload['amount'], 2);
        }

        return 0.0;
    }
}
