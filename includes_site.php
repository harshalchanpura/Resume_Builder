<?php
if (!function_exists('site_header')) {
function site_header($title='ResumeFlow', $active='') {
    global $tab_token;
    $logged=!empty($_SESSION['user_id']) && $tab_token!=='';
    $start=$logged?tab_url('builder.php'):tab_url('register.php');
    $nav=[
      ['Home','index.php','home'],['About Us','about.php','about'],['Template','templates.php','template'],
      
    ];
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($title)?> — ResumeFlow</title><link rel="stylesheet" href="assets/css/app.css"></head><body class="site-page">
<header class="topbar site-topbar"><a class="brand" href="<?=e(tab_url('index.php'))?>"><span class="brand-mark">R</span> ResumeFlow</a>
<nav class="site-nav">
<?php foreach($nav as [$label,$href,$key]): ?><a class="<?=($active===$key?'active':'')?>" href="<?=e(tab_url($href))?>"><?=e($label)?><?php if($key==='template'): ?><span class="nav-caret">⌄</span><?php endif; ?></a><?php endforeach; ?>
<?php if($logged): ?><a href="<?=e(tab_url('dashboard.php'))?>">Dashboard</a><a class="nav-btn" href="<?=e(tab_url('logout.php'))?>">Log out</a><?php else: ?><a href="<?=e(tab_url('login.php'))?>">Log in</a><a class="nav-btn" href="<?=e(tab_url('register.php'))?>">Sign Up</a><?php endif; ?>
</nav></header>
<?php }
function site_footer(){ global $tab_token; ?>
<footer class="site-footer"><div><a class="brand" href="<?=e(tab_url('index.php'))?>"><span class="brand-mark">R</span> ResumeFlow</a><p>Professional resumes, career tools and application-ready documents in one workspace.</p></div><div class="footer-links"><a href="<?=e(tab_url('about.php'))?>">About</a><a href="<?=e(tab_url('templates.php'))?>">Templates</a><a href="<?=e(tab_url('contact.php'))?>">Contact</a><a href="<?=e(tab_url('privacy.php'))?>">Privacy</a><a href="<?=e(tab_url('terms.php'))?>">Terms</a></div><small>© <?=date('Y')?> ResumeFlow</small></footer></body></html>
<?php }
}
?>
