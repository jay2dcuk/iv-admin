<?php
// Shared layout helpers
// Usage: include this file, then call layout_head(), layout_sidebar(), layout_foot()

function layout_head(string $title = 'Admin', string $active = ''): void {
    global $_SESSION;
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title) ?> — Hewitts Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:#1E3A5F;--blue:#2563EB;--blue-light:#EFF6FF;--blue-mid:#BFDBFE;
  --green:#059669;--green-light:#ECFDF5;
  --amber:#D97706;--amber-light:#FFFBEB;
  --red:#DC2626;--red-light:#FEF2F2;
  --purple:#7C3AED;--purple-light:#F5F3FF;
  --text:#111827;--muted:#6B7280;--border:#E5E7EB;
  --bg:#F3F4F6;--white:#FFFFFF;
  --radius:12px;--radius-sm:8px;
  --shadow:0 1px 8px rgba(0,0,0,.06);
}
body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;font-size:14px}

/* SIDEBAR */
.sidebar{width:240px;background:var(--navy);min-height:100vh;display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;overflow-y:auto;z-index:100;scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.1) transparent}
.sidebar::-webkit-scrollbar{width:4px}
.sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:4px}
.sb-logo{padding:18px 16px;border-bottom:1px solid rgba(255,255,255,.08);flex-shrink:0}
.sb-logo-title{font-size:15px;font-weight:700;color:#fff;display:flex;align-items:center;gap:10px}
.sb-logo-icon{width:34px;height:34px;background:rgba(255,255,255,.12);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0}
.sb-logo-sub{font-size:11px;color:rgba(255,255,255,.35);margin-top:2px;padding-left:44px}
.sb-sec{padding:16px 14px 4px;font-size:10px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:rgba(255,255,255,.3)}
.sb-link{display:flex;align-items:center;gap:9px;padding:8px 12px;margin:1px 8px;border-radius:7px;color:rgba(255,255,255,.6);text-decoration:none;font-size:12.5px;font-weight:500;transition:all .12s;cursor:pointer;border:none;background:none;width:calc(100% - 16px);text-align:left}
.sb-link i{font-size:16px;flex-shrink:0;width:18px;text-align:center}
.sb-link:hover{background:rgba(255,255,255,.08);color:#fff}
.sb-link.active{background:var(--blue);color:#fff}
.sb-sub{padding-left:16px}
.sb-sub .sb-link{font-size:12px;padding:6px 12px;color:rgba(255,255,255,.5)}
.sb-sub .sb-link:hover{color:#fff}
.sb-sub .sb-link.active{background:rgba(255,255,255,.12);color:#fff}
.sb-badge{margin-left:auto;background:var(--red);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px;min-width:18px;text-align:center}
.sb-badge.warn{background:var(--amber)}
.sb-badge.info{background:var(--blue)}
.sb-group{margin-bottom:2px}
.sb-group-toggle{display:flex;align-items:center;gap:9px;padding:8px 12px;margin:1px 8px;border-radius:7px;color:rgba(255,255,255,.6);font-size:12.5px;font-weight:500;cursor:pointer;border:none;background:none;width:calc(100% - 16px);text-align:left;transition:all .12s}
.sb-group-toggle i.ti-chevron-down{margin-left:auto;font-size:12px;transition:transform .2s}
.sb-group-toggle:hover{background:rgba(255,255,255,.08);color:#fff}
.sb-group-toggle.open i.ti-chevron-down{transform:rotate(180deg)}
.sb-group-toggle i:first-child{font-size:16px;flex-shrink:0;width:18px;text-align:center}
.sb-children{display:none}
.sb-children.open{display:block}
.sb-footer{padding:12px;border-top:1px solid rgba(255,255,255,.08);flex-shrink:0}
.sb-user{display:flex;align-items:center;gap:9px}
.sb-avatar{width:30px;height:30px;background:rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0}
.sb-name{font-size:12px;font-weight:600;color:#fff}
.sb-role{font-size:10px;color:rgba(255,255,255,.4)}
.sb-logout{margin-left:auto;color:rgba(255,255,255,.4);font-size:16px;cursor:pointer;text-decoration:none}
.sb-logout:hover{color:#fff}

/* MAIN */
.main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-width:0}

/* TOPBAR */
.topbar{background:var(--white);border-bottom:1px solid var(--border);height:56px;padding:0 24px;display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:50}
.topbar-title{font-size:16px;font-weight:700;color:var(--text)}
.topbar-sub{font-size:11px;color:var(--muted);margin-top:1px}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:8px}
.search{display:flex;align-items:center;gap:7px;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:7px 11px;width:220px;transition:border-color .15s}
.search:focus-within{border-color:var(--blue)}
.search input{border:none;background:none;outline:none;font-size:12px;width:100%;font-family:inherit;color:var(--text)}
.search input::placeholder{color:var(--muted)}
.search i{color:var(--muted);font-size:15px}
.icon-btn{width:34px;height:34px;border-radius:var(--radius-sm);border:1.5px solid var(--border);background:var(--white);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:16px;text-decoration:none;transition:all .12s;position:relative}
.icon-btn:hover{border-color:var(--blue);color:var(--blue)}
.notif-dot{position:absolute;top:5px;right:5px;width:7px;height:7px;background:var(--red);border-radius:50%;border:2px solid white}

/* PAGE */
.page{padding:20px 24px;flex:1}
.page-header{margin-bottom:20px;display:flex;align-items:center;justify-content:space-between}
.page-title{font-size:18px;font-weight:700;color:var(--text)}
.page-sub{font-size:12px;color:var(--muted);margin-top:2px}

/* CARDS */
.card{background:var(--white);border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow);overflow:hidden;margin-bottom:16px}
.card-head{padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:14px;font-weight:600;color:var(--text)}
.card-sub{font-size:11px;color:var(--muted);margin-top:1px}
.card-body{padding:18px}
.card-link{font-size:12px;color:var(--blue);font-weight:500;cursor:pointer;border:none;background:none;font-family:inherit;text-decoration:none}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;border:none;font-family:inherit;transition:all .12s;text-decoration:none}
.btn i{font-size:15px}
.btn-primary{background:var(--blue);color:#fff}
.btn-primary:hover{background:#1d4ed8}
.btn-secondary{background:var(--white);color:var(--text);border:1px solid var(--border)}
.btn-secondary:hover{background:var(--bg)}
.btn-success{background:var(--green);color:#fff}
.btn-danger{background:var(--red);color:#fff}
.btn-sm{padding:5px 10px;font-size:12px}
.btn-sm i{font-size:13px}

/* TABLE */
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
thead th{padding:9px 14px;font-size:10px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);text-align:left;background:var(--bg);border-bottom:1px solid var(--border);white-space:nowrap}
tbody td{padding:11px 14px;font-size:13px;border-bottom:1px solid var(--border);vertical-align:middle}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover td{background:rgba(37,99,235,.03)}

/* BADGES */
.badge{display:inline-flex;align-items:center;gap:3px;padding:3px 8px;border-radius:99px;font-size:10px;font-weight:600;white-space:nowrap}
.badge-success{background:var(--green-light);color:var(--green)}
.badge-warning{background:var(--amber-light);color:var(--amber)}
.badge-info{background:var(--blue-light);color:var(--blue)}
.badge-danger{background:var(--red-light);color:var(--red)}
.badge-purple{background:var(--purple-light);color:var(--purple)}
.badge-gray{background:var(--bg);color:var(--muted);border:1px solid var(--border)}

/* FORMS */
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:12px;font-weight:500;color:var(--text);margin-bottom:5px}
.form-control{width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:13px;font-family:inherit;color:var(--text);outline:none;transition:border-color .15s;background:var(--white)}
.form-control:focus{border-color:var(--blue)}
select.form-control{cursor:pointer}
.form-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px}

/* FILTERS BAR */
.filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center}
.filters .form-control{width:auto;min-width:140px;padding:7px 10px;font-size:12px}
.filter-label{font-size:12px;color:var(--muted);font-weight:500}

/* PAGINATION */
.pagination{display:flex;align-items:center;gap:4px;padding:12px 0}
.page-btn{width:32px;height:32px;border-radius:6px;border:1px solid var(--border);background:var(--white);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;color:var(--text);text-decoration:none;transition:all .12s}
.page-btn:hover,.page-btn.active{background:var(--blue);color:#fff;border-color:var(--blue)}
.page-info{font-size:12px;color:var(--muted);margin:0 8px}

/* ALERTS */
.alert{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.alert i{font-size:16px;flex-shrink:0}
.alert-success{background:var(--green-light);color:var(--green);border:1px solid #A7F3D0}
.alert-danger{background:var(--red-light);color:var(--red);border:1px solid #FECACA}
.alert-warning{background:var(--amber-light);color:var(--amber);border:1px solid #FDE68A}
.alert-info{background:var(--blue-light);color:var(--blue);border:1px solid var(--blue-mid)}

/* STATS */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px}
.stat-card{background:var(--white);border-radius:var(--radius);padding:16px;border:1px solid var(--border);box-shadow:var(--shadow)}
.stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.stat-icon{width:36px;height:36px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center}
.stat-icon i{font-size:19px}
.stat-icon.blue{background:var(--blue-light);color:var(--blue)}
.stat-icon.green{background:var(--green-light);color:var(--green)}
.stat-icon.amber{background:var(--amber-light);color:var(--amber)}
.stat-icon.red{background:var(--red-light);color:var(--red)}
.stat-icon.purple{background:var(--purple-light);color:var(--purple)}
.stat-val{font-size:24px;font-weight:700;color:var(--text);letter-spacing:-.5px}
.stat-lbl{font-size:11px;color:var(--muted);margin-top:2px}
.stat-trend{font-size:11px;font-weight:600;padding:2px 7px;border-radius:99px;display:flex;align-items:center;gap:3px}
.trend-up{background:var(--green-light);color:var(--green)}
.trend-dn{background:var(--red-light);color:var(--red)}

/* MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;display:none;align-items:center;justify-content:center}
.modal-overlay.open{display:flex}
.modal-box{background:var(--white);border-radius:16px;width:90%;max-width:600px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2)}
.modal-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.modal-title{font-size:15px;font-weight:600}
.modal-close{background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);line-height:1}
.modal-body{padding:20px}
.modal-foot{padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px}

/* AI CHAT */
.ai-fab{position:fixed;bottom:20px;right:20px;width:48px;height:48px;background:var(--purple);border-radius:14px;border:none;color:#fff;cursor:pointer;box-shadow:0 4px 16px rgba(124,58,237,.4);display:flex;align-items:center;justify-content:center;z-index:199;transition:all .2s}
.ai-fab:hover{transform:scale(1.06)}
.ai-fab i{font-size:22px}
.chat-panel{position:fixed;bottom:20px;right:20px;width:340px;max-height:500px;background:var(--white);border-radius:16px;box-shadow:0 16px 48px rgba(0,0,0,.16);border:1px solid var(--border);display:none;flex-direction:column;z-index:200}
.chat-panel.open{display:flex}
.chat-head{padding:12px 14px;background:var(--navy);border-radius:16px 16px 0 0;display:flex;align-items:center;gap:9px}
.chat-head-icon{width:30px;height:30px;background:var(--purple);border-radius:8px;display:flex;align-items:center;justify-content:center}
.chat-head-icon i{font-size:15px;color:#fff}
.chat-head-name{font-size:13px;font-weight:600;color:#fff}
.chat-head-status{font-size:10px;color:rgba(255,255,255,.45)}
.chat-close{margin-left:auto;color:rgba(255,255,255,.5);background:none;border:none;font-size:17px;cursor:pointer;line-height:1}
.chat-msgs{flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:8px;max-height:320px}
.msg{display:flex;gap:7px}
.msg.user{flex-direction:row-reverse}
.msg-av{width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.msg-av.ai{background:var(--purple)}
.msg-av.user{background:var(--blue)}
.msg-av i{font-size:13px;color:#fff}
.msg-bubble{max-width:80%;padding:8px 11px;border-radius:10px;font-size:12px;line-height:1.5}
.msg.ai .msg-bubble{background:var(--bg);color:var(--text);border-radius:3px 10px 10px 10px}
.msg.user .msg-bubble{background:var(--blue);color:#fff;border-radius:10px 3px 10px 10px}
.typing{display:flex;gap:3px;padding:3px 0}
.typing span{width:5px;height:5px;background:var(--muted);border-radius:50%;animation:bounce .8s infinite}
.typing span:nth-child(2){animation-delay:.15s}
.typing span:nth-child(3){animation-delay:.3s}
@keyframes bounce{0%,80%,100%{transform:translateY(0)}40%{transform:translateY(-5px)}}
.chat-input-wrap{padding:9px 10px;border-top:1px solid var(--border);display:flex;gap:7px}
.chat-input{flex:1;border:1.5px solid var(--border);border-radius:8px;padding:7px 10px;font-size:12px;resize:none;outline:none;font-family:inherit;max-height:70px;line-height:1.4;transition:border-color .15s}
.chat-input:focus{border-color:var(--blue)}
.chat-send{width:32px;height:32px;background:var(--blue);border:none;border-radius:8px;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.chat-send i{font-size:15px}

@media(max-width:900px){.sidebar{transform:translateX(-100%)}.main{margin-left:0}}
</style>
<?php } ?>

<?php function layout_sidebar(string $active = ''): void {
    global $_SESSION;
    $name = $_SESSION['admin_name'] ?? 'Admin';
    $initials = strtoupper(implode('', array_map(fn($p) => $p[0], array_slice(explode(' ', $name), 0, 2))));
    $url = APP_URL;
?>
<nav class="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-title">
      <div class="sb-logo-icon">🎓</div>
      Hewitts Admin
    </div>
    <div class="sb-logo-sub">of Croydon</div>
  </div>

  <div class="sb-sec">Overview</div>
  <a class="sb-link <?= $active==='dashboard'?'active':'' ?>" href="<?=$url?>/dashboard.php"><i class="ti ti-layout-dashboard"></i> Dashboard</a>
  <a class="sb-link <?= $active==='ai'?'active':'' ?>" href="#"><i class="ti ti-brain"></i> AI Insights</a>

  <div class="sb-sec">Orders</div>
  <a class="sb-link <?= $active==='orders'?'active':'' ?>" href="<?=$url?>/sales-Order-List.php"><i class="ti ti-package"></i> All Orders</a>
  <a class="sb-link <?= $active==='orders-tp'?'active':'' ?>" href="<?=$url?>/sales-Order-ListTP.php"><i class="ti ti-clock"></i> Today's Orders</a>
  <a class="sb-link <?= $active==='bulk-print'?'active':'' ?>" href="<?=$url?>/sales-Order-List-Bulkprint.php"><i class="ti ti-printer"></i> Bulk Print</a>
  <a class="sb-link <?= $active==='cancellations'?'active':'' ?>" href="<?=$url?>/cancel-Item-List.php"><i class="ti ti-arrow-back-up"></i> Cancellations</a>
  <a class="sb-link <?= $active==='awaiting'?'active':'' ?>" href="<?=$url?>/clear-Wait-Delivery.php"><i class="ti ti-clock-pause"></i> Awaiting Delivery</a>
  <a class="sb-link <?= $active==='ostock'?'active':'' ?>" href="<?=$url?>/outof-Stock-List.php"><i class="ti ti-alert-circle"></i> Out of Stock</a>
  <a class="sb-link <?= $active==='refunds'?'active':'' ?>" href="<?=$url?>/refund-List.php"><i class="ti ti-receipt-refund"></i> Refunds</a>
  <a class="sb-link <?= $active==='move'?'active':'' ?>" href="<?=$url?>/order-Move.php"><i class="ti ti-arrows-move"></i> Move Order</a>

  <div class="sb-sec">Catalogue</div>

  <div class="sb-group">
    <button class="sb-group-toggle <?= in_array($active,['uniform','uniform-cat','uniform-price','uniform-stock','uniform-links'])?' open':'' ?>" onclick="toggleGroup(this)">
      <i class="ti ti-shirt"></i> Uniforms <i class="ti ti-chevron-down"></i>
    </button>
    <div class="sb-sub sb-children <?= in_array($active,['uniform','uniform-cat','uniform-price','uniform-stock','uniform-links'])?' open':'' ?>">
      <a class="sb-link <?=$active==='uniform'?'active':''?>" href="<?=$url?>/uniform-Master-List.php"><i class="ti ti-list"></i> Items</a>
      <a class="sb-link" href="<?=$url?>/uniform-Master-New.php"><i class="ti ti-plus"></i> New Item</a>
      <a class="sb-link <?=$active==='uniform-cat'?'active':''?>" href="<?=$url?>/uniform-Main-Category-List.php"><i class="ti ti-category"></i> Departments</a>
      <a class="sb-link" href="<?=$url?>/uniform-Category-List.php"><i class="ti ti-tag"></i> Product Groups</a>
      <a class="sb-link <?=$active==='uniform-price'?'active':''?>" href="<?=$url?>/uniform-Price-Discount-List.php"><i class="ti ti-discount"></i> Price & Discount</a>
      <a class="sb-link" href="<?=$url?>/uniform-Menu-Link-List.php"><i class="ti ti-link"></i> Menu Links</a>
      <a class="sb-link" href="<?=$url?>/review-Content-List.php"><i class="ti ti-star"></i> Reviews</a>
    </div>
  </div>

  <div class="sb-group">
    <button class="sb-group-toggle <?= in_array($active,['sports','sports-cat','sports-price'])?' open':'' ?>" onclick="toggleGroup(this)">
      <i class="ti ti-ball-football"></i> Sports Items <i class="ti ti-chevron-down"></i>
    </button>
    <div class="sb-sub sb-children <?= in_array($active,['sports','sports-cat','sports-price'])?' open':'' ?>">
      <a class="sb-link <?=$active==='sports'?'active':''?>" href="<?=$url?>/sports-Master-List.php"><i class="ti ti-list"></i> Items</a>
      <a class="sb-link" href="<?=$url?>/sports-Master-New.php"><i class="ti ti-plus"></i> New Item</a>
      <a class="sb-link" href="<?=$url?>/sports-Main-Category-List.php"><i class="ti ti-category"></i> Departments</a>
      <a class="sb-link" href="<?=$url?>/sports-Category-List.php"><i class="ti ti-tag"></i> Product Groups</a>
      <a class="sb-link" href="<?=$url?>/sports-Price-Discount-List.php"><i class="ti ti-discount"></i> Price & Discount</a>
      <a class="sb-link" href="<?=$url?>/sports-Menu-Link-List.php"><i class="ti ti-link"></i> Menu Links</a>
    </div>
  </div>

  <div class="sb-group">
    <button class="sb-group-toggle <?= in_array($active,['scout','scout-cat','scout-price'])?' open':'' ?>" onclick="toggleGroup(this)">
      <i class="ti ti-award"></i> Scouts & Guides <i class="ti ti-chevron-down"></i>
    </button>
    <div class="sb-sub sb-children <?= in_array($active,['scout','scout-cat','scout-price'])?' open':'' ?>">
      <a class="sb-link <?=$active==='scout'?'active':''?>" href="<?=$url?>/scout-Master-List.php"><i class="ti ti-list"></i> Items</a>
      <a class="sb-link" href="<?=$url?>/scout-Master-New.php"><i class="ti ti-plus"></i> New Item</a>
      <a class="sb-link" href="<?=$url?>/scout-Main-Category-List.php"><i class="ti ti-category"></i> Departments</a>
      <a class="sb-link" href="<?=$url?>/scout-Category-List.php"><i class="ti ti-tag"></i> Product Groups</a>
      <a class="sb-link" href="<?=$url?>/scout-Price-Discount-List.php"><i class="ti ti-discount"></i> Price & Discount</a>
      <a class="sb-link" href="<?=$url?>/scout-Menu-Link-List.php"><i class="ti ti-link"></i> Menu Links</a>
    </div>
  </div>

  <div class="sb-group">
    <button class="sb-group-toggle <?= in_array($active,['workwear','workwear-cat','workwear-price'])?' open':'' ?>" onclick="toggleGroup(this)">
      <i class="ti ti-hard-hat"></i> Workwear <i class="ti ti-chevron-down"></i>
    </button>
    <div class="sb-sub sb-children <?= in_array($active,['workwear','workwear-cat','workwear-price'])?' open':'' ?>">
      <a class="sb-link <?=$active==='workwear'?'active':''?>" href="<?=$url?>/workwear-Master-List.php"><i class="ti ti-list"></i> Items</a>
      <a class="sb-link" href="<?=$url?>/workwear-Master-New.php"><i class="ti ti-plus"></i> New Item</a>
      <a class="sb-link" href="<?=$url?>/workwear-Main-Category-List.php"><i class="ti ti-category"></i> Departments</a>
      <a class="sb-link" href="<?=$url?>/workwear-Category-List.php"><i class="ti ti-tag"></i> Product Groups</a>
      <a class="sb-link" href="<?=$url?>/workwear-Price-Discount-List.php"><i class="ti ti-discount"></i> Price & Discount</a>
      <a class="sb-link" href="<?=$url?>/workwear-Menu-Link-List.php"><i class="ti ti-link"></i> Menu Links</a>
    </div>
  </div>

  <a class="sb-link <?=$active==='nametapes'?'active':''?>" href="<?=$url?>/nametape-List.php"><i class="ti ti-tag"></i> Labels & Nametapes</a>
  <a class="sb-link <?=$active==='adverts'?'active':''?>" href="<?=$url?>/advt-Master-List.php"><i class="ti ti-speakerphone"></i> Adverts</a>

  <div class="sb-sec">Schools & Users</div>
  <a class="sb-link <?=$active==='schools'?'active':''?>" href="<?=$url?>/school-Master-List.php"><i class="ti ti-school"></i> Schools</a>
  <a class="sb-link <?=$active==='customers'?'active':''?>" href="<?=$url?>/site-User-List.php"><i class="ti ti-users"></i> Customers</a>
  <a class="sb-link <?=$active==='admin-users'?'active':''?>" href="<?=$url?>/admin-User-List.php"><i class="ti ti-user-shield"></i> Admin Users</a>
  <a class="sb-link <?=$active==='access'?'active':''?>" href="<?=$url?>/access-List.php"><i class="ti ti-shield-lock"></i> Access Control</a>

  <div class="sb-sec">Finance & Reports</div>
  <a class="sb-link <?=$active==='reports'?'active':''?>" href="<?=$url?>/sales-Report-Details2.php"><i class="ti ti-chart-bar"></i> Sales Reports</a>
  <a class="sb-link <?=$active==='daily'?'active':''?>" href="<?=$url?>/daily-Sales-Report.php"><i class="ti ti-calendar-stats"></i> Daily Sales</a>
  <a class="sb-link <?=$active==='school-report'?'active':''?>" href="<?=$url?>/school-Sales-Report.php"><i class="ti ti-school"></i> School Sales</a>
  <a class="sb-link <?=$active==='coupons'?'active':''?>" href="<?=$url?>/promo-Coupon-List.php"><i class="ti ti-ticket"></i> Promo Coupons</a>
  <a class="sb-link <?=$active==='loyalty'?'active':''?>" href="<?=$url?>/loyalty-List.php"><i class="ti ti-star"></i> Loyalty</a>
  <a class="sb-link <?=$active==='spl-discount'?'active':''?>" href="<?=$url?>/spl-Discount-List.php"><i class="ti ti-discount-2"></i> Special Discounts</a>

  <div class="sb-sec">Marketing</div>
  <a class="sb-link <?=$active==='email'?'active':''?>" href="<?=$url?>/mass-Mail-New.php"><i class="ti ti-mail"></i> Mass Email</a>
  <a class="sb-link <?=$active==='sent-mail'?'active':''?>" href="<?=$url?>/mass-Mail-Sent-List.php"><i class="ti ti-mail-check"></i> Sent Emails</a>
  <a class="sb-link <?=$active==='sms'?'active':''?>" href="<?=$url?>/sms-Settings.php"><i class="ti ti-message"></i> SMS</a>
  <a class="sb-link <?=$active==='news'?'active':''?>" href="<?=$url?>/news-Master-List.php"><i class="ti ti-news"></i> News</a>
  <a class="sb-link <?=$active==='reminders'?'active':''?>" href="<?=$url?>/reminder-List.php"><i class="ti ti-bell"></i> Reminders</a>
  <a class="sb-link <?=$active==='queries'?'active':''?>" href="<?=$url?>/query-Content-List.php"><i class="ti ti-help"></i> Customer Queries</a>

  <div class="sb-sec">System</div>
  <a class="sb-link <?=$active==='couriers'?'active':''?>" href="<?=$url?>/courier-List.php"><i class="ti ti-truck"></i> Couriers</a>
  <a class="sb-link <?=$active==='shipping'?'active':''?>" href="<?=$url?>/shipping-Master-List.php"><i class="ti ti-package-import"></i> Shipping</a>
  <a class="sb-link <?=$active==='opening-times'?'active':''?>" href="<?=$url?>/opening-Times-New.php"><i class="ti ti-clock"></i> Opening Times</a>
  <a class="sb-link <?=$active==='pages'?'active':''?>" href="<?=$url?>/page-Master-List.php"><i class="ti ti-file-text"></i> Pages</a>
  <a class="sb-link <?=$active==='analytics'?'active':''?>" href="<?=$url?>/analytics_dashboard.php"><i class="ti ti-chart-dots"></i> Analytics</a>
  <a class="sb-link <?=$active==='change-pwd'?'active':''?>" href="<?=$url?>/change-password.php"><i class="ti ti-key"></i> Change Password</a>

  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar"><?= $initials ?></div>
      <div>
        <div class="sb-name"><?= htmlspecialchars($name) ?></div>
        <div class="sb-role">Administrator</div>
      </div>
      <a href="<?=$url?>/logOut.php" class="sb-logout" title="Log out"><i class="ti ti-logout"></i></a>
    </div>
  </div>
</nav>
<?php } ?>

<?php function layout_topbar(string $title, string $subtitle = ''): void { ?>
<header class="topbar">
  <div>
    <div class="topbar-title"><?= htmlspecialchars($title) ?></div>
    <?php if ($subtitle): ?><div class="topbar-sub"><?= htmlspecialchars($subtitle) ?></div><?php endif; ?>
  </div>
  <div class="topbar-right">
    <div class="search">
      <i class="ti ti-search"></i>
      <input type="text" placeholder="Search…" id="globalSearch">
    </div>
    <a href="outof-Stock-List.php" class="icon-btn" title="Stock alerts">
      <i class="ti ti-alert-circle"></i>
      <span class="notif-dot"></span>
    </a>
    <a href="sales-Order-List.php" class="icon-btn" title="Orders">
      <i class="ti ti-package"></i>
    </a>
  </div>
</header>
<?php } ?>

<?php function layout_foot(): void { ?>
<button class="ai-fab" id="fab" onclick="openChat()"><i class="ti ti-sparkles"></i></button>
<div class="chat-panel" id="chat">
  <div class="chat-head">
    <div class="chat-head-icon"><i class="ti ti-robot"></i></div>
    <div>
      <div class="chat-head-name">Hewitts AI</div>
      <div class="chat-head-status">Powered by Claude</div>
    </div>
    <button class="chat-close" onclick="closeChat()"><i class="ti ti-x"></i></button>
  </div>
  <div class="chat-msgs" id="msgs">
    <div class="msg ai">
      <div class="msg-av ai"><i class="ti ti-robot"></i></div>
      <div class="msg-bubble">Hi! Ask me anything about your orders, stock, schools or sales.</div>
    </div>
  </div>
  <div class="chat-input-wrap">
    <textarea class="chat-input" id="cinput" placeholder="Ask anything…" rows="1"
      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendAI()}"
      oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"></textarea>
    <button class="chat-send" id="csend" onclick="sendAI()"><i class="ti ti-send"></i></button>
  </div>
</div>
<script>
function toggleGroup(btn){
  btn.classList.toggle('open');
  btn.nextElementSibling.classList.toggle('open');
}
function openChat(){document.getElementById('chat').classList.add('open');document.getElementById('fab').style.display='none';document.getElementById('cinput').focus();}
function closeChat(){document.getElementById('chat').classList.remove('open');document.getElementById('fab').style.display='flex';}
async function sendAI(){
  const input=document.getElementById('cinput');
  const text=input.value.trim();if(!text)return;
  addMsg(text,'user');input.value='';input.style.height='auto';
  document.getElementById('csend').disabled=true;
  const tid='t'+Date.now();addTyping(tid);
  try{
    const r=await fetch('api/ai-chat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:text})});
    removeTyping(tid);
    const d=await r.json();addMsg(d.reply||'Sorry, no response.','ai');
  }catch(e){removeTyping(tid);addMsg('Sorry, AI is unavailable right now.','ai');}
  document.getElementById('csend').disabled=false;
}
function addMsg(text,role){
  const msgs=document.getElementById('msgs');
  const d=document.createElement('div');d.className='msg '+role;
  d.innerHTML=`<div class="msg-av ${role}"><i class="ti ti-${role==='ai'?'robot':'user'}"></i></div><div class="msg-bubble">${text.replace(/\n/g,'<br>')}</div>`;
  msgs.appendChild(d);msgs.scrollTop=msgs.scrollHeight;
}
function addTyping(id){
  const msgs=document.getElementById('msgs');
  const d=document.createElement('div');d.className='msg ai';d.id=id;
  d.innerHTML='<div class="msg-av ai"><i class="ti ti-robot"></i></div><div class="msg-bubble"><div class="typing"><span></span><span></span><span></span></div></div>';
  msgs.appendChild(d);msgs.scrollTop=msgs.scrollHeight;
}
function removeTyping(id){const e=document.getElementById(id);if(e)e.remove();}
</script>
</body></html>
<?php } ?>
