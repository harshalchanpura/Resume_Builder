<?php
include 'config.php';
$token = $tab_token !== '' ? $tab_token : $page_token;
$preferred_layout = $_GET['layout'] ?? ($_POST['layout'] ?? '');
$allowed_layouts = ['split','clean','header','modern','executive','minimal','timeline','creative','corporate','consulting','tech','academic','compact','elegant','grid','mono','senior'];
if (!in_array($preferred_layout, $allowed_layouts, true)) $preferred_layout = '';
if (!empty($_SESSION['user_id']) && $tab_token !== '') {
    header('Location: ' . tab_url('dashboard.php')); exit;
}
$msg = '';
if (isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (strlen($name) < 2) {
        $msg = 'Please enter your full name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Please enter a valid email address.';
    } elseif (strlen($pass) < 6) {
        $msg = 'Password must be at least 6 characters.';
    } elseif ($pass !== $confirm) {
        $msg = 'Passwords do not match.';
    } else {
        $check = $conn->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
        if (!$check) {
            $msg = 'Database setup error: ' . ($conn->error ?: 'Could not prepare the query.');
        } else {
            $check->bind_param('s', $email);
            $check->execute();
            $exists = $check->get_result()->fetch_assoc();
            if ($exists) {
                $msg = 'This email is already registered. Please log in.';
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $s = $conn->prepare('INSERT INTO users(name,email,password) VALUES(?,?,?)');
                if (!$s) {
                    $msg = 'Database setup error: ' . ($conn->error ?: 'Could not prepare account creation.');
                } else {
                    $s->bind_param('sss', $name, $email, $hash);
                    if (!$s->execute()) {
                        $msg = 'Could not create the account: ' . ($s->error ?: $conn->error);
                    } else {
                        $newId = $s->insert_id;
                        $ins = $conn->prepare('INSERT INTO tab_sessions(token,user_id,expires_at) VALUES(?,?,DATE_ADD(NOW(), INTERVAL 12 HOUR)) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id),expires_at=VALUES(expires_at)');
                        if (!$ins) {
                            $msg = 'Account created, but login session could not be started: ' . $conn->error;
                        } else {
                            $ins->bind_param('si', $token, $newId);
                            if (!$ins->execute()) {
                                $msg = 'Account created, but login session could not be started: ' . $ins->error;
                            } else {
                                $_SESSION['user_id'] = $newId;
                                $_SESSION['user_name'] = $name;
                                if ($preferred_layout !== '') { header('Location: builder.php?layout=' . rawurlencode($preferred_layout) . '&tab=' . rawurlencode($token)); } else { header('Location: dashboard.php?tab=' . rawurlencode($token)); } exit;
                            }
                        }
                    }
                }
            }
        }
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create account — ResumeFlow</title><link rel="stylesheet" href="assets/css/app.css"></head><body class="auth-page"><div class="auth-shell"><a class="brand" href="<?php echo e(tab_url('index.php')); ?>"><span class="brand-mark">R</span> ResumeFlow</a><div class="auth-card"><div class="auth-visual signup"><span class="eyebrow">YOUR NEXT CHAPTER</span><h1>Build something that represents you.</h1><p>Create an account, save your resume and keep refining it whenever you want.</p><div class="check-list"><span>✓ Save your work</span><span>✓ Change design anytime</span><span>✓ Print-ready preview</span></div></div><div class="auth-form"><h2>Create account</h2><p class="muted">It only takes a minute.</p><?php if($msg): ?><div class="alert"><?php echo e($msg); ?></div><?php endif; ?><form method="post"><input type="hidden" name="tab" id="tabToken" value="<?php echo e($token); ?>"><input type="hidden" name="layout" value="<?php echo e($preferred_layout); ?>"><label>Full name<input name="name" required autocomplete="name"></label><label>Email<input type="email" name="email" required autocomplete="email"></label><label>Password<input type="password" name="password" minlength="6" required autocomplete="new-password"></label><label>Confirm password<input type="password" name="confirm" minlength="6" required autocomplete="new-password"></label><button class="primary full" name="register">Create account →</button></form><p class="switch">Already registered? <a href="<?php echo e(tab_url('login.php')); ?>">Log in</a></p></div></div></div><script src="assets/js/tab-auth.js"></script></body></html>
