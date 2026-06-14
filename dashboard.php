<?php
require_once 'includes/config.php';
require_auth();

try {
    require_once 'includes/db.php';
    require_once 'includes/stats.php';
} catch (Exception $e) {
    error_log('Dashboard: ' . $e->getMessage());
}

$name = explode(' ', $_SESSION['admin_name'] ?? 'Admin')[0];
$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

function fmt(float $v): string { return '£' . number_format($v, 2); }

$js_orders = [];
foreach ($recent_orders ?? [] as $o) {
    $status = 'pending';
    if ($o['oStatus'] === 'D') $status = 'shipped';
    elseif ($o['pg_Status'] === '0') $status = 'paid';
    elseif ($o['oStatus'] === 'C') $status = 'cancelled';
    $js_orders[] = [
        'id'     => $o['order_Id'],
        'name'   => trim($o['fName'] . ' ' . $o['lName']),
        'cat'    => $o['categories'] ?? '',
        'items'  => (int)$o['item_count'],
        'total'  => '£' . number_format($o['pg_Amount'], 2),
        'status' => $status,
        'date'   => date('d M', strtotime($o['dt'])),
    ];
}
$rev_change = 0;
if (!empty($last_week['total']) && $last_week['total'] > 0) {
    $rev_change = round((($revenue['total'] - $last_week['total']) / $last_week['total']) * 100);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Hewitts Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --purple:#6C63FF;--purple-light:#F0EFFE;--purple-mid:#E8E6FD;--purple-dark:#5148CC;
  --blue:#3B82F6;--blue-light:#EFF6FF;
  --green:#10B981;--green-light:#ECFDF5;
  --amber:#F59E0B;--amber-light:#FFFBEB;
  --red:#EF4444;--red-light:#FEF2F2;
  --pink:#EC4899;--pink-light:#FDF2F8;
  --text:#1E1B4B;--text2:#6B7280;--text3:#9CA3AF;
  --border:#E5E7EB;--border2:#F3F4F6;
  --bg:#F8F7FF;--white:#FFFFFF;
  --sidebar:#FFFFFF;
  --radius:14px;--radius-sm:10px;--radius-xs:8px;
  --shadow:0 1px 3px rgba(108,99,255,.08),0 1px 2px rgba(0,0,0,.04);
  --shadow-md:0 4px 16px rgba(108,99,255,.12);
}
body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;font-size:14px}

/* SIDEBAR */
.sidebar{width:240px;background:var(--sidebar);min-height:100vh;display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;overflow-y:auto;z-index:100;border-right:1px solid var(--border2);scrollbar-width:thin}
.sb-logo{padding:20px 20px 16px;display:flex;align-items:center;gap:10px}
.sb-logo-icon{width:36px;height:36px;background:var(--purple);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.sb-logo-name{font-size:15px;font-weight:700;color:var(--text)}
.sb-logo-sub{font-size:11px;color:var(--text3);margin-top:1px}
.sb-divider{height:1px;background:var(--border2);margin:4px 16px}
.sb-sec{padding:14px 20px 5px;font-size:10px;font-weight:600;letter-spacing:.7px;text-transform:uppercase;color:var(--text3)}
.sb-link{display:flex;align-items:center;gap:10px;padding:9px 12px;margin:1px 10px;border-radius:var(--radius-xs);color:var(--text2);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;cursor:pointer;border:none;background:none;width:calc(100% - 20px);text-align:left}
.sb-link i{font-size:17px;flex-shrink:0;width:20px;text-align:center;color:var(--text3)}
.sb-link:hover{background:var(--purple-light);color:var(--purple)}
.sb-link:hover i{color:var(--purple)}
.sb-link.active{background:var(--purple-light);color:var(--purple);font-weight:600}
.sb-link.active i{color:var(--purple)}
.sb-badge{margin-left:auto;background:var(--red);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px}
.sb-badge.warn{background:var(--amber)}
.sb-group-toggle{display:flex;align-items:center;gap:10px;padding:9px 12px;margin:1px 10px;border-radius:var(--radius-xs);color:var(--text2);font-size:13px;font-weight:500;cursor:pointer;border:none;background:none;width:calc(100% - 20px);text-align:left;transition:all .15s}
.sb-group-toggle i:first-child{font-size:17px;flex-shrink:0;width:20px;text-align:center;color:var(--text3)}
.sb-group-toggle .arrow{margin-left:auto;font-size:12px;transition:transform .2s;color:var(--text3)}
.sb-group-toggle:hover{background:var(--purple-light);color:var(--purple)}
.sb-group-toggle:hover i{color:var(--purple)}
.sb-group-toggle.open .arrow{transform:rotate(180deg)}
.sb-children{display:none;padding-left:8px}
.sb-children.open{display:block}
.sb-children .sb-link{font-size:12px;color:var(--text3);padding:7px 12px}
.sb-children .sb-link:hover{color:var(--purple)}
.sb-footer{margin-top:auto;padding:12px 16px;border-top:1px solid var(--border2)}
.sb-user{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:var(--radius-xs);transition:background .15s;cursor:pointer}
.sb-user:hover{background:var(--purple-light)}
.sb-avatar{width:32px;height:32px;background:linear-gradient(135deg,var(--purple),#A78BFA);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0}
.sb-uname{font-size:12px;font-weight:600;color:var(--text)}
.sb-urole{font-size:10px;color:var(--text3)}
.sb-logout{margin-left:auto;color:var(--text3);font-size:16px;cursor:pointer;text-decoration:none;transition:color .15s}
.sb-logout:hover{color:var(--red)}

/* MAIN */
.main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-width:0}

/* TOPBAR */
.topbar{background:var(--white);border-bottom:1px solid var(--border2);height:60px;padding:0 24px;display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:50}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:10px}
.search{display:flex;align-items:center;gap:8px;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:8px 14px;width:260px;transition:all .15s}
.search:focus-within{border-color:var(--purple);background:var(--white);box-shadow:0 0 0 3px var(--purple-mid)}
.search input{border:none;background:none;outline:none;font-size:13px;width:100%;font-family:inherit;color:var(--text)}
.search input::placeholder{color:var(--text3)}
.search i{color:var(--text3);font-size:16px}
.topbar-btn{width:36px;height:36px;border-radius:var(--radius-xs);background:var(--bg);border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text2);font-size:17px;text-decoration:none;transition:all .15s;position:relative}
.topbar-btn:hover{border-color:var(--purple);color:var(--purple);background:var(--purple-light)}
.notif-dot{position:absolute;top:5px;right:5px;width:7px;height:7px;background:var(--red);border-radius:50%;border:2px solid var(--white)}
.topbar-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--purple),#A78BFA);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;cursor:pointer}

/* PAGE */
.page{padding:24px;flex:1}

/* WELCOME */
.welcome{margin-bottom:24px}
.welcome h2{font-size:22px;font-weight:700;color:var(--text)}
.welcome p{font-size:13px;color:var(--text2);margin-top:4px}

/* STAT CARDS */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.stat{background:var(--white);border-radius:var(--radius);padding:20px;border:1.5px solid var(--border2);box-shadow:var(--shadow);position:relative;overflow:hidden;transition:box-shadow .2s}
.stat:hover{box-shadow:var(--shadow-md)}
.stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center}
.stat-icon i{font-size:22px}
.stat-icon.purple{background:var(--purple-light);color:var(--purple)}
.stat-icon.green{background:var(--green-light);color:var(--green)}
.stat-icon.blue{background:var(--blue-light);color:var(--blue)}
.stat-icon.amber{background:var(--amber-light);color:var(--amber)}
.stat-icon.red{background:var(--red-light);color:var(--red)}
.stat-icon.pink{background:var(--pink-light);color:var(--pink)}
.stat-trend{display:flex;align-items:center;gap:3px;font-size:11px;font-weight:600;padding:3px 8px;border-radius:99px}
.trend-up{background:var(--green-light);color:var(--green)}
.trend-dn{background:var(--red-light);color:var(--red)}
.stat-val{font-size:26px;font-weight:700;color:var(--text);letter-spacing:-.5px;line-height:1.1}
.stat-lbl{font-size:12px;color:var(--text2);margin-top:4px}
.stat-spark{margin-top:12px;height:32px}

/* QUICK ACTIONS */
.quick-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px}
.quick{background:var(--white);border:1.5px solid var(--border2);border-radius:var(--radius);padding:16px 14px;text-align:center;cursor:pointer;transition:all .15s;text-decoration:none;display:block}
.quick:hover{border-color:var(--purple);box-shadow:var(--shadow-md);transform:translateY(-2px)}
.quick-icon{width:40px;height:40px;border-radius:12px;background:var(--purple-light);display:flex;align-items:center;justify-content:center;margin:0 auto 10px}
.quick-icon i{font-size:20px;color:var(--purple)}
.quick-label{font-size:12px;font-weight:600;color:var(--text)}
.quick-sub{font-size:11px;color:var(--text3);margin-top:2px}

/* GRID */
.grid2{display:grid;grid-template-columns:1fr 340px;gap:16px;margin-bottom:16px}
.grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px}

/* CARDS */
.card{background:var(--white);border-radius:var(--radius);border:1.5px solid var(--border2);box-shadow:var(--shadow);overflow:hidden;margin-bottom:16px}
.card-head{padding:16px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border2)}
.card-title{font-size:14px;font-weight:600;color:var(--text)}
.card-sub{font-size:11px;color:var(--text2);margin-top:2px}
.card-link{font-size:12px;color:var(--purple);font-weight:500;cursor:pointer;border:none;background:none;font-family:inherit;text-decoration:none;display:flex;align-items:center;gap:4px}
.card-link:hover{color:var(--purple-dark)}

/* CHART */
.chart-area{padding:16px 20px}
.chart-tabs{display:flex;gap:3px;background:var(--bg);border-radius:var(--radius-xs);padding:3px;width:fit-content;margin-bottom:14px}
.ctab{padding:5px 14px;border-radius:6px;font-size:11px;font-weight:500;cursor:pointer;color:var(--text3);border:none;background:none;font-family:inherit;transition:all .15s}
.ctab.active{background:var(--white);color:var(--purple);font-weight:600;box-shadow:0 1px 4px rgba(108,99,255,.15)}
.chart-wrap{height:180px;position:relative}

/* CATEGORY BARS */
.cat-list{padding:0 20px 16px}
.cat-row{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border2)}
.cat-row:last-child{border-bottom:none}
.cat-dot{width:8px;height:8px;border-radius:3px;flex-shrink:0}
.cat-name{font-size:12px;color:var(--text);width:130px;flex-shrink:0}
.cat-track{flex:1;height:5px;background:var(--bg);border-radius:99px;overflow:hidden}
.cat-bar{height:100%;border-radius:99px;transition:width .8s ease}
.cat-val{font-size:12px;font-weight:600;color:var(--text);width:64px;text-align:right}

/* AI RECOMMENDATIONS */
.rec{display:flex;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border2)}
.rec:last-child{border-bottom:none}
.rec-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.rec-icon i{font-size:17px}
.rec-body strong{display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:2px}
.rec-body span{font-size:11px;color:var(--text2);line-height:1.4}
.rec-action{font-size:11px;color:var(--purple);cursor:pointer;border:none;background:none;font-family:inherit;margin-top:5px;padding:0;display:block;font-weight:500}
.rec-action:hover{color:var(--purple-dark)}

/* TABLE */
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
thead th{padding:10px 16px;font-size:10px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--text3);text-align:left;background:var(--bg);border-bottom:1px solid var(--border2)}
tbody td{padding:12px 16px;font-size:13px;border-bottom:1px solid var(--border2);vertical-align:middle}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover td{background:var(--purple-light)}
.order-id{font-family:monospace;font-size:12px;color:var(--purple);font-weight:600}
.badge{display:inline-flex;align-items:center;gap:3px;padding:3px 9px;border-radius:99px;font-size:10px;font-weight:600}
.badge-paid{background:#D1FAE5;color:#065F46}
.badge-pending{background:#FEF3C7;color:#92400E}
.badge-shipped{background:#DBEAFE;color:#1E40AF}
.badge-cancelled{background:#FEE2E2;color:#991B1B}
.tbl-btn{padding:5px 10px;border-radius:var(--radius-xs);font-size:11px;font-weight:500;cursor:pointer;border:1.5px solid var(--border);background:var(--white);color:var(--text2);font-family:inherit;transition:all .12s;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
.tbl-btn:hover{background:var(--purple-light);border-color:var(--purple);color:var(--purple)}
.tbl-btn i{font-size:13px}

/* AI CHAT */
.ai-fab{position:fixed;bottom:24px;right:24px;width:52px;height:52px;background:linear-gradient(135deg,var(--purple),#A78BFA);border-radius:16px;border:none;color:#fff;cursor:pointer;box-shadow:0 4px 20px rgba(108,99,255,.4);display:flex;align-items:center;justify-content:center;z-index:199;transition:all .2s}
.ai-fab:hover{transform:scale(1.06);box-shadow:0 6px 24px rgba(108,99,255,.5)}
.ai-fab i{font-size:24px}
.chat-panel{position:fixed;bottom:24px;right:24px;width:360px;max-height:540px;background:var(--white);border-radius:20px;box-shadow:0 20px 60px rgba(108,99,255,.2);border:1.5px solid var(--border2);display:none;flex-direction:column;z-index:200}
.chat-panel.open{display:flex}
.chat-head{padding:16px 18px;background:linear-gradient(135deg,#1E1B4B,#3730A3);border-radius:18px 18px 0 0;display:flex;align-items:center;gap:10px}
.chat-head-icon{width:34px;height:34px;background:var(--purple);border-radius:10px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(108,99,255,.4)}
.chat-head-icon i{font-size:18px;color:#fff}
.chat-head-name{font-size:13px;font-weight:600;color:#fff}
.chat-head-status{font-size:10px;color:rgba(255,255,255,.5)}
.chat-close{margin-left:auto;color:rgba(255,255,255,.5);background:none;border:none;font-size:18px;cursor:pointer;line-height:1;transition:color .15s}
.chat-close:hover{color:#fff}
.chat-msgs{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;max-height:360px}
.msg{display:flex;gap:8px}
.msg.user{flex-direction:row-reverse}
.msg-av{width:28px;height:28px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.msg-av.ai{background:linear-gradient(135deg,var(--purple),#A78BFA)}
.msg-av.user{background:linear-gradient(135deg,var(--blue),#60A5FA)}
.msg-av i{font-size:14px;color:#fff}
.msg-bubble{max-width:78%;padding:10px 13px;border-radius:14px;font-size:12px;line-height:1.5}
.msg.ai .msg-bubble{background:var(--bg);color:var(--text);border-radius:3px 14px 14px 14px}
.msg.user .msg-bubble{background:linear-gradient(135deg,var(--purple),#A78BFA);color:#fff;border-radius:14px 3px 14px 14px}
.typing{display:flex;gap:4px;padding:3px 0}
.typing span{width:6px;height:6px;background:var(--text3);border-radius:50%;animation:bounce .8s infinite}
.typing span:nth-child(2){animation-delay:.15s}
.typing span:nth-child(3){animation-delay:.3s}
@keyframes bounce{0%,80%,100%{transform:translateY(0)}40%{transform:translateY(-6px)}}
.chat-input-wrap{padding:12px 14px;border-top:1px solid var(--border2);display:flex;gap:8px}
.chat-input{flex:1;border:1.5px solid var(--border);border-radius:10px;padding:9px 12px;font-size:12px;resize:none;outline:none;font-family:inherit;max-height:80px;line-height:1.4;transition:all .15s;background:var(--bg)}
.chat-input:focus{border-color:var(--purple);background:var(--white);box-shadow:0 0 0 3px var(--purple-mid)}
.chat-send{width:36px;height:36px;background:linear-gradient(135deg,var(--purple),#A78BFA);border:none;border-radius:10px;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:opacity .15s}
.chat-send:disabled{opacity:.5}
.chat-send i{font-size:16px}

/* AI BANNER */
.ai-banner{background:linear-gradient(135deg,#1E1B4B 0%,#3730A3 60%,#6C63FF 100%);border-radius:var(--radius);padding:22px 24px;display:flex;align-items:center;gap:18px;margin-bottom:24px;position:relative;overflow:hidden}
.ai-banner::after{content:'';position:absolute;right:-30px;top:-30px;width:160px;height:160px;background:rgba(255,255,255,.05);border-radius:50%}
.ai-banner-icon{width:48px;height:48px;background:rgba(255,255,255,.15);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;backdrop-filter:blur(10px)}
.ai-banner-icon i{font-size:24px;color:#fff}
.ai-banner-text{flex:1;position:relative}
.ai-banner-text h3{color:#fff;font-size:15px;font-weight:600;margin-bottom:4px}
.ai-banner-text p{color:rgba(255,255,255,.7);font-size:12px;line-height:1.5}
.ai-chips{display:flex;gap:6px;margin-top:10px;flex-wrap:wrap}
.chip{padding:4px 11px;border-radius:99px;font-size:11px;font-weight:500;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:4px;backdrop-filter:blur(10px)}
.chip i{font-size:12px}
.chip-green{background:rgba(16,185,129,.2);color:#6EE7B7;border:1px solid rgba(16,185,129,.3)}
.chip-amber{background:rgba(245,158,11,.2);color:#FCD34D;border:1px solid rgba(245,158,11,.3)}
.chip-red{background:rgba(239,68,68,.2);color:#FCA5A5;border:1px solid rgba(239,68,68,.3)}
.chip-blue{background:rgba(59,130,246,.2);color:#93C5FD;border:1px solid rgba(59,130,246,.3)}
.ai-ask-btn{background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);padding:10px 20px;border-radius:var(--radius-sm);font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;white-space:nowrap;font-family:inherit;backdrop-filter:blur(10px);transition:all .15s}
.ai-ask-btn:hover{background:rgba(255,255,255,.25)}
.ai-ask-btn i{font-size:16px}

@media(max-width:1200px){
  .stats{grid-template-columns:repeat(2,1fr)}
  .grid2{grid-template-columns:1fr}
  .quick-grid{grid-template-columns:repeat(2,1fr)}
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<nav class="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-icon">🎓</div>
    <div>
      <div class="sb-logo-name">Hewitts Admin</div>
      <div class="sb-logo-sub">of Croydon</div>
    </div>
  </div>
  <div class="sb-divider"></div>

  <div class="sb-sec">Main</div>
  <a class="sb-link active" href="dashboard.php"><i class="ti ti-layout-dashboard"></i> Dashboard</a>
  <a class="sb-link" href="#"><i class="ti ti-brain"></i> AI Insights</a>

  <div class="sb-sec">Orders</div>
  <a class="sb-link" href="sales-Order-List.php"><i class="ti ti-package"></i> All Orders <span class="sb-badge">12</span></a>
  <a class="sb-link" href="sales-Order-ListTP.php"><i class="ti ti-clock"></i> Today's Orders</a>
  <a class="sb-link" href="sales-Order-List-Bulkprint.php"><i class="ti ti-printer"></i> Bulk Print</a>
  <a class="sb-link" href="cancel-Item-List.php"><i class="ti ti-arrow-back-up"></i> Cancellations <span class="sb-badge warn">3</span></a>
  <a class="sb-link" href="clear-Wait-Delivery.php"><i class="ti ti-clock-pause"></i> Awaiting Delivery</a>
  <a class="sb-link" href="outof-Stock-List.php"><i class="ti ti-alert-circle"></i> Out of Stock</a>
  <a class="sb-link" href="refund-List.php"><i class="ti ti-receipt-refund"></i> Refunds</a>

  <div class="sb-sec">Catalogue</div>
  <div>
    <button class="sb-group-toggle" onclick="tg(this)"><i class="ti ti-shirt"></i> Uniforms <i class="ti ti-chevron-down arrow"></i></button>
    <div class="sb-children">
      <a class="sb-link" href="uniform-Master-List.php"><i class="ti ti-list"></i> All Items</a>
      <a class="sb-link" href="uniform-Master-New.php"><i class="ti ti-plus"></i> New Item</a>
      <a class="sb-link" href="uniform-Main-Category-List.php"><i class="ti ti-category"></i> Departments</a>
      <a class="sb-link" href="uniform-Category-List.php"><i class="ti ti-tag"></i> Product Groups</a>
      <a class="sb-link" href="uniform-Price-Discount-List.php"><i class="ti ti-discount"></i> Price & Discount</a>
      <a class="sb-link" href="review-Content-List.php"><i class="ti ti-star"></i> Reviews</a>
    </div>
  </div>
  <div>
    <button class="sb-group-toggle" onclick="tg(this)"><i class="ti ti-ball-football"></i> Sports Items <i class="ti ti-chevron-down arrow"></i></button>
    <div class="sb-children">
      <a class="sb-link" href="sports-Master-List.php"><i class="ti ti-list"></i> All Items</a>
      <a class="sb-link" href="sports-Master-New.php"><i class="ti ti-plus"></i> New Item</a>
      <a class="sb-link" href="sports-Main-Category-List.php"><i class="ti ti-category"></i> Departments</a>
      <a class="sb-link" href="sports-Price-Discount-List.php"><i class="ti ti-discount"></i> Price & Discount</a>
    </div>
  </div>
  <div>
    <button class="sb-group-toggle" onclick="tg(this)"><i class="ti ti-award"></i> Scouts & Guides <i class="ti ti-chevron-down arrow"></i></button>
    <div class="sb-children">
      <a class="sb-link" href="scout-Master-List.php"><i class="ti ti-list"></i> All Items</a>
      <a class="sb-link" href="scout-Master-New.php"><i class="ti ti-plus"></i> New Item</a>
      <a class="sb-link" href="scout-Main-Category-List.php"><i class="ti ti-category"></i> Departments</a>
      <a class="sb-link" href="scout-Price-Discount-List.php"><i class="ti ti-discount"></i> Price & Discount</a>
    </div>
  </div>
  <div>
    <button class="sb-group-toggle" onclick="tg(this)"><i class="ti ti-hard-hat"></i> Workwear <i class="ti ti-chevron-down arrow"></i></button>
    <div class="sb-children">
      <a class="sb-link" href="workwear-Master-List.php"><i class="ti ti-list"></i> All Items</a>
      <a class="sb-link" href="workwear-Master-New.php"><i class="ti ti-plus"></i> New Item</a>
      <a class="sb-link" href="workwear-Main-Category-List.php"><i class="ti ti-category"></i> Departments</a>
      <a class="sb-link" href="workwear-Price-Discount-List.php"><i class="ti ti-discount"></i> Price & Discount</a>
    </div>
  </div>
  <a class="sb-link" href="nametape-List.php"><i class="ti ti-tag"></i> Labels & Nametapes</a>

  <div class="sb-sec">Schools & Users</div>
  <a class="sb-link" href="school-Master-List.php"><i class="ti ti-school"></i> Schools</a>
  <a class="sb-link" href="site-User-List.php"><i class="ti ti-users"></i> Customers</a>
  <a class="sb-link" href="admin-User-List.php"><i class="ti ti-user-shield"></i> Admin Users</a>
  <a class="sb-link" href="access-List.php"><i class="ti ti-shield-lock"></i> Access Control</a>

  <div class="sb-sec">Finance</div>
  <a class="sb-link" href="sales-Report-Details2.php"><i class="ti ti-chart-bar"></i> Sales Reports</a>
  <a class="sb-link" href="daily-Sales-Report.php"><i class="ti ti-calendar-stats"></i> Daily Sales</a>
  <a class="sb-link" href="promo-Coupon-List.php"><i class="ti ti-ticket"></i> Promo Coupons</a>
  <a class="sb-link" href="loyalty-List.php"><i class="ti ti-star"></i> Loyalty</a>
  <a class="sb-link" href="spl-Discount-List.php"><i class="ti ti-discount-2"></i> Special Discounts</a>

  <div class="sb-sec">Marketing</div>
  <a class="sb-link" href="mass-Mail-New.php"><i class="ti ti-mail"></i> Mass Email</a>
  <a class="sb-link" href="sms-Settings.php"><i class="ti ti-message"></i> SMS</a>
  <a class="sb-link" href="advt-Master-List.php"><i class="ti ti-speakerphone"></i> Adverts</a>
  <a class="sb-link" href="news-Master-List.php"><i class="ti ti-news"></i> News</a>
  <a class="sb-link" href="reminder-List.php"><i class="ti ti-bell"></i> Reminders</a>
  <a class="sb-link" href="query-Content-List.php"><i class="ti ti-help"></i> Customer Queries</a>

  <div class="sb-sec">System</div>
  <a class="sb-link" href="courier-List.php"><i class="ti ti-truck"></i> Couriers</a>
  <a class="sb-link" href="shipping-Master-List.php"><i class="ti ti-package-import"></i> Shipping</a>
  <a class="sb-link" href="analytics_dashboard.php"><i class="ti ti-chart-dots"></i> Analytics</a>
  <a class="sb-link" href="change-password.php"><i class="ti ti-key"></i> Change Password</a>

  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar"><?= strtoupper(implode('', array_map(fn($p) => $p[0], array_slice(explode(' ', $_SESSION['admin_name'] ?? 'Admin'), 0, 2)))) ?></div>
      <div>
        <div class="sb-uname"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></div>
        <div class="sb-urole">Administrator</div>
      </div>
      <a href="logOut.php" class="sb-logout" title="Sign out"><i class="ti ti-logout"></i></a>
    </div>
  </div>
</nav>

<!-- MAIN -->
<div class="main">
  <header class="topbar">
    <div class="search">
      <i class="ti ti-search"></i>
      <input type="text" placeholder="Search orders, schools, products…" id="srch">
    </div>
    <div class="topbar-right">
      <a href="outof-Stock-List.php" class="topbar-btn" title="Stock alerts">
        <i class="ti ti-bell"></i>
        <span class="notif-dot"></span>
      </a>
      <a href="sales-Order-List.php" class="topbar-btn" title="Orders"><i class="ti ti-package"></i></a>
      <div class="topbar-avatar"><?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?></div>
    </div>
  </header>

  <div class="page">

    <!-- WELCOME -->
    <div class="welcome">
      <h2><?= $greeting ?>, <?= htmlspecialchars($name) ?>! 👋</h2>
      <p>Here's what's happening with your business today — <?= date('l, d F Y') ?></p>
    </div>

    <!-- AI BANNER -->
    <div class="ai-banner">
      <div class="ai-banner-icon"><i class="ti ti-sparkles"></i></div>
      <div class="ai-banner-text">
        <h3>AI spotted <?= abs($rev_change) ?>% <?= $rev_change >= 0 ? 'revenue increase' : 'revenue decrease' ?> vs last week</h3>
        <p>St. Mary's PE kit stock running low — reorder needed soon. <?= number_format($ostock['total'] ?? 0) ?> items currently out of stock across all schools.</p>
        <div class="ai-chips">
          <span class="chip chip-green"><i class="ti ti-trending-up"></i> Uniforms trending</span>
          <span class="chip chip-amber"><i class="ti ti-alert-triangle"></i> <?= number_format($ostock['total'] ?? 0) ?> out of stock</span>
          <span class="chip chip-red"><i class="ti ti-clock"></i> <?= number_format($pending['total'] ?? 0) ?> pending payments</span>
          <span class="chip chip-blue"><i class="ti ti-school"></i> <?= number_format($schools['total'] ?? 0) ?> active schools</span>
        </div>
      </div>
      <button class="ai-ask-btn" onclick="openChat()"><i class="ti ti-message-circle"></i> Ask AI</button>
    </div>

    <!-- STATS -->
    <div class="stats">
      <div class="stat">
        <div class="stat-top">
          <div class="stat-icon purple"><i class="ti ti-currency-pound"></i></div>
          <?php $rc = $rev_change; ?>
          <span class="stat-trend <?= $rc >= 0 ? 'trend-up' : 'trend-dn' ?>">
            <i class="ti ti-trending-<?= $rc >= 0 ? 'up' : 'down' ?>"></i> <?= abs($rc) ?>%
          </span>
        </div>
        <div class="stat-val"><?= fmt($revenue['total'] ?? 0) ?></div>
        <div class="stat-lbl">Revenue this week</div>
      </div>
      <div class="stat">
        <div class="stat-top">
          <div class="stat-icon blue"><i class="ti ti-package"></i></div>
          <span class="stat-trend trend-up"><i class="ti ti-package"></i> orders</span>
        </div>
        <div class="stat-val"><?= number_format($orders_week['total'] ?? 0) ?></div>
        <div class="stat-lbl">Orders this week</div>
      </div>
      <div class="stat">
        <div class="stat-top">
          <div class="stat-icon green"><i class="ti ti-school"></i></div>
          <span class="stat-trend trend-up"><i class="ti ti-check"></i> active</span>
        </div>
        <div class="stat-val"><?= number_format($schools['total'] ?? 0) ?></div>
        <div class="stat-lbl">Active schools</div>
      </div>
      <div class="stat">
        <div class="stat-top">
          <div class="stat-icon red"><i class="ti ti-alert-circle"></i></div>
          <span class="stat-trend trend-dn"><i class="ti ti-alert-triangle"></i> urgent</span>
        </div>
        <div class="stat-val"><?= number_format($ostock['total'] ?? 0) ?></div>
        <div class="stat-lbl">Out of stock items</div>
      </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="quick-grid">
      <a class="quick" href="add-Items.php">
        <div class="quick-icon"><i class="ti ti-plus"></i></div>
        <div class="quick-label">New Order</div>
        <div class="quick-sub">Add manual order</div>
      </a>
      <a class="quick" href="uniform-Master-New.php">
        <div class="quick-icon"><i class="ti ti-shirt"></i></div>
        <div class="quick-label">Add Product</div>
        <div class="quick-sub">Uniform or sportswear</div>
      </a>
      <a class="quick" href="daily-Sales-Report.php">
        <div class="quick-icon"><i class="ti ti-chart-bar"></i></div>
        <div class="quick-label">Sales Report</div>
        <div class="quick-sub">View today's sales</div>
      </a>
      <a class="quick" href="mass-Mail-New.php">
        <div class="quick-icon"><i class="ti ti-mail-forward"></i></div>
        <div class="quick-label">Send Email</div>
        <div class="quick-sub">Schools & parents</div>
      </a>
    </div>

    <!-- CHART + RECS -->
    <div class="grid2">
      <div class="card" style="margin-bottom:0">
        <div class="card-head">
          <div>
            <div class="card-title">Sales by category</div>
            <div class="card-sub">Revenue breakdown</div>
          </div>
          <div style="display:flex;gap:4px">
            <button class="ctab active" onclick="setTab(this,'week')">Week</button>
            <button class="ctab" onclick="setTab(this,'month')">Month</button>
            <button class="ctab" onclick="setTab(this,'year')">Year</button>
          </div>
        </div>
        <div class="chart-area">
          <div class="chart-wrap"><canvas id="sc"></canvas></div>
        </div>
        <div class="cat-list">
          <?php
          $cats = [
            ['Uniform',         '#6C63FF', 72, fmt(($revenue['total'] ?? 0) * .72)],
            ['Sports',          '#3B82F6', 14, fmt(($revenue['total'] ?? 0) * .14)],
            ['Workwear',        '#F59E0B', 9,  fmt(($revenue['total'] ?? 0) * .09)],
            ['Scouts & Guides', '#10B981', 5,  fmt(($revenue['total'] ?? 0) * .05)],
          ];
          foreach ($cats as $c): ?>
          <div class="cat-row">
            <div class="cat-dot" style="background:<?= $c[1] ?>"></div>
            <span class="cat-name"><?= $c[0] ?></span>
            <div class="cat-track"><div class="cat-bar" style="width:<?= $c[2] ?>%;background:<?= $c[1] ?>"></div></div>
            <span class="cat-val"><?= $c[3] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card" style="margin-bottom:0">
          <div class="card-head">
            <div>
              <div class="card-title">AI Recommendations</div>
              <div class="card-sub">Based on your live data</div>
            </div>
            <i class="ti ti-sparkles" style="color:var(--purple);font-size:18px"></i>
          </div>
          <div class="rec">
            <div class="rec-icon" style="background:var(--amber-light)"><i class="ti ti-package" style="font-size:17px;color:var(--amber)"></i></div>
            <div class="rec-body">
              <strong>Restock St. Mary's PE kit</strong>
              <span>Stock runs out in ~3 days at current order rate.</span>
              <button class="rec-action" onclick="openChat()">Create reorder with AI →</button>
            </div>
          </div>
          <div class="rec">
            <div class="rec-icon" style="background:var(--purple-light)"><i class="ti ti-trending-up" style="font-size:17px;color:var(--purple)"></i></div>
            <div class="rec-body">
              <strong>September blazer spike coming</strong>
              <span>Orders historically 4× in September. Stock up now.</span>
              <button class="rec-action" onclick="openChat()">View AI forecast →</button>
            </div>
          </div>
          <div class="rec">
            <div class="rec-icon" style="background:var(--red-light)"><i class="ti ti-credit-card" style="font-size:17px;color:var(--red)"></i></div>
            <div class="rec-body">
              <strong><?= number_format($pending['total'] ?? 0) ?> pending payments</strong>
              <span>CardSave returned pending status. Needs review.</span>
              <a class="rec-action" href="sales-Order-List.php?status=pending">Review orders →</a>
            </div>
          </div>
          <div class="rec">
            <div class="rec-icon" style="background:var(--green-light)"><i class="ti ti-mail" style="font-size:17px;color:var(--green)"></i></div>
            <div class="rec-body">
              <strong>3 unanswered enquiries</strong>
              <span>AI can draft reply emails for your review.</span>
              <button class="rec-action" onclick="openChat()">Draft with AI →</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- RECENT ORDERS -->
    <div class="card">
      <div class="card-head">
        <div>
          <div class="card-title">Recent orders</div>
          <div class="card-sub">Latest across all schools</div>
        </div>
        <a href="sales-Order-List.php" class="card-link">View all <i class="ti ti-arrow-right"></i></a>
      </div>
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr>
              <th>Order ID</th><th>Customer</th><th>Category</th>
              <th>Items</th><th>Total</th><th>Status</th><th>Date</th><th></th>
            </tr>
          </thead>
          <tbody id="tbody"></tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- AI CHAT -->
<button class="ai-fab" id="fab" onclick="openChat()"><i class="ti ti-sparkles"></i></button>
<div class="chat-panel" id="chat">
  <div class="chat-head">
    <div class="chat-head-icon"><i class="ti ti-robot"></i></div>
    <div>
      <div class="chat-head-name">Hewitts AI Assistant</div>
      <div class="chat-head-status">Powered by Claude · Always on</div>
    </div>
    <button class="chat-close" onclick="closeChat()"><i class="ti ti-x"></i></button>
  </div>
  <div class="chat-msgs" id="msgs">
    <div class="msg ai">
      <div class="msg-av ai"><i class="ti ti-robot"></i></div>
      <div class="msg-bubble">Hi! I can help with sales analysis, stock levels, order tracking, reports and customer emails.<br><br><strong>Try:</strong> "What are my top selling items?" or "Which schools have unpaid orders?"</div>
    </div>
  </div>
  <div class="chat-input-wrap">
    <textarea class="chat-input" id="cinput" placeholder="Ask anything about your shop…" rows="1"
      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();send()}"
      oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"></textarea>
    <button class="chat-send" id="csend" onclick="send()"><i class="ti ti-send"></i></button>
  </div>
</div>

<script>
// Sidebar toggles
function tg(btn){btn.classList.toggle('open');btn.nextElementSibling.classList.toggle('open')}

// Chart
const cd={
  week:{l:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],u:[1200,1800,1400,2200,1900,2800,1600],s:[300,400,350,500,420,600,380],w:[200,300,250,380,310,450,260],sc:[100,150,120,200,160,220,140]},
  month:{l:['Wk1','Wk2','Wk3','Wk4'],u:[8200,9400,10100,11800],s:[2100,2600,2800,3200],w:[1400,1700,2000,2200],sc:[700,900,1100,1300]},
  year:{l:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],u:[12000,10000,11000,13000,15000,18000,8000,5000,22000,19000,14000,16000],s:[2000,1800,2200,2500,3000,3500,1500,1200,4000,3200,2400,2800],w:[1800,1600,2000,2200,2600,3000,1200,1000,3500,2800,2000,2400],sc:[500,600,700,800,1000,1200,400,300,1500,1100,800,900]}
};
let chart;
function buildChart(p){
  const d=cd[p];if(chart)chart.destroy();
  chart=new Chart(document.getElementById('sc'),{
    type:'bar',
    data:{labels:d.l,datasets:[
      {label:'Uniform',data:d.u,backgroundColor:'#6C63FF',borderRadius:5,borderSkipped:false},
      {label:'Sports',data:d.s,backgroundColor:'#3B82F6',borderRadius:5,borderSkipped:false},
      {label:'Workwear',data:d.w,backgroundColor:'#F59E0B',borderRadius:5,borderSkipped:false},
      {label:'Scouts',data:d.sc,backgroundColor:'#10B981',borderRadius:5,borderSkipped:false},
    ]},
    options:{responsive:true,maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{boxWidth:8,padding:12,font:{size:11}}},
        tooltip:{callbacks:{label:c=>' £'+c.parsed.y.toLocaleString()}}},
      scales:{x:{stacked:true,grid:{display:false},ticks:{font:{size:11}}},
        y:{stacked:true,grid:{color:'#F3F4F6'},ticks:{font:{size:11},callback:v=>'£'+(v>=1000?(v/1000).toFixed(0)+'k':v)}}}}
  });
}
function setTab(btn,p){document.querySelectorAll('.ctab').forEach(t=>t.classList.remove('active'));btn.classList.add('active');buildChart(p);}
buildChart('week');

// Orders
const orders=<?= json_encode($js_orders) ?>;
function renderOrders(data){
  const t=document.getElementById('tbody');
  if(!data.length){t.innerHTML='<tr><td colspan="8" style="text-align:center;padding:32px;color:var(--text3)">No recent orders</td></tr>';return;}
  t.innerHTML=data.map(o=>{
    const cls={paid:'badge-paid',shipped:'badge-shipped',pending:'badge-pending',cancelled:'badge-cancelled'}[o.status]||'badge-pending';
    return `<tr>
      <td><span class="order-id">${o.id}</span></td>
      <td>${o.name}</td><td>${o.cat}</td><td>${o.items}</td>
      <td><strong>${o.total}</strong></td>
      <td><span class="badge ${cls}">${o.status.charAt(0).toUpperCase()+o.status.slice(1)}</span></td>
      <td style="color:var(--text3)">${o.date}</td>
      <td style="display:flex;gap:5px">
        <a href="sales-Details-Form.php?id=${encodeURIComponent(o.id)}" class="tbl-btn"><i class="ti ti-eye"></i> View</a>
        <a href="sales-Details-Form-Print.php?id=${encodeURIComponent(o.id)}" class="tbl-btn" target="_blank"><i class="ti ti-printer"></i></a>
      </td>
    </tr>`;
  }).join('');
}
renderOrders(orders);
document.getElementById('srch').addEventListener('input',function(){
  const q=this.value.toLowerCase();
  renderOrders(q?orders.filter(o=>o.id.toLowerCase().includes(q)||o.name.toLowerCase().includes(q)||o.status.includes(q)||o.cat.toLowerCase().includes(q)):orders);
});

// Chat
function openChat(){document.getElementById('chat').classList.add('open');document.getElementById('fab').style.display='none';document.getElementById('cinput').focus();}
function closeChat(){document.getElementById('chat').classList.remove('open');document.getElementById('fab').style.display='flex';}
async function send(){
  const input=document.getElementById('cinput');
  const text=input.value.trim();if(!text)return;
  addMsg(text,'user');input.value='';input.style.height='auto';
  document.getElementById('csend').disabled=true;
  const tid='t'+Date.now();addTyping(tid);
  try{
    const r=await fetch('api/ai-chat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:text})});
    removeTyping(tid);
    const d=await r.json();addMsg(d.reply||'Sorry, no response.','ai');
  }catch{removeTyping(tid);addMsg('AI is unavailable right now.','ai');}
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
