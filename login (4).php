<?php
include '../config.php';

$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $username=trim($_POST['username']??'');
    $password=$_POST['password']??'';
    $adm_user=getenv('ADMIN_USER')?:'admin'; $adm_pass=getenv('ADMIN_PASSWORD')?:'admin123';
    if (hash_equals($adm_user,$username) && hash_equals($adm_pass,$password)) {
        $token=$admin_tab_token!==''?$admin_tab_token:$admin_page_token;
        $ins=$conn->prepare('INSERT INTO admin_tab_sessions(token,username,expires_at) VALUES(?,?,DATE_ADD(NOW(), INTERVAL 12 HOUR)) ON DUPLICATE KEY UPDATE username=VALUES(username),expires_at=VALUES(expires_at)');
        if($ins){$ins->bind_param('ss',$token,$username);$ins->execute();}
        $_SESSION['admin']=true;
        $_SESSION['admin_username']=$username;
        header('Location: dashboard.php?admin_tab='.rawurlencode($token)); exit;
    }
    $msg='Invalid admin username or password.';
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login — ResumeFlow</title><link rel="stylesheet" href="../assets/css/app.css"></head><body class="auth-page"><div class="auth-shell"><a class="brand" href="../index.php"><span class="brand-mark">R</span> ResumeFlow</a><div class="auth-card"><div class="auth-visual admin-visual"><span class="eyebrow">ADMIN CONTROL CENTER</span><h1>Manage the platform.</h1><p>Users, resumes and activity are managed separately from the user workspace.</p></div><div class="auth-form"><h2>Admin login</h2><?php if($msg): ?><div class="alert"><?php echo e($msg); ?></div><?php endif; ?><form method="post"><input type="hidden" name="admin_tab" id="adminTabToken" value="<?php echo e($admin_tab_token!==''?$admin_tab_token:$admin_page_token); ?>"><label>Username<input name="username" autocomplete="username" required></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button class="primary full" type="submit">Sign in to admin →</button></form><p class="switch"><a href="../index.php">← Back to website</a></p></div></div></div><script src="../assets/js/admin-tab-auth.js"></script></body></html>
