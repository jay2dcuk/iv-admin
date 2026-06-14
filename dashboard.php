<?php require_once 'includes/config.php'; require_auth(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — <?= APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:#1E3A5F;--navy2:#162d4a;
  --blue:#2563EB;--blue-light:#EFF6FF;--blue-mid:#BFDBFE;
  --green:#059669;--green-light:#ECFDF5;
  --amber:#D97706;--amber-light:#FFFBEB;
  --red:#DC2626;--red-light:#FEF2F2;
  --purple:#7C3AED;--purple-light:#F5F3FF;
  --text:#111827;--muted:#6B7280;--border:#E5E7EB;
  --bg:#F3F4F6;--white:#FFFFFF;
  --radius:12px;--radius-sm:8px;
  --shadow:0 1px 8px rgba(0,0,0,.06);
}
body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh}

/* SIDEBAR */
.sidebar{width:240px;background:var(--navy);min-height:100vh;display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;overflow-y:auto;z-index:100}
.sb-logo{padding:20px 18px;border-bottom:1px solid rgba(255,255,255,.08)}
.sb-logo-title{font-size:15px;font-weight:700;color:#fff;display:flex;align-items:center;gap:10px}
.sb-logo-icon{width:36px;height:36px;background:rgba(255,255,255,.12);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.sb-logo-sub{font-size:11px;color:rgba(255,255,255,.4);margin-top:3px;padding-left:46px}
.sb-sec{padding:18px 14px 5px;font-size:10px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:rgba(255,255,255,.3)}
.sb-link{display:flex;align-items:center;gap:10px;padding:9px 14px;margin:1px 8px;border-radius:var(--radius-sm);color:rgba(255,255,255,.6);text-decoration:none;font-size:13px;font-weight:500;transition:all .12s;cursor:pointer;border:none;background:none;width:calc(100% - 16px);text-align:left}
.sb-link i{font-size:17px;flex-shrink:0;width:20px;text-align:center}
.sb-link:hover{background:rgba(255,255,255,.08);color:#fff}
.sb-link.active{background:var(--blue);color:#fff}
.sb-badge{margin-left:auto;background:var(--red);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px;min-width:18px;text-align:center}
.sb-badge.warn{background:var(--amber)}
.sb-footer{margin-top:auto;padding:14px;border-top:1px solid rgba(255,255,255,.08)}
.sb-user{display:flex;align-items:center;gap:10px}
.sb-avatar{width:32px;height:32px;background:rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0}
.sb-name{font-size:12px;font-weight:600;color:#fff}
.sb-role{font-size:10px;color:rgba(255,255,255,.4)}
.sb-logout{margin-left:auto;color:rgba(255,255,255,.4);font-size:16px;cursor:pointer;text-decoration:none}

/* MAIN */
.main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-width:0}

/* TOPBAR */
.topbar{background:var(--white);border-bottom:1px solid var(--border);height:60px;padding:0 28px;display:flex;align-items:center;gap:14px;position:sticky;top:0;z-index:50}
.topbar-title{font-size:17px;font-weight:700;color:var(--text)}
.topbar-sub{font-size:12px;color:var(--muted);margin-top:1px}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:10px}
.search{display:flex;align-items:center;gap:8px;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:8px 12px;width:240px;transition:border-color .15s}
.search:focus-within{border-color:var(--blue)}
.search input{border:none;background:none;outline:none;font-size:13px;width:100%;font-family:inherit;color:var(--text)}
.search i{color:var(--muted);font-size:16px}
.icon-btn{width:36px;height:36px;border-radius:var(--radius-sm);border:1.5px solid var(--border);background:var(--white);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:17px;text-decoration:none;transition:all .12s;position:relative}
.icon-btn:hover{border-color:var(--blue);color:var(--blue)}
.notif-dot{position:absolute;top:5px;right:5px;width:7px;height:7px;background:var(--red);border-radius:50%;border:2px solid white}

/* PAGE */
.page{padding:24px 28px;flex:1}

/* AI BANNER */
.ai-banner{background:var(--navy);border-radius:var(--radius);padding:20px 24px;display:flex;align-items:center;gap:16px;margin-bottom:24px}
.ai-banner-icon{width:44px;height:44px;background:var(--purple);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ai-banner-icon i{font-size:22px;color:#fff}
.ai-banner-text{flex:1}
.ai-banner-text h3{font-size:14px;font-weight:600;color:#fff;margin-bottom:4px}
.ai-banner-text p{font-size:12px;color:rgba(255,255,255,.6);line-height:1.5}
.ai-chips{display:flex;gap:6px;margin-top:10px;flex-wrap:wrap}
.chip{padding:3px 10px;border-radius:99px;font-size:11px;font-weight:500;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:4px}
.chip-green{background:rgba(5,150,105,.2);color:#34D399}
.chip-amber{background:rgba(217,119,6,.2);color:#FCD34D}
.chip-red{background:rgba(220,38,38,.2);color:#FCA5A5}
.chip-blue{background:rgba(37,99,235,.2);color:#93C5FD}
.ai-btn{background:var(--purple);color:#fff;border:none;padding:10px 18px;border-radius:var(--radius-sm);font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;white-space:nowrap;font-family:inherit}
.ai-btn:hover{background:#6d28d9}
.ai-btn i{font-size:15px}

/* STATS */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.stat{background:var(--white);border-radius:var(--radius);padding:18px;border:1px solid var(--border);box-shadow:var(--shadow)}
.stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.stat-icon{width:38px;height:38px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center}
.stat-icon i{font-size:20px}
.stat-icon.blue{background:var(--blue-light);color:var(--blue)}
.stat-icon.green{background:var(--green-light);color:var(--green)}
.stat-icon.amber{background:var(--amber-light);color:var(--amber)}
.stat-icon.red{background:var(--red-light);color:var(--red)}
.stat-trend{font-size:11px;font-weight:600;padding:2px 8px;border-radius:99px;display:flex;align-items:center;gap:3px}
.trend-up{background:var(--green-light);color:var(--green)}
.trend-dn{background:var(--red-light);color:var(--red)}
.stat-val{font-size:26px;font-weight:700;color:var(--text);letter-spacing:-.5px}
.stat-lbl{font-size:12px;color:var(--muted);margin-top:2px}

/* QUICK ACTIONS */
.quick-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px}
.quick{background:var(--white);border:1px solid var(--border);border-radius:var(--radius);padding:16px 14px;text-align:center;cursor:pointer;transition:all .15s;text-decoration:none;display:block}
.quick:hover{border-color:var(--blue);transform:translateY(-1px);box-shadow:0 4px 12px rgba(37,99,235,.1)}
.quick i{font-size:24px;color:var(--muted);display:block;margin-bottom:8px}
.quick:hover i{color:var(--blue)}
.quick-label{font-size:12px;font-weight:600;color:var(--text)}
.quick-sub{font-size:11px;color:var(--muted);margin-top:2px}

/* GRID */
.grid2{display:grid;grid-template-columns:1fr 320px;gap:16px;margin-bottom:16px}

/* CARDS */
.card{background:var(--white);border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow);overflow:hidden}
.card-head{padding:16px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:14px;font-weight:600;color:var(--text)}
.card-sub{font-size:11px;color:var(--muted);margin-top:1px}
.card-link{font-size:12px;color:var(--blue);font-weight:500;cursor:pointer;border:none;background:none;font-family:inherit;text-decoration:none}

/* CHART */
.chart-area{padding:16px 18px}
.chart-tabs{display:flex;gap:4px;margin-bottom:14px;background:var(--bg);border-radius:var(--radius-sm);padding:3px;width:fit-content}
.ctab{padding:5px 12px;border-radius:6px;font-size:11px;font-weight:500;cursor:pointer;color:var(--muted);border:none;background:none;font-family:inherit}
.ctab.active{background:var(--white);color:var(--text);box-shadow:0 1px 4px rgba(0,0,0,.08)}
.chart-wrap{height:200px;position:relative}

/* CATEGORIES */
.cat-list{padding:0 18px 16px}
.cat-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)}
.cat-row:last-child{border-bottom:none}
.cat-dot{width:8px;height:8px;border-radius:2px;flex-shrink:0}
.cat-name{font-size:12px;color:var(--text);width:120px;flex-shrink:0}
.cat-track{flex:1;height:5px;background:var(--bg);border-radius:99px;overflow:hidden}
.cat-bar{height:100%;border-radius:99px}
.cat-val{font-size:12px;font-weight:600;color:var(--text);width:60px;text-align:right}

/* AI RECS */
.rec-list{padding:0}
.rec{display:flex;gap:10px;padding:12px 18px;border-bottom:1px solid var(--border)}
.rec:last-child{border-bottom:none}
.rec-icon{width:32px;height:32px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.rec-icon i{font-size:16px}
.rec-body strong{display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:2px}
.rec-body span{font-size:11px;color:var(--muted);line-height:1.4}
.rec-action{font-size:11px;color:var(--blue);cursor:pointer;border:none;background:none;font-family:inherit;margin-top:4px;padding:0;display:block}

/* TABLE */
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
thead th{padding:10px 16px;font-size:10px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);text-align:left;background:var(--bg);border-bottom:1px solid var(--border)}
tbody td{padding:12px 16px;font-size:13px;border-bottom:1px solid var(--border);vertical-align:middle}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover td{background:var(--blue-light)}
.order-id{font-family:monospace;font-size:12px;color:var(--blue);font-weight:600}
.badge{display:inline-flex;align-items:center;gap:3px;padding:3px 9px;border-radius:99px;font-size:10px;font-weight:600}
.badge::before{content:'●';font-size:7px}
.badge-paid{background:var(--green-light);color:var(--green)}
.badge-pending{background:var(--amber-light);color:var(--amber)}
.badge-shipped{background:var(--blue-light);color:var(--blue)}
.badge-cancelled{background:var(--red-light);color:var(--red)}
.tbl-btn{padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500;cursor:pointer;border:1px solid var(--border);background:var(--white);color:var(--muted);font-family:inherit;transition:all .12s}
.tbl-btn:hover{background:var(--blue);border-color:var(--blue);color:#fff}

/* AI CHAT */
.ai-fab{position:fixed;bottom:24px;right:24px;width:52px;height:52px;background:var(--purple);border-radius:14px;border:none;color:#fff;font-size:22px;cursor:pointer;box-shadow:0 4px 16px rgba(124,58,237,.4);display:flex;align-items:center;justify-content:center;z-index:199;transition:all .2s}
.ai-fab:hover{transform:scale(1.06)}
.ai-fab i{font-size:24px}
.chat-panel{position:fixed;bottom:24px;right:24px;width:360px;max-height:540px;background:var(--white);border-radius:16px;box-shadow:0 16px 48px rgba(0,0,0,.16);border:1px solid var(--border);display:flex;flex-direction:column;z-index:200;display:none}
.chat-panel.open{display:flex}
.chat-head{padding:14px 16px;background:var(--navy);border-radius:16px 16px 0 0;display:flex;align-items:center;gap:10px}
.chat-head-icon{width:32px;height:32px;background:var(--purple);border-radius:9px;display:flex;align-items:center;justify-content:center}
.chat-head-icon i{font-size:17px;color:#fff}
.chat-head-name{font-size:13px;font-weight:600;color:#fff}
.chat-head-status{font-size:10px;color:rgba(255,255,255,.45)}
.chat-close{margin-left:auto;color:rgba(255,255,255,.5);background:none;border:none;font-size:18px;cursor:pointer;line-height:1}
.chat-msgs{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px;max-height:340px}
.msg{display:flex;gap:8px}
.msg.user{flex-direction:row-reverse}
.msg-av{width:26px;height:26px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0}
.msg-av.ai{background:var(--purple)}
.msg-av.ai i{font-size:14px;color:#fff}
.msg-av.user{background:var(--blue)}
.msg-av.user i{font-size:14px;color:#fff}
.msg-bubble{max-width:78%;padding:9px 12px;border-radius:12px;font-size:12px;line-height:1.5}
.msg.ai .msg-bubble{background:var(--bg);color:var(--text);border-radius:3px 12px 12px 12px}
.msg.user .msg-bubble{background:var(--blue);color:#fff;border-radius:12px 3px 12px 12px}
.typing{display:flex;gap:3px;padding:4px 0}
.typing span{width:5px;height:5px;background:var(--muted);border-radius:50%;animation:bounce .8s infinite}
.typing span:nth-child(2){animation-delay:.15s}
.typing span:nth-child(3){animation-delay:.3s}
@keyframes bounce{0%,80%,100%{transform:translateY(0)}40%{transform:translateY(-5px)}}
.chat-input-wrap{padding:10px 12px;border-top:1px solid var(--border);display:flex;gap:8px}
.chat-input{flex:1;border:1.5px solid var(--border);border-radius:9px;padding:8px 11px;font-size:12px;resize:none;outline:none;font-family:inherit;max-height:80px;line-height:1.4;transition:border-color .15s}
.chat-input:focus{border-color:var(--blue)}
.chat-send{width:34px;height:34px;background:var(--blue);border:none;border-radius:9px;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.chat-send:disabled{opacity:.5}
.chat-send i{font-size:16px}

@media(max-width:1100px){.stats{grid-template-columns:repeat(2,1fr)}.grid2{grid-template-columns:1fr}.quick-grid{grid-template-columns:repeat(2,1fr)}}
</style>
</head>
<body>

<!-- SIDEBAR -->
<nav class="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-title">
      <div class="sb-logo-icon">🎓</div>
      Hewitts Admin
    </div>
    <div class="sb-logo-sub">of Croydon</div>
  </div>

  <div class="sb-sec">Overview</div>
  <a class="sb-link active" href="dashboard.php"><i class="ti ti-layout-dashboard"></i> Dashboard</a>
  <a class="sb-link" href="#"><i class="ti ti-brain"></i> AI Insights</a>

  <div class="sb-sec">Orders</div>
  <a class="sb-link" href="sales-Order-List.php"><i class="ti ti-package"></i> All Orders <span class="sb-badge">12</span></a>
  <a class="sb-link" href="sales-Order-List.php"><i class="ti ti-printer"></i> Bulk Print</a>
  <a class="sb-link" href="cancel-Item-List.php"><i class="ti ti-arrow-back-up"></i> Cancellations <span class="sb-badge warn">3</span></a>
  <a class="sb-link" href="clear-Wait-Delivery.php"><i class="ti ti-clock-pause"></i> Awaiting Delivery</a>
  <a class="sb-link" href="outof-Stock-List.php"><i class="ti ti-alert-circle"></i> Out of Stock</a>
  <a class="sb-link" href="refund-List.php"><i class="ti ti-receipt-refund"></i> Refunds</a>
  <a class="sb-link" href="exchange-Form.php"><i class="ti ti-arrows-exchange"></i> Exchanges</a>

  <div class="sb-sec">Catalogue</div>
  <a class="sb-link" href="uniform-Master-List.php"><i class="ti ti-shirt"></i> Uniforms</a>
  <a class="sb-link" href="sports-Master-List.php"><i class="ti ti-ball-football"></i> Sports Items</a>
  <a class="sb-link" href="scout-Master-List.php"><i class="ti ti-award"></i> Scouts &amp; Guides</a>
  <a class="sb-link" href="workwear-Master-List.php"><i class="ti ti-hard-hat"></i> Workwear</a>
  <a class="sb-link" href="nametape-List.php"><i class="ti ti-tag"></i> Labels &amp; Nametapes</a>

  <div class="sb-sec">Schools &amp; Users</div>
  <a class="sb-link" href="school-Master-List.php"><i class="ti ti-school"></i> Schools</a>
  <a class="sb-link" href="site-User-List.php"><i class="ti ti-users"></i> Customers</a>
  <a class="sb-link" href="admin-User-List.php"><i class="ti ti-user-shield"></i> Admin Users</a>
  <a class="sb-link" href="access-List.php"><i class="ti ti-shield-lock"></i> Access Control</a>

  <div class="sb-sec">Finance</div>
  <a class="sb-link" href="sales-Report-Details.php"><i class="ti ti-chart-bar"></i> Sales Reports</a>
  <a class="sb-link" href="daily-Sales-Report.php"><i class="ti ti-calendar-stats"></i> Daily Sales</a>
  <a class="sb-link" href="promo-Coupon-List.php"><i class="ti ti-ticket"></i> Promo Coupons</a>
  <a class="sb-link" href="loyalty-List.php"><i class="ti ti-star"></i> Loyalty</a>
  <a class="sb-link" href="cc-Charges-New.php"><i class="ti ti-credit-card"></i> CC Charges</a>

  <div class="sb-sec">Marketing</div>
  <a class="sb-link" href="mass-Mail-New.php"><i class="ti ti-mail"></i> Mass Email</a>
  <a class="sb-link" href="sms-Settings.php"><i class="ti ti-message"></i> SMS</a>
  <a class="sb-link" href="advt-Master-List.php"><i class="ti ti-speakerphone"></i> Adverts</a>
  <a class="sb-link" href="news-Master-List.php"><i class="ti ti-news"></i> News</a>
  <a class="sb-link" href="reminder-List.php"><i class="ti ti-bell"></i> Reminders</a>

  <div class="sb-sec">System</div>
  <a class="sb-link" href="courier-List.php"><i class="ti ti-truck"></i> Couriers</a>
  <a class="sb-link" href="shipping-Master-List.php"><i class="ti ti-package-import"></i> Shipping</a>
  <a class="sb-link" href="module-Master-List.php"><i class="ti ti-puzzle"></i> Modules</a>
  <a class="sb-link" href="addcron.php"><i class="ti ti-clock"></i> Cron Jobs</a>
  <a class="sb-link" href="change-password.php"><i class="ti ti-key"></i> Change Password</a>

  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar"><?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 2)) ?></div>
      <div>
        <div class="sb-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></div>
        <div class="sb-role">Administrator</div>
      </div>
      <a href="logOut.php" class="sb-logout" title="Log out"><i class="ti ti-logout"></i></a>
    </div>
  </div>
</nav>

<!-- MAIN -->
<div class="main">
  <header class="topbar">
    <div>
      <div class="topbar-title">Dashboard</div>
      <div class="topbar-sub"><?= date('l, d F Y') ?></div>
    </div>
    <div class="topbar-right">
      <div class="search">
        <i class="ti ti-search"></i>
        <input type="text" placeholder="Search orders, schools, products…" id="srch">
      </div>
      <a href="outof-Stock-List.php" class="icon-btn" title="Stock alerts">
        <i class="ti ti-alert-circle"></i>
        <span class="notif-dot"></span>
      </a>
      <a href="sales-Order-List.php" class="icon-btn" title="New orders">
        <i class="ti ti-bell"></i>
      </a>
    </div>
  </header>

  <div class="page">

    <!-- AI BANNER -->
    <div class="ai-banner">
      <div class="ai-banner-icon"><i class="ti ti-robot"></i></div>
      <div class="ai-banner-text">
        <h3>Good <?= (date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening')) ?>, <?= htmlspecialchars(explode(' ', $_SESSION['admin_name'] ?? 'there')[0]) ?>!</h3>
        <p>Revenue is up 18% vs last week. St. Mary's PE kit stock running low — reorder needed within 3 days. 3 pending payments need attention.</p>
        <div class="ai-chips">
          <span class="chip chip-green"><i class="ti ti-trending-up"></i> Uniforms up 18%</span>
          <span class="chip chip-amber"><i class="ti ti-alert-triangle"></i> 6 low stock items</span>
          <span class="chip chip-red"><i class="ti ti-clock"></i> 3 pending payments</span>
          <span class="chip chip-blue"><i class="ti ti-school"></i> 38 active schools</span>
        </div>
      </div>
      <button class="ai-btn" onclick="openChat()">
        <i class="ti ti-sparkles"></i> Ask AI
      </button>
    </div>

    <!-- STATS -->
    <div class="stats">
      <div class="stat">
        <div class="stat-top">
          <div class="stat-icon blue"><i class="ti ti-currency-pound"></i></div>
          <span class="stat-trend trend-up"><i class="ti ti-trending-up"></i> +18%</span>
        </div>
        <div class="stat-val">£14,280</div>
        <div class="stat-lbl">Revenue this week</div>
      </div>
      <div class="stat">
        <div class="stat-top">
          <div class="stat-icon green"><i class="ti ti-package"></i></div>
          <span class="stat-trend trend-up"><i class="ti ti-trending-up"></i> +9%</span>
        </div>
        <div class="stat-val">247</div>
        <div class="stat-lbl">Orders this week</div>
      </div>
      <div class="stat">
        <div class="stat-top">
          <div class="stat-icon amber"><i class="ti ti-school"></i></div>
          <span class="stat-trend trend-up"><i class="ti ti-trending-up"></i> +3</span>
        </div>
        <div class="stat-val">38</div>
        <div class="stat-lbl">Active schools</div>
      </div>
      <div class="stat">
        <div class="stat-top">
          <div class="stat-icon red"><i class="ti ti-alert-circle"></i></div>
          <span class="stat-trend trend-dn"><i class="ti ti-alert-triangle"></i> urgent</span>
        </div>
        <div class="stat-val">6</div>
        <div class="stat-lbl">Low stock items</div>
      </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="quick-grid">
      <a class="quick" href="add-Items.php">
        <i class="ti ti-plus"></i>
        <div class="quick-label">New Order</div>
        <div class="quick-sub">Add manual order</div>
      </a>
      <a class="quick" href="uniform-Master-New.php">
        <i class="ti ti-shirt"></i>
        <div class="quick-label">Add Product</div>
        <div class="quick-sub">Uniform or sportswear</div>
      </a>
      <a class="quick" href="daily-Sales-Report.php">
        <i class="ti ti-chart-bar"></i>
        <div class="quick-label">Sales Report</div>
        <div class="quick-sub">View today's sales</div>
      </a>
      <a class="quick" href="mass-Mail-New.php">
        <i class="ti ti-mail-forward"></i>
        <div class="quick-label">Send Email</div>
        <div class="quick-sub">Schools &amp; parents</div>
      </a>
    </div>

    <!-- MAIN GRID -->
    <div class="grid2">

      <!-- Sales Chart -->
      <div class="card">
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
          <div class="cat-list" style="margin-top:16px;padding:0">
            <div class="cat-row"><div class="cat-dot" style="background:#2563EB"></div><span class="cat-name">Uniform</span><div class="cat-track"><div class="cat-bar" style="width:72%;background:#2563EB"></div></div><span class="cat-val">£10,282</span></div>
            <div class="cat-row"><div class="cat-dot" style="background:#059669"></div><span class="cat-name">Sports</span><div class="cat-track"><div class="cat-bar" style="width:14%;background:#059669"></div></div><span class="cat-val">£1,999</span></div>
            <div class="cat-row"><div class="cat-dot" style="background:#D97706"></div><span class="cat-name">Workwear</span><div class="cat-track"><div class="cat-bar" style="width:9%;background:#D97706"></div></div><span class="cat-val">£1,285</span></div>
            <div class="cat-row"><div class="cat-dot" style="background:#7C3AED"></div><span class="cat-name">Scouts &amp; Guides</span><div class="cat-track"><div class="cat-bar" style="width:5%;background:#7C3AED"></div></div><span class="cat-val">£714</span></div>
          </div>
        </div>
      </div>

      <!-- AI Recommendations -->
      <div class="card">
        <div class="card-head">
          <div>
            <div class="card-title">AI Recommendations</div>
            <div class="card-sub">Based on your data</div>
          </div>
        </div>
        <div class="rec-list">
          <div class="rec">
            <div class="rec-icon" style="background:var(--amber-light)"><i class="ti ti-package" style="font-size:16px;color:var(--amber)"></i></div>
            <div class="rec-body">
              <strong>Restock St. Mary's PE kit</strong>
              <span>Stock runs out in ~3 days at current order rate.</span>
              <button class="rec-action" onclick="openChat()">Create reorder with AI →</button>
            </div>
          </div>
          <div class="rec">
            <div class="rec-icon" style="background:var(--blue-light)"><i class="ti ti-trending-up" style="font-size:16px;color:var(--blue)"></i></div>
            <div class="rec-body">
              <strong>September blazer spike coming</strong>
              <span>Orders historically 4× in Sept. Stock up now.</span>
              <button class="rec-action" onclick="openChat()">View AI forecast →</button>
            </div>
          </div>
          <div class="rec">
            <div class="rec-icon" style="background:var(--red-light)"><i class="ti ti-credit-card" style="font-size:16px;color:var(--red)"></i></div>
            <div class="rec-body">
              <strong>3 pending payments</strong>
              <span>CardSave returned pending status on 3 orders.</span>
              <a class="rec-action" href="sales-Order-List.php">Review orders →</a>
            </div>
          </div>
          <div class="rec">
            <div class="rec-icon" style="background:var(--green-light)"><i class="ti ti-mail" style="font-size:16px;color:var(--green)"></i></div>
            <div class="rec-body">
              <strong>3 unanswered enquiries</strong>
              <span>AI can draft reply emails for your review.</span>
              <button class="rec-action" onclick="openChat()">Draft with AI →</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ORDERS TABLE -->
    <div class="card">
      <div class="card-head">
        <div>
          <div class="card-title">Recent orders</div>
          <div class="card-sub">Latest across all schools</div>
        </div>
        <a href="sales-Order-List.php" class="card-link">View all →</a>
      </div>
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr>
              <th>Order ID</th>
              <th>School</th>
              <th>Category</th>
              <th>Items</th>
              <th>Total</th>
              <th>Status</th>
              <th>Date</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="tbody">
          </tbody>
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
      <div class="msg-bubble">Hi! I can help with sales analysis, stock levels, order tracking, report generation and customer emails.<br><br><strong>Try asking:</strong><br>• What are my top selling items?<br>• Which schools have unpaid orders?<br>• Generate a weekly summary<br>• What needs restocking urgently?</div>
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
// Chart
const cd={
  week:{l:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],u:[1200,1800,1400,2200,1900,2800,1600],s:[300,400,350,500,420,600,380],w:[200,300,250,380,310,450,260],sc:[100,150,120,200,160,220,140]},
  month:{l:['Wk1','Wk2','Wk3','Wk4'],u:[8200,9400,10100,11800],s:[2100,2600,2800,3200],w:[1400,1700,2000,2200],sc:[700,900,1100,1300]},
  year:{l:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],u:[12000,10000,11000,13000,15000,18000,8000,5000,22000,19000,14000,16000],s:[2000,1800,2200,2500,3000,3500,1500,1200,4000,3200,2400,2800],w:[1800,1600,2000,2200,2600,3000,1200,1000,3500,2800,2000,2400],sc:[500,600,700,800,1000,1200,400,300,1500,1100,800,900]}
};
let chart;
function buildChart(p){
  const d=cd[p];
  if(chart)chart.destroy();
  chart=new Chart(document.getElementById('sc'),{
    type:'bar',
    data:{labels:d.l,datasets:[
      {label:'Uniform',data:d.u,backgroundColor:'#2563EB',borderRadius:4,borderSkipped:false},
      {label:'Sports',data:d.s,backgroundColor:'#059669',borderRadius:4,borderSkipped:false},
      {label:'Workwear',data:d.w,backgroundColor:'#D97706',borderRadius:4,borderSkipped:false},
      {label:'Scouts',data:d.sc,backgroundColor:'#7C3AED',borderRadius:4,borderSkipped:false},
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

// Orders table
const orders=[
  {id:'HW-8821',school:"St. Mary's Primary",cat:'Uniform',items:5,total:'£142.50',status:'paid',date:'13 Jun'},
  {id:'HW-8820',school:'Croydon High',cat:'Sports',items:2,total:'£58.00',status:'shipped',date:'13 Jun'},
  {id:'HW-8819',school:"St. Anne's Academy",cat:'Uniform',items:8,total:'£231.00',status:'pending',date:'12 Jun'},
  {id:'HW-8818',school:'Whitgift School',cat:'Workwear',items:1,total:'£24.99',status:'paid',date:'12 Jun'},
  {id:'HW-8817',school:'Trinity School',cat:'Scouts & Guides',items:3,total:'£89.00',status:'cancelled',date:'11 Jun'},
  {id:'HW-8816',school:"St. Joseph's",cat:'Uniform',items:6,total:'£175.00',status:'shipped',date:'11 Jun'},
  {id:'HW-8815',school:'Archbishop Tenison',cat:'Uniform',items:4,total:'£112.00',status:'paid',date:'10 Jun'},
  {id:'HW-8814',school:'Riddlesdown College',cat:'Sports',items:7,total:'£203.50',status:'pending',date:'10 Jun'},
];
function renderOrders(data){
  document.getElementById('tbody').innerHTML=data.map(o=>`
    <tr>
      <td><span class="order-id">#${o.id}</span></td>
      <td>${o.school}</td>
      <td>${o.cat}</td>
      <td>${o.items}</td>
      <td><strong>${o.total}</strong></td>
      <td><span class="badge badge-${o.status}">${o.status.charAt(0).toUpperCase()+o.status.slice(1)}</span></td>
      <td style="color:var(--muted)">${o.date}</td>
      <td style="display:flex;gap:6px">
        <button class="tbl-btn" onclick="window.location='sales-Details-Form.php?id=${o.id}'">View</button>
        <button class="tbl-btn">Print</button>
      </td>
    </tr>`).join('');
}
renderOrders(orders);

document.getElementById('srch').addEventListener('input',function(){
  const q=this.value.toLowerCase();
  renderOrders(q?orders.filter(o=>o.id.toLowerCase().includes(q)||o.school.toLowerCase().includes(q)||o.status.includes(q)||o.cat.toLowerCase().includes(q)):orders);
});

// Chat
function openChat(){document.getElementById('chat').classList.add('open');document.getElementById('fab').style.display='none';document.getElementById('cinput').focus();}
function closeChat(){document.getElementById('chat').classList.remove('open');document.getElementById('fab').style.display='flex';}

async function send(){
  const input=document.getElementById('cinput');
  const text=input.value.trim();
  if(!text)return;
  addMsg(text,'user');
  input.value='';input.style.height='auto';
  document.getElementById('csend').disabled=true;
  const tid='t'+Date.now();addTyping(tid);
  try{
    const r=await fetch('api/ai-chat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:text})});
    removeTyping(tid);
    if(r.ok){const d=await r.json();addMsg(d.reply||'Sorry, no response.','ai');}
    else{addMsg(demo(text),'ai');}
  }catch{removeTyping(tid);addMsg(demo(text),'ai');}
  document.getElementById('csend').disabled=false;
}

function demo(m){
  m=m.toLowerCase();
  if(m.includes('top sell')||m.includes('best'))return '📊 Top sellers this week:\n\n1. St. Anne\'s Blazer — 48 units (£1,344)\n2. Croydon High PE Kit — 32 units (£896)\n3. High-Vis Work Vest — 27 units (£351)\n\nUniforms account for 72% of revenue.';
  if(m.includes('stock')||m.includes('restock'))return '⚠️ Items needing urgent restock:\n\n• St. Mary\'s PE Shorts (10-11y) — 2 left\n• Croydon High Tie — 4 left\n• Scout Badge Set A — 3 left\n• Work Wear Polo (L) — 5 left\n\nShall I draft a purchase order?';
  if(m.includes('report')||m.includes('summary'))return '📈 Week ending 13 Jun:\n\nRevenue: £14,280 (+18%)\nOrders: 247 (+9%)\nNew schools: 3\nAvg order: £57.81\n\nTop school: St. Anne\'s Academy (£2,100)\n\nWant me to email this report?';
  if(m.includes('unpaid')||m.includes('pending'))return '💳 Pending payments:\n\n#HW-8819 — St. Anne\'s — £231.00 (2 days)\n#HW-8814 — Riddlesdown — £203.50 (today)\n#HW-8811 — St. Joseph\'s — £89.00 (today)\n\nShall I send payment reminder emails?';
  return '🤖 I can help with sales analytics, stock management, order tracking, reports and customer emails. What would you like to know?';
}

function addMsg(text,role){
  const msgs=document.getElementById('msgs');
  const d=document.createElement('div');
  d.className=`msg ${role}`;
  d.innerHTML=`<div class="msg-av ${role}"><i class="ti ti-${role==='ai'?'robot':'user'}"></i></div><div class="msg-bubble">${text.replace(/\n/g,'<br>')}</div>`;
  msgs.appendChild(d);msgs.scrollTop=msgs.scrollHeight;
}
function addTyping(id){
  const msgs=document.getElementById('msgs');
  const d=document.createElement('div');d.className='msg ai';d.id=id;
  d.innerHTML=`<div class="msg-av ai"><i class="ti ti-robot"></i></div><div class="msg-bubble"><div class="typing"><span></span><span></span><span></span></div></div>`;
  msgs.appendChild(d);msgs.scrollTop=msgs.scrollHeight;
}
function removeTyping(id){const e=document.getElementById(id);if(e)e.remove();}
</script>
</body>
</html>
