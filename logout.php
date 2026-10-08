<?php
include 'config.php';
if($tab_token!==''){ $s=$conn->prepare('DELETE FROM tab_sessions WHERE token=?');if($s){$s->bind_param('s',$tab_token);$s->execute();}unset($_SESSION['user_id'],$_SESSION['user_name']);header('Location: index.php');exit;}
if(!empty($_SESSION['admin']))unset($_SESSION['admin']);header('Location: index.php');exit;
?>