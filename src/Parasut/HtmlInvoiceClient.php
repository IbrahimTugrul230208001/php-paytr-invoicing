<?php

namespace App\Parasut;

use App\Support\Logger;

class HtmlInvoiceClient implements PurchaseBillClient
{
    private Logger $logger;
    private array $options;

    public function __construct(Logger $logger, array $options = [])
    {
        $this->logger = $logger;
        $this->options = $options;
    }

    public function createPurchaseBill(array $purchaseBillData): array
    {
        $data  = $purchaseBillData['data'] ?? [];
        $attrs = $data['attributes'] ?? [];
        $rels  = $data['relationships'] ?? [];

        $details = $rels['details']['data'] ?? [];
        $detail  = $details[0]['attributes'] ?? [];

        $qty      = (float)($detail['quantity'] ?? 1);
        $unit     = (float)($detail['unit_price'] ?? 0);
        $vatRate  = (float)($detail['vat_rate'] ?? 0);
        $itemDesc = (string)($detail['description'] ?? 'Kalem');

        $net   = round($qty * $unit, 2);
        $vat   = round($net * $vatRate / 100, 2);
        $gross = round($net + $vat, 2);

        $currency = $attrs['currency'] ?? 'TRY';
        $issue    = $attrs['issue_date'] ?? date('Y-m-d');
        $due      = $attrs['due_date'] ?? $issue;

        $supplierId   = $rels['supplier']['data']['id'] ?? '';
        $supplierName = $this->options['supplier_name'] ?? ('Tedarikçi #' . $supplierId);
        $description  = $attrs['description'] ?? '';
        $brand        = $this->options['brand'] ?? 'Ön Fatura';
        $logo         = $this->options['logo'] ?? null;

        $fmt = fn($n) => number_format((float)$n, 2, ',', '.');

        $css = '
        :root{--bg:#0f172a;--panel:#0b1220;--muted:#94a3b8;--text:#e5e7eb;--line:#1f2937;--accent:#22c55e;}
        *{box-sizing:border-box} body{margin:0;background:var(--bg);color:var(--text);font:14px/1.5 Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif;padding:24px}
        .invoice{max-width:980px;margin:0 auto;background:linear-gradient(180deg,rgba(255,255,255,.03),transparent) var(--panel);border:1px solid var(--line);border-radius:16px;overflow:hidden;box-shadow:0 10px 32px rgba(0,0,0,.4)}
        .header{display:flex;gap:16px;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid var(--line)}
        .brand{display:flex;flex-direction:column}.brand h1{margin:0;font-size:18px}.muted{color:var(--muted);font-size:12px}.logo{height:40px}
        .meta{display:grid;gap:6px}.meta .label{display:block;color:var(--muted);font-size:11px}
        .party{padding:16px 20px;border-bottom:1px solid var(--line)} .party-name{font-weight:600}
        table.items{width:100%;border-collapse:collapse} table.items thead th{background:#101826;color:#cbd5e1;text-align:left;padding:12px;border-bottom:1px solid var(--line)}
        table.items td{padding:12px;border-bottom:1px solid var(--line)} .right{text-align:right}
        .totals{padding:16px 20px;display:flex;flex-direction:column;gap:8px} .totals .row{display:flex;justify-content:space-between} .grand{font-weight:700}
        .footer{display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-top:1px solid var(--line)}
        .print{background:var(--accent);border:0;color:#06120a;padding:10px 14px;border-radius:8px;font-weight:600;cursor:pointer}
        @media print{body{background:#fff}.invoice{box-shadow:none;border:none}}
        ';

        $html = '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Ön Fatura</title><style>'.$css.'</style></head><body>'
              . '<div class="invoice">'
              . '<header class="header">'
              . ($logo ? '<img class="logo" src="'.htmlspecialchars($logo).'" alt="Logo"/>' : '')
              . '<div class="brand"><h1>'.htmlspecialchars($brand).'</h1><div class="muted">'.htmlspecialchars($description).'</div></div>'
              . '<div class="meta"><div><span class="label">Belge Tarihi</span><span>'.$issue.'</span></div><div><span class="label">Vade</span><span>'.$due.'</span></div><div><span class="label">Para Birimi</span><span>'.htmlspecialchars($currency).'</span></div></div>'
              . '</header>'
              . '<section class="party"><div><span class="label">Tedarikçi</span><div class="party-name">'.htmlspecialchars($supplierName).'</div><div class="muted">ID: '.htmlspecialchars((string)$supplierId).'</div></div></section>'
              . '<table class="items"><thead><tr><th>Açıklama</th><th class="right">Miktar</th><th class="right">Birim Fiyat</th><th class="right">KDV %</th><th class="right">Tutar</th></tr></thead>'
              . '<tbody><tr><td>'.htmlspecialchars($itemDesc).'</td><td class="right">'.$fmt($qty).'</td><td class="right">'.$fmt($unit).'</td><td class="right">'.$fmt($vatRate).'</td><td class="right">'.$fmt($net).'</td></tr></tbody></table>'
              . '<section class="totals"><div class="row"><span>Ara Toplam</span><span>'.$fmt($net).' '.$currency.'</span></div><div class="row"><span>KDV</span><span>'.$fmt($vat).' '.$currency.'</span></div><div class="row grand"><span>Genel Toplam</span><span>'.$fmt($gross).' '.$currency.'</span></div></section>'
              . '<footer class="footer"><button onclick="window.print()" class="print">Yazdır</button><span class="muted">Bu sayfa resmi fatura değildir; demo amaçlıdır.</span></footer>'
              . '</div></body></html>';

        $this->logger->info('Rendered HTML invoice preview', ['net' => $net, 'vat' => $vat, 'gross' => $gross]);

        return ['html' => $html, 'totals' => ['net' => $net, 'vat' => $vat, 'gross' => $gross]];
    }
}