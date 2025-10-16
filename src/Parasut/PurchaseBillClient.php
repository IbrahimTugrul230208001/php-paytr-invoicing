<?php

namespace App\Parasut;

interface PurchaseBillClient
{
    // Returns an array; HTML renderer will return ['html' => '<!DOCTYPE html>...']
    public function createPurchaseBill(array $purchaseBillData): array;
}