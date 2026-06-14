<?php
require_once __DIR__ . '/db.php';

// Revenue this week (paid orders: pg_Status='0' and pg_Authcode != '')
$revenue = DB::one("
    SELECT COALESCE(SUM(pg_Amount), 0) as total
    FROM sales_master
    WHERE pg_Status = '0' AND pg_Authcode != ''
    AND dt >= DATE_SUB(NOW(), INTERVAL 7 DAY)
") ?? ['total' => 0];

// Orders this week
$orders_week = DB::one("
    SELECT COUNT(*) as total
    FROM sales_master
    WHERE dt >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    AND pg_Authcode != '' AND order_Id != ''
") ?? ['total' => 0];

// Last week revenue for comparison
$last_week = DB::one("
    SELECT COALESCE(SUM(pg_Amount), 0) as total
    FROM sales_master
    WHERE pg_Status = '0' AND pg_Authcode != ''
    AND dt >= DATE_SUB(NOW(), INTERVAL 14 DAY)
    AND dt < DATE_SUB(NOW(), INTERVAL 7 DAY)
") ?? ['total' => 0];

// Active schools
$schools = DB::one("SELECT COUNT(*) as total FROM scholl_master WHERE scholl_Status = 1") ?? ['total' => 0];

// Pending orders (not yet processed)
$pending = DB::one("
    SELECT COUNT(*) as total FROM sales_master
    WHERE pg_Status = '0' AND pg_Authcode != ''
    AND oStatus = '' AND order_Id != ''
") ?? ['total' => 0];

// Out of stock items
$ostock = DB::one("
    SELECT COUNT(DISTINCT id) as total FROM sales_details
    WHERE oStock = 1
") ?? ['total' => 0];

// Recent orders
$recent_orders = DB::query("
    SELECT
        m.order_Id,
        m.fName, m.lName,
        m.pg_Amount,
        m.pg_Status,
        m.oStatus,
        m.dt,
        m.approve_Status,
        COUNT(d.id) as item_count,
        GROUP_CONCAT(DISTINCT CASE d.item_Category
            WHEN 1 THEN 'Uniform'
            WHEN 2 THEN 'Sports'
            WHEN 3 THEN 'Scouts & Guides'
            WHEN 9 THEN 'Workwear'
            ELSE 'Other'
        END SEPARATOR ', ') as categories
    FROM sales_master m
    LEFT JOIN sales_details d ON d.order_Id = m.order_Id
    WHERE m.pg_Authcode != '' AND m.order_Id != ''
    GROUP BY m.id
    ORDER BY m.dt DESC
    LIMIT 10
") ?? [];

// Sales by category this week
$by_category = DB::query("
    SELECT
        CASE d.item_Category
            WHEN 1 THEN 'Uniform'
            WHEN 2 THEN 'Sports'
            WHEN 3 THEN 'Scouts & Guides'
            WHEN 9 THEN 'Workwear'
            ELSE 'Other'
        END as cat_name,
        SUM(d.qty * d.item_Price) as total
    FROM sales_details d
    JOIN sales_master m ON m.order_Id = d.order_Id
    WHERE m.pg_Status = '0' AND m.pg_Authcode != ''
    AND m.dt >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY d.item_Category
    ORDER BY total DESC
") ?? [];

// Revenue trend last 7 days
$trend = DB::query("
    SELECT DATE(dt) as day, SUM(pg_Amount) as total
    FROM sales_master
    WHERE pg_Status = '0' AND pg_Authcode != ''
    AND dt >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(dt)
    ORDER BY day ASC
") ?? [];

// Revenue change %
$rev_change = 0;
if ($last_week['total'] > 0) {
    $rev_change = round((($revenue['total'] - $last_week['total']) / $last_week['total']) * 100);
}
