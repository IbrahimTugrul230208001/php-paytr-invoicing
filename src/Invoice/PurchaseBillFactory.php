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

        // Allow unit_price override from form; fallback to PayTR-like amount mapping
        $amount = isset($defaults['unit_price'])
            ? (float) $defaults['unit_price']
            : $this->extractAmount($payload);

        if ($amount <= 0) {
            throw new RuntimeException('Unable to determine unit price for purchase bill.');
        }

        $quantity = isset($defaults['quantity']) ? (float) $defaults['quantity'] : 1.0;

        $itemDescription   = $payload['product_name'] ?? $defaults['item_description'] ?? 'Kalem';
        $descriptionPrefix = $defaults['description_prefix'] ?? 'Sipariş';
        $rootDescription   = $defaults['description'] ?? sprintf('%s - %s', $descriptionPrefix, $payload['merchant_oid'] ?? 'unknown');

        $detail = [
            'type' => 'purchase_bill_details',
            'attributes' => [
                'quantity'     => $quantity,
                'unit_price'   => $amount,
                'vat_rate'     => isset($defaults['vat_rate']) ? (int) $defaults['vat_rate'] : 18,
                'description'  => $itemDescription,
            ],
        ];

        $productId = $defaults['product_id'] ?? null;
        if (!empty($productId)) {
            $detail['relationships']['product'] = [
                'data' => [
                    'type' => 'products',
                    'id'   => $productId,
                ],
            ];
        }

        $warehouseId = $defaults['warehouse_id'] ?? null;
        if (!empty($warehouseId)) {
            $detail['relationships']['warehouse'] = [
                'data' => [
                    'type' => 'warehouses',
                    'id'   => $warehouseId,
                ],
            ];
        }

        $relationships = [
            'supplier' => [
                'data' => [
                    'type' => 'contacts',
                    'id'   => $supplierId,
                ],
            ],
            'details' => [
                'data' => [$detail],
            ],
        ];

        $categoryId = $defaults['category_id'] ?? null;
        if (!empty($categoryId)) {
            $relationships['category'] = [
                'data' => [
                    'type' => 'categories',
                    'id'   => $categoryId,
                ],
            ];
        }

        // Allow overriding dates/currency/exchange_rate from form
        $issueDate    = $defaults['issue_date'] ?? date('Y-m-d');
        $dueDate      = $defaults['due_date'] ?? $issueDate;
        $currency     = $defaults['currency'] ?? 'TRY';
        $exchangeRate = isset($defaults['exchange_rate']) ? (float) $defaults['exchange_rate'] : 1.0;

        return [
            'data' => [
                'type' => 'purchase_bills',
                'attributes' => [
                    'description'   => $rootDescription,
                    'issue_date'    => $issueDate,
                    'due_date'      => $dueDate,
                    'currency'      => $currency,
                    'exchange_rate' => $exchangeRate,
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
