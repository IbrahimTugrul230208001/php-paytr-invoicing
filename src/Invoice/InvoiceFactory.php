<?php

namespace App\Invoice;

class InvoiceFactory
{
    public function fromPaytrPayload(array $payload, array $defaults = []): array
    {
        $customerName = $payload['user_name'] ?? $defaults['customer_name'] ?? 'Unknown Customer';
        $amount = isset($payload['merchant_oid']) ? (float)($payload['payment_amount'] ?? 0) / 100 : (float)($payload['payment_amount'] ?? 0);
        $itemDescription = $payload['product_name'] ?? $defaults['item_description'] ?? 'PayTR Sale';

        return [
            'data' => [
                'type' => 'sales_invoices',
                'attributes' => [
                    'item_type' => 'invoice',
                    'description' => ($defaults['invoice_description'] ?? 'PayTR transaction invoice') . ' - ' . $customerName,
                    'issue_date' => date('Y-m-d'),
                    'currency' => $defaults['currency'] ?? 'TRY',
                    'exchange_rate' => 1,
                ],
                'relationships' => [
                    'customer' => [
                        'data' => [
                            'type' => 'contacts',
                            'id' => $defaults['customer_id'] ?? null,
                        ],
                    ],
                    'details' => [
                        'data' => [[
                            'type' => 'sales_invoice_details',
                            'attributes' => [
                                'quantity' => 1,
                                'unit_price' => $amount,
                                'vat_rate' => $defaults['vat_rate'] ?? 18,
                                'description' => $itemDescription,
                            ],
                        ]],
                    ],
                ],
            ],
        ];
    }
}