<?php
// checkout.php — DRIP ZONE Order Processing & Bill
require_once 'db.php';

/* ════════════════════════════════════════════════════
   VIEW BILL (GET)
   ════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['view'], $_GET['bill_id'])) {
    $bill_id = (int) $_GET['bill_id'];

    $bStmt = $pdo->prepare(
        "SELECT *, DATE_FORMAT(bill_date,'%d %b %Y, %h:%i %p') AS fmt_date
         FROM Bills WHERE bill_id = ?"
    );
    $bStmt->execute([$bill_id]);
    $bill = $bStmt->fetch();

    if (!$bill) { http_response_code(404); die('Bill not found.'); }

    $iStmt = $pdo->prepare(
        "SELECT item_name, size_chosen, unit_price, quantity, line_total FROM Bill_Items WHERE bill_id = ?"
    );
    $iStmt->execute([$bill_id]);
    $billItems = $iStmt->fetchAll();

    $subtotal = array_sum(array_column($billItems, 'line_total'));
    $gst      = $subtotal * 0.05;
    $total    = (float) $bill['total_amount'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Order #<?= $bill_id ?> — DRIP ZONE 🔥</title>
  <meta name="robots" content="noindex"/>
  <link rel="stylesheet" href="style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
</head>
<body>

<!-- Success marquee -->
<div class="marquee-strip" aria-hidden="true">
  <div class="marquee-track">
    <span>🔥 ORDER CONFIRMED</span><span>★</span>
    <span>🎉 YOUR DRIP IS LOCKED IN</span><span>★</span>
    <span>📦 GET READY TO SLAY</span><span>★</span>
    <span>🔥 ORDER CONFIRMED</span><span>★</span>
    <span>🎉 YOUR DRIP IS LOCKED IN</span><span>★</span>
    <span>📦 GET READY TO SLAY</span><span>★</span>
  </div>
</div>

<div class="bill-page" id="bill-page">
  <div class="bill-wrap" id="bill-wrap">

    <!-- Header -->
    <div class="bill-top">
      <div class="bill-logo">🔥 DRIP ZONE</div>
      <div class="bill-tagline">Men's Streetwear &amp; Fashion</div>
      <div class="bill-divider"></div>
      <div class="bill-meta">
        <strong>ORDER RECEIPT</strong><br/>
        Order #<strong><?= str_pad($bill_id, 5, '0', STR_PAD_LEFT) ?></strong>
        &nbsp;|&nbsp;
        <?= htmlspecialchars($bill['fmt_date']) ?>
      </div>
    </div>

    <!-- Body -->
    <div class="bill-content">

      <!-- Customer -->
      <div class="bill-customer">
        <div class="bill-section-title">CUSTOMER INFO</div>
        <div class="bill-customer-row">
          <strong>Name</strong><span><?= htmlspecialchars($bill['customer_name']) ?></span>
        </div>
        <?php if ($bill['customer_phone']): ?>
        <div class="bill-customer-row">
          <strong>Phone</strong><span><?= htmlspecialchars($bill['customer_phone']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($bill['customer_email']): ?>
        <div class="bill-customer-row">
          <strong>Email</strong><span><?= htmlspecialchars($bill['customer_email']) ?></span>
        </div>
        <?php endif; ?>
        <div class="bill-customer-row">
          <strong>Payment</strong>
          <span style="color:var(--neon-yellow);font-weight:700;"><?= htmlspecialchars($bill['payment_method']) ?></span>
        </div>
        <?php if ($bill['notes']): ?>
        <div class="bill-customer-row">
          <strong>Notes</strong><span><?= htmlspecialchars($bill['notes']) ?></span>
        </div>
        <?php endif; ?>
      </div>

      <!-- Items -->
      <div class="bill-section-title">ITEMS ORDERED</div>
      <table class="bill-items-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Item</th>
            <th style="text-align:right;">Price</th>
            <th style="text-align:center;">Qty</th>
            <th style="text-align:right;">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($billItems as $idx => $bi): ?>
          <tr>
            <td style="color:#333;font-size:.78rem;"><?= $idx + 1 ?></td>
            <td>
              <?= htmlspecialchars($bi['item_name']) ?>
              <?php if ($bi['size_chosen']): ?>
                <span style="font-size:.72rem;color:var(--neon-yellow);font-weight:700;margin-left:.3rem;">
                  [<?= htmlspecialchars($bi['size_chosen']) ?>]
                </span>
              <?php endif; ?>
            </td>
            <td style="text-align:right;">₹<?= number_format($bi['unit_price'],2) ?></td>
            <td style="text-align:center;"><?= (int)$bi['quantity'] ?></td>
            <td style="text-align:right;font-weight:700;color:var(--off-white);">₹<?= number_format($bi['line_total'],2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Totals -->
      <div class="bill-totals">
        <div class="bill-total-row">
          <span>Subtotal</span>
          <span class="val">₹<?= number_format($subtotal,2) ?></span>
        </div>
        <div class="bill-total-row">
          <span>GST @ 5%</span>
          <span class="val">₹<?= number_format($gst,2) ?></span>
        </div>
        <div class="bill-total-row grand">
          <span>GRAND TOTAL</span>
          <span class="val">₹<?= number_format($total,2) ?></span>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="bill-footer">
      <div class="bill-thank">STAY DRIPPED. 🔥</div>
      <div class="bill-footer-sub">
        No returns on sale items. Exchanges within 7 days with this receipt.<br/>
        DRIP ZONE — Bold Cuts. Zero Rules.
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="bill-actions" id="bill-actions">
    <a href="index.php" class="btn btn-outline-w btn-lg">← KEEP SHOPPING</a>
    <button class="btn btn-pink btn-lg" onclick="window.print()">
      🖨️ PRINT RECEIPT
    </button>
    <a href="admin.php?page=bills" class="btn btn-outline-w btn-lg">VIEW ALL ORDERS</a>
  </div>
</div>
</body>
</html>
<?php
    exit;
}

/* ════════════════════════════════════════════════════
   PLACE ORDER (POST JSON)
   ════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Invalid payload.']); exit; }

    $customerName  = trim($data['customer_name']  ?? '');
    $customerPhone = trim($data['customer_phone'] ?? '');
    $customerEmail = trim($data['customer_email'] ?? '');
    $paymentMethod = trim($data['payment_method'] ?? 'Cash');
    $notes         = trim($data['notes']          ?? '');
    $items         = $data['items']               ?? [];

    if (!$customerName) { http_response_code(422); echo json_encode(['success'=>false,'error'=>'Name is required.']); exit; }
    if (!$items)        { http_response_code(422); echo json_encode(['success'=>false,'error'=>'Cart is empty.']); exit; }

    // Server-side re-price & stock check
    $verified  = [];
    $subtotal  = 0.0;

    foreach ($items as $raw) {
        $stockId  = (int) ($raw['stock_id'] ?? 0);
        $quantity = (int) ($raw['quantity'] ?? 1);
        if ($stockId <= 0 || $quantity <= 0) continue;

        $s = $pdo->prepare("SELECT item_name,price,stock_qty FROM Stock WHERE id=? AND display_status=1");
        $s->execute([$stockId]);
        $row = $s->fetch();
        if (!$row) continue;

        if ($quantity > $row['stock_qty']) {
            echo json_encode(['success'=>false,'error'=>"Only {$row['stock_qty']} of \"{$row['item_name']}\" available."]);
            exit;
        }

        $line     = (float)$row['price'] * $quantity;
        $subtotal += $line;
        $verified[] = [
            'stock_id'   => $stockId,
            'item_name'  => $row['item_name'],
            'unit_price' => (float)$row['price'],
            'quantity'   => $quantity,
            'line_total' => $line,
        ];
    }

    if (!$verified) { http_response_code(422); echo json_encode(['success'=>false,'error'=>'No valid items.']); exit; }

    $gst         = $subtotal * 0.05;
    $totalAmount = $subtotal + $gst;

    try {
        $pdo->beginTransaction();

        $billStmt = $pdo->prepare(
            "INSERT INTO Bills (customer_name,customer_phone,customer_email,total_amount,payment_method,notes)
             VALUES (?,?,?,?,?,?)"
        );
        $billStmt->execute([$customerName, $customerPhone ?: null, $customerEmail ?: null, round($totalAmount,2), $paymentMethod, $notes ?: null]);
        $billId = (int) $pdo->lastInsertId();

        $iStmt  = $pdo->prepare("INSERT INTO Bill_Items (bill_id,stock_id,item_name,unit_price,quantity,line_total) VALUES (?,?,?,?,?,?)");
        $dStmt  = $pdo->prepare("UPDATE Stock SET stock_qty=stock_qty-? WHERE id=? AND stock_qty>=?");

        foreach ($verified as $vi) {
            $iStmt->execute([$billId, $vi['stock_id'], $vi['item_name'], $vi['unit_price'], $vi['quantity'], $vi['line_total']]);
            $dStmt->execute([$vi['quantity'], $vi['stock_id'], $vi['quantity']]);
            if ($dStmt->rowCount() === 0) {
                $pdo->rollBack();
                echo json_encode(['success'=>false,'error'=>"Stock depleted for \"{$vi['item_name']}\". Refresh and try again."]);
                exit;
            }
        }

        $pdo->commit();
        echo json_encode(['success'=>true,'bill_id'=>$billId]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Checkout error: '.$e->getMessage());
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Server error. Please try again.']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'Method not allowed.']);
