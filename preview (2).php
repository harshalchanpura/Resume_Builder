<?php
include '../config.php';
require_admin();
$id=(int)($_GET['id']??0); $s=$conn->prepare('SELECT * FROM resumes WHERE id=? LIMIT 1'); $s->bind_param('i',$id); $s->execute(); $r=$s->get_result()->fetch_assoc(); if(!$r){http_response_code(404);die('Resume not found.');}
$asset_prefix='../';
$adminTabUrl=admin_tab_url('dashboard.php');
$adminView=true;
include '../resume_markup.php';
?>
