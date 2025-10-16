<?php

namespace App\Paytr;

use App\Invoice\InvoiceFactory;
use App\Parasut\ParasutClient;
use App\Support\Logger;
use RuntimeException;

class CallbackHandler
{
    private SignatureValidator $validator;
    private ParasutClient $parasutClient;
    private InvoiceFactory $invoiceFactory;
    private Logger $logger;
    private array $invoiceDefaults;

    public function __construct(
        SignatureValidator $validator,
        ParasutClient $parasutClient,
        InvoiceFactory $invoiceFactory,
        Logger $logger,
        array $invoiceDefaults
    ) {
        $this->validator = $validator;
        $this->parasutClient = $parasutClient;
        $this->invoiceFactory = $invoiceFactory;
        $this->logger = $logger;
        $this->invoiceDefaults = $invoiceDefaults;
    }

    public function handle(array $payload): array
    {
        $this->logger->info('Received PayTR callback', ['payload' => $payload]);

        if (!$this->validator->validate($payload)) {
            $this->logger->error('Invalid PayTR signature', ['payload' => $payload]);
            throw new RuntimeException('Invalid signature');
        }

        if (($payload['status'] ?? '') !== 'success') {
            $this->logger->info('Ignoring non-successful PayTR callback', ['status' => $payload['status'] ?? null]);
            return ['status' => 'ignored'];
        }

        if (empty($this->invoiceDefaults['customer_id'])) {
            throw new RuntimeException('Paraşüt default customer ID is not configured.');
        }

        $invoiceData = $this->invoiceFactory->fromPaytrPayload($payload, $this->invoiceDefaults);
        $invoice = $this->parasutClient->createInvoice($invoiceData);

        return ['status' => 'created', 'invoice' => $invoice];
    }
}