<?php

namespace App\Paytr;

use App\Invoice\PurchaseBillFactory;
use App\Parasut\ParasutClient;
use App\Support\Logger;
use RuntimeException;

class CallbackHandler
{
    private SignatureValidator $validator;
    private ParasutClient $parasutClient;
    private PurchaseBillFactory $purchaseBillFactory;
    private Logger $logger;
    private array $invoiceDefaults;
    private bool $useDummyPayload;
    private array $dummyPayloadDefaults;

    public function __construct(
        SignatureValidator $validator,
        ParasutClient $parasutClient,
        PurchaseBillFactory $purchaseBillFactory,
        Logger $logger,
        array $invoiceDefaults,
        array $paytrConfig = []
    ) {
        $this->validator = $validator;
        $this->parasutClient = $parasutClient;
        $this->purchaseBillFactory = $purchaseBillFactory;
        $this->logger = $logger;
        $this->invoiceDefaults = $invoiceDefaults;
        $this->useDummyPayload = (bool) ($paytrConfig['use_dummy_payload'] ?? false);
        $this->dummyPayloadDefaults = $paytrConfig['dummy_payload'] ?? [];
    }

    public function handle(array $payload): array
    {
        $this->logger->info('Received PayTR callback', ['payload' => $payload]);

        if ($this->useDummyPayload) {
            $payload = $this->buildDummyPayload($payload);
            $this->logger->info('Using dummy PayTR payload', ['payload' => $payload]);
        } else {
            if (!$this->validator->validate($payload)) {
                $this->logger->error('Invalid PayTR signature', ['payload' => $payload]);
                throw new RuntimeException('Invalid signature');
            }

            if (($payload['status'] ?? '') !== 'success') {
                $this->logger->info('Ignoring non-successful PayTR callback', ['status' => $payload['status'] ?? null]);
                return ['status' => 'ignored'];
            }
        }

        $purchaseBillData = $this->purchaseBillFactory->fromPaytrPayload($payload, $this->invoiceDefaults);
        $purchaseBill = $this->parasutClient->createPurchaseBill($purchaseBillData);

        return ['status' => 'created', 'purchase_bill' => $purchaseBill];
    }

    private function buildDummyPayload(array $payload): array
    {
        $defaults = array_merge([
            'status' => 'success',
            'merchant_oid' => 'dummy-' . uniqid('', true),
            'payment_amount' => 10000,
        ], $this->dummyPayloadDefaults);

        return array_merge($defaults, $payload);
    }
}
