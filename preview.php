<?php
include 'config.php';
require_user();
$r=get_resume($conn,(int)$_SESSION['user_id'],(int)($_GET['resume_id']??0));
if(!$r){header('Location: '.tab_url('dashboard.php'));exit;}
$adminView=false;
include 'resume_markup.php';
?>
