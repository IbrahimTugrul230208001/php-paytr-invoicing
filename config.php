<?php

return [
    'paytr' => [
        'merchant_key' => getenv('PAYTR_MERCHANT_KEY') ?: '',
        'merchant_salt' => getenv('PAYTR_MERCHANT_SALT') ?: '',
        'use_dummy_payload' => getenv('PAYTR_USE_DUMMY_PAYLOAD') ? filter_var(getenv('PAYTR_USE_DUMMY_PAYLOAD'), FILTER_VALIDATE_BOOLEAN) : true,
        'dummy_payload' => [
            'user_name' => 'Dummy Supplier',
            'product_name' => 'Sample Purchase',
            'payment_amount' => 10000,
        ],
    ],
    'parasut' => [
        'base_url' => getenv('PARASUT_BASE_URL') ?: 'https://api.parasut.com',
        'company_id' => getenv('PARASUT_COMPANY_ID') ?: 'YOUR_COMPANY_ID',
        'client_id' => getenv('PARASUT_CLIENT_ID') ?: 'YOUR_CLIENT_ID',
        'client_secret' => getenv('PARASUT_CLIENT_SECRET') ?: 'YOUR_CLIENT_SECRET',
        'username' => getenv('PARASUT_USERNAME') ?: 'YOUR_EMAIL',
        'password' => getenv('PARASUT_PASSWORD') ?: 'YOUR_PASSWORD',
    ],
    'invoice' => [
        'log_path' => __DIR__ . '/logs',
        'currency' => getenv('PARASUT_CURRENCY') ?: 'TRY',
        'vat_rate' => getenv('PARASUT_VAT_RATE') ? (float) getenv('PARASUT_VAT_RATE') : 18.0,
        'supplier_id' => getenv('PARASUT_SUPPLIER_ID') ?: 'SUPPLIER_CONTACT_ID',
        'category_id' => getenv('PARASUT_CATEGORY_ID') ?: null,
        'product_id' => getenv('PARASUT_PRODUCT_ID') ?: null,
        'warehouse_id' => getenv('PARASUT_WAREHOUSE_ID') ?: null,
        'description_prefix' => getenv('PARASUT_DESCRIPTION_PREFIX') ?: 'PayTR purchase bill',
    ],
];
