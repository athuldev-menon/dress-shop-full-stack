<?php
// admin.php — DRIP ZONE Admin Dashboard
session_start();
if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
require_once 'db.php';

define('UPLOAD_DIR',    'uploads/');
define('UPLOAD_MAX_MB', 5);
define('ALLOWED_TYPES', ['image/jpeg','image/png','image/webp','image/gif']);

if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

$page   = $_GET['page']   ?? 'dashboard';
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$flash  = ['type'=>'','msg'=>''];

/* ══ ADD ══ */
if ($action==='add_item' && $_SERVER['REQUEST_METHOD']==='POST') {
    $name      = trim($_POST['item_name']     ?? '');
    $desc      = trim($_POST['description']   ?? '');
    $category  = trim($_POST['category']      ?? 'Tees');
    $price     = (float)($_POST['price']      ?? 0);
    $qty       = (int)($_POST['stock_qty']    ?? 0);
    $sizes     = trim($_POST['sizes']         ?? 'S,M,L,XL,XXL');
    $display   = isset($_POST['display_status']) ? 1 : 0;
    $img_path  = null;

    if ($name && $price > 0) {
        if (!empty($_FILES['item_image']['name'])) {
            $up = handleImageUpload('item_image');
            if ($up['success'])  { $img_path = $up['path']; }
            else { $flash = ['type'=>'danger','msg'=>$up['error']]; goto render; }
        }
        $pdo->prepare("INSERT INTO Stock (item_name,description,category,image_path,price,stock_qty,sizes,display_status) VALUES (?,?,?,?,?,?,?,?)")
            ->execute([$name, $desc?:null, $category, $img_path, $price, $qty, $sizes, $display]);
        $flash = ['type'=>'success','msg'=>"\"$name\" added! 🔥"];
        $page = 'stock';
    } else {
        $flash = ['type'=>'danger','msg'=>'Name and price are required.'];
    }
}

/* ══ EDIT ══ */
if ($action==='edit_item' && $_SERVER['REQUEST_METHOD']==='POST') {
    $id       = (int)($_POST['item_id']       ?? 0);
    $name     = trim($_POST['item_name']      ?? '');
    $desc     = trim($_POST['description']    ?? '');
    $category = trim($_POST['category']       ?? 'Tees');
    $price    = (float)($_POST['price']       ?? 0);
    $qty      = (int)($_POST['stock_qty']     ?? 0);
    $sizes    = trim($_POST['sizes']          ?? 'S,M,L,XL,XXL');
    $display  = isset($_POST['display_status']) ? 1 : 0;

    if ($id && $name && $price > 0) {
        $ex = $pdo->prepare("SELECT image_path FROM Stock WHERE id=?");
        $ex->execute([$id]);
        $curImg   = $ex->fetchColumn();
        $img_path = $curImg;

        if (!empty($_FILES['item_image']['name'])) {
            $up = handleImageUpload('item_image');
            if ($up['success']) {
                if ($curImg && file_exists($curImg)) @unlink($curImg);
                $img_path = $up['path'];
            } else { $flash=['type'=>'danger','msg'=>$up['error']]; goto render; }
        }
        $pdo->prepare("UPDATE Stock SET item_name=?,description=?,category=?,image_path=?,price=?,stock_qty=?,sizes=?,display_status=? WHERE id=?")
            ->execute([$name,$desc?:null,$category,$img_path,$price,$qty,$sizes,$display,$id]);
        $flash = ['type'=>'success','msg'=>"\"$name\" updated! ✅"];
        $page = 'stock';
    } else {
        $flash = ['type'=>'danger','msg'=>'Invalid data.'];
    }
}

/* ══ DELETE ══ */
if ($action==='delete_item' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $s  = $pdo->prepare("SELECT image_path,item_name FROM Stock WHERE id=?");
    $s->execute([$id]);
    $row = $s->fetch();
    if ($row) {
        if ($row['image_path'] && file_exists($row['image_path'])) @unlink($row['image_path']);
        $pdo->prepare("DELETE FROM Stock WHERE id=?")->execute([$id]);
        $flash = ['type'=>'success','msg'=>"\"{$row['item_name']}\" deleted."];
    }
    $page = 'stock';
}

/* ══ TOGGLE DISPLAY (AJAX) ══ */
if ($action==='toggle_display' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = (int)$_GET['id'];
    $pdo->prepare("UPDATE Stock SET display_status=1-display_status WHERE id=?")->execute([$id]);
    $s = $pdo->prepare("SELECT display_status FROM Stock WHERE id=?");
    $s->execute([$id]);
    echo json_encode(['success'=>true,'status'=>(int)$s->fetchColumn()]);
    exit;
}

/* ══ Image Upload ══ */
function handleImageUpload(string $field): array {
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) return ['success'=>false,'error'=>'Upload error '.$file['error'].'.'];
    if ($file['size'] > UPLOAD_MAX_MB*1024*1024) return ['success'=>false,'error'=>'Max '.UPLOAD_MAX_MB.'MB allowed.'];
    $fi   = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($fi, $file['tmp_name']);
    finfo_close($fi);
    if (!in_array($mime, ALLOWED_TYPES)) return ['success'=>false,'error'=>'Only JPG/PNG/WebP/GIF allowed.'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $dest = UPLOAD_DIR . bin2hex(random_bytes(12)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return ['success'=>false,'error'=>'Failed to save file.'];
    return ['success'=>true,'path'=>$dest];
}

/* ══ Data ══ */
$totalItems   = (int) $pdo->query("SELECT COUNT(*) FROM Stock")->fetchColumn();
$totalVisible = (int) $pdo->query("SELECT COUNT(*) FROM Stock WHERE display_status=1")->fetchColumn();
$lowStock     = (int) $pdo->query("SELECT COUNT(*) FROM Stock WHERE stock_qty<=5 AND stock_qty>0")->fetchColumn();
$outStock     = (int) $pdo->query("SELECT COUNT(*) FROM Stock WHERE stock_qty=0")->fetchColumn();
$totalBills   = (int) $pdo->query("SELECT COUNT(*) FROM Bills")->fetchColumn();
$totalRev     = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM Bills")->fetchColumn();
$todayRev     = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM Bills WHERE DATE(bill_date)=CURDATE()")->fetchColumn();

$stockItems = [];
if (in_array($page,['stock','dashboard'])) {
    $stockItems = $pdo->query("SELECT id,item_name,category,image_path,price,stock_qty,sizes,display_status,DATE_FORMAT(updated_at,'%d %b %Y') AS updated FROM Stock ORDER BY created_at DESC")->fetchAll();
}

$editItem = null;
if ($page==='stock' && isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM Stock WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $editItem = $s->fetch();
}

$bills = [];
if ($page==='bills') {
    $bills = $pdo->query("SELECT b.bill_id,b.customer_name,b.customer_phone,b.payment_method,b.total_amount,DATE_FORMAT(b.bill_date,'%d %b %Y, %h:%i %p') AS bill_date,COUNT(bi.id) AS item_count FROM Bills b LEFT JOIN Bill_Items bi ON bi.bill_id=b.bill_id GROUP BY b.bill_id ORDER BY b.bill_date DESC")->fetchAll();
}

$cats = ['Tees','Shirts','Jeans','Cargo','Hoodies','Jackets','Joggers','Accessories','Other'];

render:
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Admin — DRIP ZONE 🔥</title>
  <meta name="robots" content="noindex,nofollow"/>
  <link rel="stylesheet" href="style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
</head>
<body>
<div class="admin-layout" id="admin-layout">

  <!-- ══ SIDEBAR ══ -->
  <aside class="admin-sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-text">🔥 DRIP ZONE</div>
      <div class="sidebar-logo-sub">Admin Panel</div>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-section-label">MAIN</div>
      <a href="admin.php?page=dashboard" class="sidebar-link <?= $page==='dashboard'?'active':'' ?>" id="nav-dashboard">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
      </a>
      <div class="sidebar-section-label">INVENTORY</div>
      <a href="admin.php?page=stock" class="sidebar-link <?= $page==='stock'?'active':'' ?>" id="nav-stock">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
        Stock Items
        <span class="badge badge-yellow" style="margin-left:auto;"><?= $totalItems ?></span>
      </a>
      <a href="admin.php?page=stock&action=add" class="sidebar-link" id="nav-add">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add New Item
      </a>
      <div class="sidebar-section-label">SALES</div>
      <a href="admin.php?page=bills" class="sidebar-link <?= $page==='bills'?'active':'' ?>" id="nav-bills">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 8.414V19a2 2 0 01-2 2z"/></svg>
        Orders &amp; Bills
        <span class="badge badge-dark" style="margin-left:auto;"><?= $totalBills ?></span>
      </a>
      <div class="sidebar-section-label">SITE</div>
      <a href="index.php" target="_blank" class="sidebar-link" id="nav-store">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        View Store ↗
      </a>
    </nav>
    <div class="sidebar-footer">
      <div class="admin-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></div>
      <a href="logout.php" style="font-size:.72rem;color:#333;">Sign out →</a>
    </div>
  </aside>

  <!-- ══ MAIN ══ -->
  <div class="admin-main">
    <div class="admin-topbar">
      <div>
        <div class="admin-page-title">
          <?php echo ['dashboard'=>'📊 DASHBOARD','stock'=>'👕 STOCK MGMT','bills'=>'🧾 ORDERS'][$page] ?? 'DASHBOARD'; ?>
        </div>
        <div class="admin-topbar-meta"><?= date('l, d F Y') ?></div>
      </div>
      <div style="display:flex;gap:.5rem;align-items:center;">
        <a href="admin.php?page=stock&action=add" class="btn btn-pink btn-sm" id="topbar-add">+ ADD ITEM</a>
        <a href="logout.php" class="btn btn-outline-w btn-sm" id="topbar-logout">SIGN OUT</a>
      </div>
    </div>

    <div class="admin-content">
      <?php if ($flash['msg']): ?>
        <div class="alert alert-<?= $flash['type'] ?>" id="flash-msg" role="alert">
          <?= htmlspecialchars($flash['msg']) ?>
        </div>
        <script>setTimeout(()=>document.getElementById('flash-msg')?.remove(),5000);</script>
      <?php endif; ?>

      <!-- ═══════════════ DASHBOARD ═══════════════ -->
      <?php if ($page==='dashboard'): ?>
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-label">TOTAL ITEMS</div>
            <div class="stat-value"><?= $totalItems ?></div>
            <div class="stat-sub"><?= $totalVisible ?> visible on store</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">TOTAL ORDERS</div>
            <div class="stat-value" style="color:var(--neon-pink);"><?= $totalBills ?></div>
            <div class="stat-sub">All time</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">TOTAL REVENUE</div>
            <div class="stat-value" style="color:var(--cyber-blue);font-size:1.6rem;">₹<?= number_format($totalRev,0) ?></div>
            <div class="stat-sub">Today: ₹<?= number_format($todayRev,0) ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">LOW / OUT OF STOCK</div>
            <div class="stat-value" style="color:var(--hot-orange);"><?= $lowStock + $outStock ?></div>
            <div class="stat-sub"><?= $outStock ?> sold out</div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="section-title">RECENT STOCK</div>
            <a href="admin.php?page=stock" class="btn btn-outline-w btn-sm">VIEW ALL</a>
          </div>
          <div class="table-wrap">
            <table class="admin-table">
              <thead><tr><th>IMG</th><th>ITEM</th><th>CAT</th><th>PRICE</th><th>QTY</th><th>STATUS</th></tr></thead>
              <tbody>
                <?php foreach(array_slice($stockItems,0,8) as $item): ?>
                <tr>
                  <td>
                    <div class="item-img">
                      <?php if($item['image_path'] && file_exists($item['image_path'])): ?>
                        <img src="<?= htmlspecialchars($item['image_path']) ?>" alt=""/>
                      <?php else: ?>👕<?php endif; ?>
                    </div>
                  </td>
                  <td><div class="item-name"><?= htmlspecialchars($item['item_name']) ?></div></td>
                  <td><span class="badge badge-dark"><?= htmlspecialchars($item['category']) ?></span></td>
                  <td style="color:var(--neon-yellow);font-weight:700;">₹<?= number_format($item['price'],2) ?></td>
                  <td>
                    <?php if($item['stock_qty']==0): ?><span class="badge badge-red">OUT</span>
                    <?php elseif($item['stock_qty']<=5): ?><span class="badge badge-pink"><?= $item['stock_qty'] ?> LEFT</span>
                    <?php else: ?><span class="badge badge-green"><?= $item['stock_qty'] ?></span>
                    <?php endif; ?>
                  </td>
                  <td><?= $item['display_status'] ? '<span class="badge badge-green">LIVE</span>' : '<span class="badge badge-dark">HIDDEN</span>' ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      <!-- ═══════════════ STOCK ═══════════════ -->
      <?php elseif($page==='stock'): ?>
        <div class="tab-nav">
          <button class="tab-btn <?= (!isset($_GET['edit'])&&$action!=='add')?'active':'' ?>" id="tab-list-btn" onclick="switchTab('list')">ALL ITEMS (<?= $totalItems ?>)</button>
          <button class="tab-btn <?= (isset($_GET['edit'])||$action==='add')?'active':'' ?>" id="tab-form-btn" onclick="switchTab('form')">
            <?= $editItem ? '✏️ EDIT ITEM' : '➕ ADD ITEM' ?>
          </button>
        </div>

        <!-- LIST TAB -->
        <div class="tab-panel <?= (!isset($_GET['edit'])&&$action!=='add')?'active':'' ?>" id="tab-panel-list">
          <div style="margin-bottom:1rem;display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;">
            <input type="search" id="admin-search" class="form-control" style="max-width:260px;" placeholder="Search items…" oninput="adminSearchFilter()"/>
            <a href="admin.php?page=stock&action=add" class="btn btn-pink btn-sm">+ ADD ITEM</a>
          </div>
          <div class="card">
            <div class="table-wrap">
              <table class="admin-table" id="stock-table">
                <thead><tr><th>IMG</th><th>NAME</th><th>CAT</th><th>SIZES</th><th>PRICE</th><th>STOCK</th><th>UPDATED</th><th>LIVE</th><th>ACTIONS</th></tr></thead>
                <tbody id="stock-tbody">
                  <?php if(empty($stockItems)): ?>
                  <tr><td colspan="9" style="text-align:center;color:#333;padding:2rem;">No items yet. <a href="admin.php?page=stock&action=add">Add one →</a></td></tr>
                  <?php else: ?>
                  <?php foreach($stockItems as $item): ?>
                  <tr data-name="<?= htmlspecialchars(strtolower($item['item_name'])) ?>" data-cat="<?= htmlspecialchars(strtolower($item['category'])) ?>">
                    <td>
                      <div class="item-img">
                        <?php if($item['image_path']&&file_exists($item['image_path'])): ?>
                          <img src="<?= htmlspecialchars($item['image_path']) ?>" alt=""/>
                        <?php else: ?>👕<?php endif; ?>
                      </div>
                    </td>
                    <td><div class="item-name"><?= htmlspecialchars($item['item_name']) ?></div></td>
                    <td><span class="badge badge-dark"><?= htmlspecialchars($item['category']) ?></span></td>
                    <td style="font-size:.72rem;color:#444;"><?= htmlspecialchars($item['sizes']) ?></td>
                    <td style="color:var(--neon-yellow);font-weight:700;">₹<?= number_format($item['price'],2) ?></td>
                    <td>
                      <?php if($item['stock_qty']==0): ?><span class="badge badge-red">OUT</span>
                      <?php elseif($item['stock_qty']<=5): ?><span class="badge badge-pink"><?= $item['stock_qty'] ?></span>
                      <?php else: ?><span class="badge badge-green"><?= $item['stock_qty'] ?></span>
                      <?php endif; ?>
                    </td>
                    <td style="font-size:.72rem;color:#333;"><?= $item['updated'] ?></td>
                    <td>
                      <label class="toggle-switch">
                        <input type="checkbox" id="toggle-<?= $item['id'] ?>" <?= $item['display_status']?'checked':'' ?> onchange="toggleDisplay(<?= $item['id'] ?>,this)"/>
                        <span class="toggle-slider"></span>
                      </label>
                    </td>
                    <td>
                      <div class="actions-cell">
                        <a href="admin.php?page=stock&edit=<?= $item['id'] ?>" class="btn btn-outline-w btn-sm">✏️</a>
                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $item['id'] ?>,'<?= htmlspecialchars(addslashes($item['item_name'])) ?>')">🗑️</button>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- FORM TAB -->
        <div class="tab-panel <?= (isset($_GET['edit'])||$action==='add')?'active':'' ?>" id="tab-panel-form">
          <div class="card">
            <div class="card-header">
              <div class="section-title"><?= $editItem ? '✏️ EDIT: '.htmlspecialchars($editItem['item_name']) : '➕ ADD NEW ITEM' ?></div>
            </div>
            <div class="card-body">
              <form method="POST" action="admin.php?page=stock" enctype="multipart/form-data" id="item-form" novalidate>
                <input type="hidden" name="action" value="<?= $editItem?'edit_item':'add_item' ?>"/>
                <?php if($editItem): ?><input type="hidden" name="item_id" value="<?= $editItem['id'] ?>"/><?php endif; ?>

                <div class="admin-form-grid">
                  <div class="form-group span-2">
                    <label class="form-label" for="item_name">Item Name *</label>
                    <input type="text" id="item_name" name="item_name" class="form-control" required
                           placeholder="e.g. Oversized Graphic Tee" value="<?= htmlspecialchars($editItem['item_name']??'') ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="category">Category *</label>
                    <select id="category" name="category" class="form-control">
                      <?php foreach($cats as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($editItem['category']??'')===$cat?'selected':'' ?>><?= $cat ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="price">Price (₹) *</label>
                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required
                           placeholder="999.00" value="<?= htmlspecialchars($editItem['price']??'') ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="stock_qty">Stock Quantity *</label>
                    <input type="number" id="stock_qty" name="stock_qty" class="form-control" min="0" required
                           value="<?= htmlspecialchars($editItem['stock_qty']??0) ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="sizes">Available Sizes</label>
                    <input type="text" id="sizes" name="sizes" class="form-control"
                           placeholder="S,M,L,XL,XXL" value="<?= htmlspecialchars($editItem['sizes']??'S,M,L,XL,XXL') ?>"/>
                  </div>
                  <div class="form-group span-2">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" placeholder="Material, fit, vibe…"><?= htmlspecialchars($editItem['description']??'') ?></textarea>
                  </div>
                  <div class="form-group" style="display:flex;align-items:center;gap:.8rem;padding-top:1.3rem;">
                    <label class="toggle-switch" for="display_status">
                      <input type="checkbox" id="display_status" name="display_status" <?= ($editItem['display_status']??1)?'checked':'' ?>/>
                      <span class="toggle-slider"></span>
                    </label>
                    <span class="form-label" style="margin:0;">Show on Store</span>
                  </div>
                  <div class="form-group span-2">
                    <label class="form-label">Product Image (JPG/PNG/WebP · max 5MB)</label>
                    <div class="image-upload-area" id="upload-area">
                      <input type="file" name="item_image" id="item_image" accept="image/*" onchange="previewImage(this)"/>
                      <div class="upload-icon">📷</div>
                      <div class="upload-text">Click or drag image here</div>
                      <div class="upload-hint">JPG, PNG, WebP, GIF</div>
                    </div>
                    <?php if(!empty($editItem['image_path'])&&file_exists($editItem['image_path'])): ?>
                      <img src="<?= htmlspecialchars($editItem['image_path']) ?>" id="img-preview" style="max-height:120px;margin-top:.7rem;display:block;" alt="Current"/>
                    <?php else: ?>
                      <img id="img-preview" src="" alt="Preview" style="display:none;"/>
                    <?php endif; ?>
                  </div>
                </div>

                <div style="display:flex;gap:.7rem;margin-top:.5rem;flex-wrap:wrap;">
                  <button type="submit" class="btn btn-pink btn-lg"><?= $editItem?'💾 SAVE CHANGES':'➕ ADD TO STOCK' ?></button>
                  <a href="admin.php?page=stock" class="btn btn-outline-w btn-lg">CANCEL</a>
                </div>
              </form>
            </div>
          </div>
        </div>

      <!-- ═══════════════ BILLS ═══════════════ -->
      <?php elseif($page==='bills'): ?>
        <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));margin-bottom:1.5rem;">
          <div class="stat-card"><div class="stat-label">TOTAL ORDERS</div><div class="stat-value"><?= $totalBills ?></div></div>
          <div class="stat-card"><div class="stat-label">TOTAL REVENUE</div><div class="stat-value" style="font-size:1.5rem;color:var(--cyber-blue);">₹<?= number_format($totalRev,0) ?></div></div>
          <div class="stat-card"><div class="stat-label">TODAY</div><div class="stat-value" style="font-size:1.5rem;color:var(--toxic-green);">₹<?= number_format($todayRev,0) ?></div></div>
        </div>
        <div class="card">
          <div class="card-header">
            <div class="section-title">ALL ORDERS</div>
            <input type="search" id="bill-search" class="form-control" style="max-width:200px;" placeholder="Search name…" oninput="billSearchFilter()"/>
          </div>
          <div class="table-wrap">
            <table class="admin-table" id="bills-table">
              <thead><tr><th>ORDER #</th><th>CUSTOMER</th><th>PHONE</th><th>ITEMS</th><th>PAYMENT</th><th>TOTAL</th><th>DATE</th><th>ACTIONS</th></tr></thead>
              <tbody id="bills-tbody">
                <?php if(empty($bills)): ?>
                <tr><td colspan="8" style="text-align:center;color:#333;padding:2rem;">No orders yet. Sales show here automatically.</td></tr>
                <?php else: ?>
                <?php foreach($bills as $b): ?>
                <tr data-name="<?= htmlspecialchars(strtolower($b['customer_name'])) ?>">
                  <td><strong style="color:var(--neon-yellow);">#<?= str_pad($b['bill_id'],5,'0',STR_PAD_LEFT) ?></strong></td>
                  <td><?= htmlspecialchars($b['customer_name']) ?></td>
                  <td style="color:#444;"><?= htmlspecialchars($b['customer_phone']??'—') ?></td>
                  <td><span class="badge badge-dark"><?= $b['item_count'] ?> item<?= $b['item_count']!=1?'s':'' ?></span></td>
                  <td><span class="badge badge-yellow"><?= htmlspecialchars($b['payment_method']) ?></span></td>
                  <td style="color:var(--neon-yellow);font-weight:700;">₹<?= number_format($b['total_amount'],2) ?></td>
                  <td style="font-size:.72rem;color:#333;"><?= htmlspecialchars($b['bill_date']) ?></td>
                  <td><a href="checkout.php?bill_id=<?= $b['bill_id'] ?>&view=1" target="_blank" class="btn btn-outline-w btn-sm">🧾 VIEW</a></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="delete-modal" role="dialog" aria-modal="true">
  <div class="modal" style="max-width:380px;">
    <div class="modal-head">
      <h2>🗑️ DELETE ITEM</h2>
      <p id="delete-modal-msg">This cannot be undone.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline-w" onclick="closeDeleteModal()">CANCEL</button>
      <a href="#" id="delete-confirm-link" class="btn btn-danger">YES, DELETE</a>
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script>
function switchTab(t) {
  ['list','form'].forEach(n => {
    document.getElementById('tab-panel-'+n).classList.toggle('active', n===t);
    document.getElementById('tab-'+n+'-btn').classList.toggle('active', n===t);
  });
}
function previewImage(inp) {
  const p = document.getElementById('img-preview');
  if (inp.files[0]) {
    const r = new FileReader();
    r.onload = e => { p.src=e.target.result; p.style.display='block'; };
    r.readAsDataURL(inp.files[0]);
  }
}
const ua = document.getElementById('upload-area');
if(ua){
  ['dragover','dragenter'].forEach(e=>ua.addEventListener(e,ev=>{ev.preventDefault();ua.classList.add('drag-over');}));
  ['dragleave','drop'].forEach(e=>ua.addEventListener(e,()=>ua.classList.remove('drag-over')));
}
async function toggleDisplay(id,cb) {
  try {
    const r=await fetch(`admin.php?action=toggle_display&id=${id}`);
    const d=await r.json();
    if(!d.success){cb.checked=!cb.checked;showToast('Update failed','error');}
    else showToast(d.status?'Item is now LIVE 🔥':'Item hidden from store','success');
  } catch { cb.checked=!cb.checked; showToast('Network error','error'); }
}
function confirmDelete(id,name) {
  document.getElementById('delete-modal-msg').textContent=`Permanently delete "${name}"? Can't undo this.`;
  document.getElementById('delete-confirm-link').href=`admin.php?page=stock&action=delete_item&id=${id}`;
  document.getElementById('delete-modal').classList.add('open');
}
function closeDeleteModal(){ document.getElementById('delete-modal').classList.remove('open'); }
document.getElementById('delete-modal')?.addEventListener('click',e=>{if(e.target===document.getElementById('delete-modal'))closeDeleteModal();});
function adminSearchFilter(){
  const q=document.getElementById('admin-search').value.toLowerCase();
  document.querySelectorAll('#stock-tbody tr[data-name]').forEach(r=>{
    r.style.display=(r.dataset.name.includes(q)||r.dataset.cat.includes(q))?'':'none';
  });
}
function billSearchFilter(){
  const q=document.getElementById('bill-search').value.toLowerCase();
  document.querySelectorAll('#bills-tbody tr[data-name]').forEach(r=>{
    r.style.display=r.dataset.name.includes(q)?'':'none';
  });
}
function showToast(msg,type='success'){
  const c=document.getElementById('toast-container');
  const t=document.createElement('div');
  t.className=`toast toast-${type}`;
  t.innerHTML=`<span class="toast-dot"></span>${msg}`;
  c.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(()=>t.remove(),320);},3500);
}
</script>
</body>
</html>
