<?php
include '../config.php';
if($admin_tab_token!==''){
    $s=$conn->prepare('DELETE FROM admin_tab_sessions WHERE token=?');
    if($s){$s->bind_param('s',$admin_tab_token);$s->execute();}
}
unset($_SESSION['admin'],$_SESSION['admin_username']);
header('Location: login.php');exit;
?>
