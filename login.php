<?php require_once 'includes/config.php';
if (!empty($_SESSION['admin_id'])) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/db.php';
    $user = clean($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $row  = DB::one("SELECT id, user_Id, password, first_Name, last_Name FROM admin_users WHERE user_Id = ? AND status = '1' LIMIT 1", [$user]);
    if ($row && password_verify($pass, $row['password'])) {
        $_SESSION['admin_id']   = $row['id'];
        $_SESSION['admin_user'] = $row['user_Id'];
        $_SESSION['admin_name'] = $row['first_Name'] . ' ' . $row['last_Name'];
        header('Location: dashboard.php'); exit;
    } else {
        // Legacy MD5 support during migration
        $md5row = DB::one("SELECT id, user_Id, password, first_Name, last_Name FROM admin_users WHERE user_Id = ? AND password = ? AND status = '1' LIMIT 1", [$user, md5($pass)]);
        if ($md5row) {
            // Upgrade to bcrypt on the fly
            DB::run("UPDATE admin_users SET password = ? WHERE id = ?", [password_hash($pass, PASSWORD_BCRYPT), $md5row['id']]);
            $_SESSION['admin_id']   = $md5row['id'];
            $_SESSION['admin_user'] = $md5row['user_Id'];
            $_SESSION['admin_name'] = $md5row['first_Name'] . ' ' . $md5row['last_Name'];
            header('Location: dashboard.php'); exit;
        }
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Hewitts Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',system-ui,sans-serif;background:#F0F2F7;min-height:100vh;display:flex;align-items:center;justify-content:center}
.wrap{width:100%;max-width:400px;padding:24px}
.card{background:#fff;border-radius:16px;padding:40px;box-shadow:0 2px 24px rgba(0,0,0,.08);border:1px solid #E5E7EB}
.logo{text-align:center;margin-bottom:32px}
.logo-icon{width:56px;height:56px;background:#1E3A5F;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:28px}
.logo-title{font-size:20px;font-weight:600;color:#1a1f36}
.logo-sub{font-size:13px;color:#6B7280;margin-top:4px}
.field{margin-bottom:16px}
.field label{display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px}
.field input{width:100%;padding:11px 14px;border:1.5px solid #E5E7EB;border-radius:10px;font-size:14px;font-family:inherit;color:#1a1f36;outline:none;transition:border-color .15s}
.field input:focus{border-color:#1E3A5F}
.error{background:#FEF2F2;border:1px solid #FECACA;color:#DC2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px}
.btn{width:100%;padding:12px;background:#1E3A5F;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .15s;margin-top:4px}
.btn:hover{background:#162d4a}
.footer{text-align:center;margin-top:20px;font-size:12px;color:#9CA3AF}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <div class="logo">
      <div class="logo-icon">🎓</div>
      <div class="logo-title">Hewitts of Croydon</div>
      <div class="logo-sub">Admin Portal</div>
    </div>

    <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Enter your username" autofocus autocomplete="username">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password">
      </div>
      <button type="submit" class="btn">Sign in</button>
    </form>
  </div>
  <div class="footer">Hewitts of Croydon Admin &copy; <?= date('Y') ?></div>
</div>
</body>
</html>
