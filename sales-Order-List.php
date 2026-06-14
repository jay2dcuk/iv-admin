<?php
require_once 'includes/config.php';
require_auth();
require_once 'includes/db.php';
require_once 'includes/layout.php';

// Filters from GET
$status    = $_GET['status']    ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to   = $_GET['date_to']   ?? date('Y-m-d');
$search    = clean($_GET['search'] ?? '');
$page      = max(1, (int)($_GET['page'] ?? 1));
$per_page  = 25;
$offset    = ($page - 1) * $per_page;

// Build WHERE
$where = "WHERE m.pg_Authcode != '' AND m.order_Id != ''";
$params = [];

if ($status === 'paid')      { $where .= " AND m.pg_Status = '0'"; }
elseif ($status === 'pending'){ $where .= " AND m.pg_Status != '0'"; }
elseif ($status === 'shipped'){ $where .= " AND m.oStatus = 'D'"; }
elseif ($status === 'approved'){ $where .= " AND m.approve_Status = 1"; }

if ($date_from) { $where .= " AND DATE(m.dt) >= ?"; $params[] = $date_from; }
if ($date_to)   { $where .= " AND DATE(m.dt) <= ?"; $params[] = $date_to; }

if ($search) {
    $where .= " AND (m.order_Id LIKE ? OR m.fName LIKE ? OR m.lName LIKE ? OR m.b_Post_Code LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s, $s]);
}

// Count
$total_row = DB::one("SELECT COUNT(*) as cnt FROM sales_master m $where", $params);
$total = $total_row['cnt'] ?? 0;
$total_pages = ceil($total / $per_page);

// Orders
$orders = DB::query("
    SELECT
        m.id, m.order_Id, m.dt,
        m.fName, m.lName,
        m.pg_Amount, m.ship_Amount,
        m.pg_Status, m.oStatus,
        m.approve_Status, m.process_Status,
        m.print_Status,
        m.delivery_No,
        m.b_Post_Code, m.b_Town,
        m.read_Status,
        m.admin_Notes,
        (SELECT COUNT(*) FROM sales_details d WHERE d.order_Id = m.order_Id) as item_count,
        (SELECT courier_Name FROM courier_service WHERE id = m.shipper_Id LIMIT 1) as courier
    FROM sales_master m
    $where
    ORDER BY m.dt DESC
    LIMIT $per_page OFFSET $offset
", $params);

// Summary stats
$summary = DB::one("
    SELECT
        COUNT(*) as total_orders,
        SUM(pg_Amount) as total_revenue,
        SUM(CASE WHEN pg_Status='0' THEN 1 ELSE 0 END) as paid,
        SUM(CASE WHEN approve_Status=0 AND pg_Status='0' THEN 1 ELSE 0 END) as unapproved
    FROM sales_master m $where
", $params);

function order_status(array $o): array {
    if ($o['oStatus'] === 'D') return ['shipped', 'Shipped'];
    if ($o['pg_Status'] === '0') return ['success', 'Paid'];
    if ($o['pg_Status'] === '~') return ['danger', 'Failed'];
    if ($o['pg_Status'] === '5') return ['warning', 'Declined'];
    return ['gray', 'Pending'];
}

layout_head('Sales Orders', 'orders');
?>
<body>
<?php layout_sidebar('orders'); ?>
<div class="main">
<?php layout_topbar('Sales Orders', 'All orders — sales_master'); ?>
<div class="page">

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-top"><div class="stat-icon blue"><i class="ti ti-package"></i></div></div>
      <div class="stat-val"><?= number_format($summary['total_orders'] ?? 0) ?></div>
      <div class="stat-lbl">Orders in period</div>
    </div>
    <div class="stat-card">
      <div class="stat-top"><div class="stat-icon green"><i class="ti ti-currency-pound"></i></div></div>
      <div class="stat-val">£<?= number_format($summary['total_revenue'] ?? 0, 2) ?></div>
      <div class="stat-lbl">Revenue in period</div>
    </div>
    <div class="stat-card">
      <div class="stat-top"><div class="stat-icon amber"><i class="ti ti-check"></i></div></div>
      <div class="stat-val"><?= number_format($summary['paid'] ?? 0) ?></div>
      <div class="stat-lbl">Paid orders</div>
    </div>
    <div class="stat-card">
      <div class="stat-top"><div class="stat-icon red"><i class="ti ti-clock"></i></div></div>
      <div class="stat-val"><?= number_format($summary['unapproved'] ?? 0) ?></div>
      <div class="stat-lbl">Awaiting approval</div>
    </div>
  </div>

  <!-- Filters -->
  <div class="card">
    <div class="card-head">
      <div><div class="card-title">Filter Orders</div></div>
      <div style="display:flex;gap:8px">
        <a href="sales-Order-List-Bulkprint.php" class="btn btn-secondary btn-sm"><i class="ti ti-printer"></i> Bulk Print</a>
        <a href="add-Items.php" class="btn btn-primary btn-sm"><i class="ti ti-plus"></i> New Order</a>
      </div>
    </div>
    <div class="card-body">
      <form method="GET" action="">
        <div class="filters">
          <input type="text" name="search" class="form-control" placeholder="Search order ID, name, postcode…" value="<?= htmlspecialchars($search) ?>">
          <select name="status" class="form-control">
            <option value="">All statuses</option>
            <option value="paid" <?= $status==='paid'?'selected':'' ?>>Paid</option>
            <option value="pending" <?= $status==='pending'?'selected':'' ?>>Pending</option>
            <option value="shipped" <?= $status==='shipped'?'selected':'' ?>>Shipped</option>
            <option value="approved" <?= $status==='approved'?'selected':'' ?>>Approved</option>
          </select>
          <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
          <span class="filter-label">to</span>
          <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
          <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-search"></i> Search</button>
          <a href="sales-Order-List.php" class="btn btn-secondary btn-sm"><i class="ti ti-x"></i> Clear</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Orders Table -->
  <div class="card">
    <div class="card-head">
      <div>
        <div class="card-title">Orders</div>
        <div class="card-sub">Showing <?= number_format(count($orders)) ?> of <?= number_format($total) ?> orders</div>
      </div>
    </div>
    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Town</th>
            <th>Items</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Approved</th>
            <th>Courier</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
          <tr><td colspan="10" style="text-align:center;padding:32px;color:var(--muted)">No orders found</td></tr>
          <?php else: foreach ($orders as $o):
            [$sc, $sl] = order_status($o);
          ?>
          <tr style="<?= !$o['read_Status'] ? 'font-weight:600;' : '' ?>">
            <td>
              <a href="sales-Details-Form.php?id=<?= urlencode($o['id']) ?>" style="color:var(--blue);font-family:monospace;font-size:12px;font-weight:600">
                <?= htmlspecialchars($o['order_Id']) ?>
              </a>
            </td>
            <td style="color:var(--muted);font-size:12px"><?= date('d M Y H:i', strtotime($o['dt'])) ?></td>
            <td><?= htmlspecialchars($o['fName'] . ' ' . $o['lName']) ?></td>
            <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($o['b_Town']) ?></td>
            <td style="text-align:center"><?= $o['item_count'] ?></td>
            <td><strong>£<?= number_format($o['pg_Amount'], 2) ?></strong></td>
            <td><span class="badge badge-<?= $sc ?>"><?= $sl ?></span></td>
            <td>
              <?php if ($o['approve_Status']): ?>
                <span class="badge badge-success"><i class="ti ti-check"></i> Yes</span>
              <?php else: ?>
                <span class="badge badge-gray">No</span>
              <?php endif; ?>
            </td>
            <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($o['courier'] ?? '—') ?></td>
            <td>
              <div style="display:flex;gap:4px">
                <a href="sales-Details-Form.php?id=<?= urlencode($o['id']) ?>" class="btn btn-secondary btn-sm"><i class="ti ti-eye"></i></a>
                <a href="sales-Details-Form-Print.php?id=<?= urlencode($o['id']) ?>" class="btn btn-secondary btn-sm" target="_blank"><i class="ti ti-printer"></i></a>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div style="padding:12px 18px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div class="page-info">Page <?= $page ?> of <?= $total_pages ?> (<?= number_format($total) ?> orders)</div>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a class="page-btn" href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>"><i class="ti ti-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($p = max(1,$page-2); $p <= min($total_pages,$page+2); $p++): ?>
          <a class="page-btn <?= $p===$page?'active':'' ?>" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
          <a class="page-btn" href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>"><i class="ti ti-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

</div><!-- /page -->
</div><!-- /main -->
<?php layout_foot(); ?>
