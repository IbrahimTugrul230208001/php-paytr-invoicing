<?php
?><!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Paraşüt Satın Alma Fişi – Demo Sipariş</title>
  <style>
    :root {
      --bg: #0f172a;        /* slate-900 */
      --card: #111827;      /* gray-900 */
      --muted: #94a3b8;     /* slate-400 */
      --text: #e5e7eb;      /* gray-200 */
      --primary: #22c55e;   /* green-500 */
      --primary-hover: #16a34a;
      --danger: #ef4444;
      --border: #1f2937;    /* gray-800 */
      --input: #0b1220;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0; background: radial-gradient(1200px 600px at 70% -10%, #1f2937, transparent) var(--bg);
      color: var(--text); font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px;
    }
    .card {
      width: 100%; max-width: 860px; background: linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,0)) var(--card);
      border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.4);
      overflow: hidden;
    }
    .card header {
      padding: 20px 24px; border-bottom: 1px solid var(--border); display:flex; align-items:center; justify-content:space-between;
    }
    .card header h1 { margin: 0; font-size: 18px; letter-spacing:.2px; }
    .muted { color: var(--muted); font-size: 12px; }
    form { padding: 24px; display:grid; grid-template-columns: repeat(12, 1fr); gap: 16px; }
    .field { grid-column: span 6; display:flex; flex-direction:column; gap:8px; }
    .field-3 { grid-column: span 3; }
    .field-4 { grid-column: span 4; }
    .field-8 { grid-column: span 8; }
    .field-12 { grid-column: span 12; }
    label { font-size: 12px; color: var(--muted); }
    input, select, textarea {
      width: 100%; padding: 12px 14px; color: var(--text); background: var(--input);
      border: 1px solid var(--border); border-radius: 10px; outline: none; transition: border .15s ease, box-shadow .15s ease;
    }
    input:focus, select:focus, textarea:focus { border-color: #334155; box-shadow: 0 0 0 3px rgba(51,65,85,.35); }
    .row { grid-column: span 12; display:flex; gap: 12px; align-items:center; justify-content:flex-end; }
    button {
      appearance: none; border: 0; padding: 12px 18px; border-radius: 10px; font-weight: 600; cursor: pointer;
      transition: transform .05s ease, background .2s ease, box-shadow .2s ease;
    }
    .btn-primary { background: var(--primary); color: #06120a; box-shadow: 0 6px 20px rgba(34,197,94,.35); }
    .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
    .help { grid-column: span 12; background: rgba(148,163,184,.06); border:1px dashed var(--border); padding: 12px 14px; border-radius: 10px; font-size: 12px; color: var(--muted);}
    @media (max-width: 768px) {
      .field, .field-3, .field-4, .field-8, .field-12 { grid-column: span 12; }
    }
  </style>
</head>
<body>
  <div class="card">
    <header>
      <h1>Paraşüt – Satın Alma Fişi Oluştur</h1>
      <div class="muted">Gerekli alanları doldurun ve “Sipariş Ver”e basın.</div>
    </header>

    <form method="post" action="/order_submit.php" autocomplete="on">
      <!-- Required by Paraşüt: supplier and at least one detail with quantity/unit_price/vat_rate -->
      <div class="field field-8">
        <label for="supplier_id">Tedarikçi (Supplier) ID – Paraşüt Contacts</label>
        <input type="text" id="supplier_id" name="supplier_id" required placeholder="contacts.id">
      </div>

      <div class="field field-4">
        <label for="merchant_oid">Sipariş/Ödeme Referansı (merchant_oid)</label>
        <input type="text" id="merchant_oid" name="merchant_oid" value="ORD-<?php echo date('YmdHis'); ?>" required>
      </div>

      <div class="field field-8">
        <label for="item_description">Kalem Açıklaması</label>
        <input type="text" id="item_description" name="item_description" required placeholder="Örn: Web hizmeti">
      </div>

      <div class="field field-4">
        <label for="vat_rate">KDV Oranı (%)</label>
        <input type="number" id="vat_rate" name="vat_rate" min="0" max="20" step="1" value="18" required>
      </div>

      <div class="field field-3">
        <label for="quantity">Miktar</label>
        <input type="number" id="quantity" name="quantity" min="1" step="1" value="1" required>
      </div>

      <div class="field field-4">
        <label for="unit_price_tl">Birim Fiyat (TL)</label>
        <input type="number" id="unit_price_tl" name="unit_price_tl" min="0" step="0.01" placeholder="0.00" required>
      </div>

      <div class="field field-3">
        <label for="currency">Para Birimi</label>
        <select id="currency" name="currency">
          <option value="TRY" selected>TRY</option>
          <option value="USD">USD</option>
          <option value="EUR">EUR</option>
        </select>
      </div>

      <div class="field field-4">
        <label for="issue_date">Belge Tarihi (issue_date)</label>
        <input type="date" id="issue_date" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required>
      </div>

      <div class="field field-4">
        <label for="due_date">Vade (due_date)</label>
        <input type="date" id="due_date" name="due_date" value="<?php echo date('Y-m-d'); ?>" required>
      </div>

      <!-- Optional relationships -->
      <div class="field field-4">
        <label for="product_id">Ürün ID (opsiyonel)</label>
        <input type="text" id="product_id" name="product_id" placeholder="products.id">
      </div>
      <div class="field field-4">
        <label for="warehouse_id">Depo ID (opsiyonel)</label>
        <input type="text" id="warehouse_id" name="warehouse_id" placeholder="warehouses.id">
      </div>
      <div class="field field-4">
        <label for="category_id">Kategori ID (opsiyonel)</label>
        <input type="text" id="category_id" name="category_id" placeholder="categories.id">
      </div>

      <div class="field field-12">
        <label for="description">Belge Açıklaması (opsiyonel)</label>
        <input type="text" id="description" name="description" placeholder="Örn: Web form siparişi">
      </div>

      <div class="help">
        Zorunlu alanlar: Supplier ID, Kalem Açıklaması, Miktar, Birim Fiyat, KDV, Issue/Due Date. Para birimi varsayılan TRY.
      </div>

      <div class="row">
        <button class="btn-primary" type="submit">Sipariş Ver</button>
      </div>
    </form>
  </div>
</body>
</html>
