<?php
if (session_status()===PHP_SESSION_NONE) session_start();
mysqli_report(MYSQLI_REPORT_OFF);
// ---- Database connection: Railway env vars first, XAMPP defaults as fallback ----
$db_url = getenv('MYSQL_URL') ?: (getenv('DATABASE_URL') ?: getenv('MYSQL_PUBLIC_URL'));
$db_host = getenv('MYSQLHOST') ?: 'localhost';
$db_port = (int)(getenv('MYSQLPORT') ?: 3306);
$db_user = getenv('MYSQLUSER') ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : '';
$db_name = getenv('MYSQLDATABASE') ?: 'resume_builder';
if ($db_url && ($u = parse_url($db_url)) && !empty($u['host'])) {
    $db_host = $u['host']; $db_port = (int)($u['port'] ?? 3306);
    $db_user = urldecode($u['user'] ?? $db_user); $db_pass = urldecode($u['pass'] ?? '');
    if (!empty($u['path'])) $db_name = ltrim($u['path'], '/');
}
// Retry a few times: on Railway the MySQL service can still be starting when the web service boots.
$conn = null;
for ($try = 1; $try <= 5; $try++) {
    $conn = @new mysqli($db_host, $db_user, $db_pass, '', $db_port);
    if (!$conn->connect_error) break;
    error_log("DB connect attempt $try failed for $db_host:$db_port as $db_user: ".$conn->connect_error);
    if ($try < 5) sleep(1);
}
if ($conn->connect_error) { http_response_code(500); die('Database connection failed. In Railway, add the MySQL variables to this web service (see README).'); }
$conn->set_charset('utf8mb4');
$db_name_safe = preg_replace('/[^a-zA-Z0-9_]/', '', $db_name);
$conn->query("CREATE DATABASE IF NOT EXISTS `$db_name_safe` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($db_name_safe);
$conn->query("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL DEFAULT '',email VARCHAR(180) NOT NULL,password VARCHAR(255) NOT NULL DEFAULT '',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY uq_users_email(email)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS resumes (id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,resume_name VARCHAR(160) NOT NULL DEFAULT 'My Resume',full_name VARCHAR(160) DEFAULT '',headline VARCHAR(200) DEFAULT '',email VARCHAR(180) DEFAULT '',phone VARCHAR(50) DEFAULT '',address VARCHAR(255) DEFAULT '',location VARCHAR(120) DEFAULT '',target_job VARCHAR(150) DEFAULT '',region VARCHAR(20) DEFAULT 'india',layout VARCHAR(30) DEFAULT 'split',accent VARCHAR(20) DEFAULT '#d97706',summary TEXT,education TEXT,skills TEXT,experience TEXT,projects TEXT,certifications TEXT,achievements TEXT,languages TEXT,linkedin VARCHAR(255) DEFAULT '',github VARCHAR(255) DEFAULT '',portfolio VARCHAR(255) DEFAULT '',photo VARCHAR(255) DEFAULT '',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
function ensure_column($conn,$table,$column,$definition){$table=preg_replace('/[^a-zA-Z0-9_]/','',$table);$column=preg_replace('/[^a-zA-Z0-9_]/','',$column);$q=$conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");if(!$q||$q->num_rows===0)$conn->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition");}
foreach([['name',"VARCHAR(120) NOT NULL DEFAULT ''"],['email',"VARCHAR(180) NOT NULL DEFAULT ''"],['password',"VARCHAR(255) NOT NULL DEFAULT ''"],['created_at',"TIMESTAMP DEFAULT CURRENT_TIMESTAMP"]] as $c)ensure_column($conn,'users',$c[0],$c[1]);
foreach([['resume_name',"VARCHAR(160) NOT NULL DEFAULT 'My Resume'"],['full_name',"VARCHAR(160) DEFAULT ''"],['headline',"VARCHAR(200) DEFAULT ''"],['email',"VARCHAR(180) DEFAULT ''"],['phone',"VARCHAR(50) DEFAULT ''"],['address',"VARCHAR(255) DEFAULT ''"],['location',"VARCHAR(120) DEFAULT ''"],['target_job',"VARCHAR(150) DEFAULT ''"],['region',"VARCHAR(20) DEFAULT 'india'"],['layout',"VARCHAR(30) DEFAULT 'split'"],['accent',"VARCHAR(20) DEFAULT '#d97706'"],['font_family',"VARCHAR(120) DEFAULT 'DM Sans'"],['font_size',"DECIMAL(4,1) DEFAULT 10.5"],['sidebar_font_size',"DECIMAL(4,1) DEFAULT 10.5"],['main_font_size',"DECIMAL(4,1) DEFAULT 10.5"],['font_weight',"VARCHAR(10) DEFAULT '400'"],['font_style',"VARCHAR(20) DEFAULT 'normal'"],['line_height',"DECIMAL(3,2) DEFAULT 1.45"],['letter_spacing',"DECIMAL(4,2) DEFAULT 0"],['summary','TEXT'],['career_objective','TEXT'],['academic_qualification','TEXT'],['education','TEXT'],['skills','TEXT'],['experience','TEXT'],['projects','TEXT'],['certifications','TEXT'],['achievements','TEXT'],['languages','TEXT'],['hobbies','TEXT'],['linkedin',"VARCHAR(255) DEFAULT ''"],['github',"VARCHAR(255) DEFAULT ''"],['portfolio',"VARCHAR(255) DEFAULT ''"],['photo',"VARCHAR(255) DEFAULT ''"],['created_at',"TIMESTAMP DEFAULT CURRENT_TIMESTAMP"],['updated_at',"TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"]] as $c)ensure_column($conn,'resumes',$c[0],$c[1]);
// Allow each account to own unlimited resumes. Older installations had a UNIQUE user_id index; migrate it to a normal index.
$idx=$conn->query("SHOW INDEX FROM resumes WHERE Column_name='user_id'");
if($idx){ while($ix=$idx->fetch_assoc()){ if((int)$ix['Non_unique']===0 && $ix['Key_name']!=='PRIMARY'){ $kn=preg_replace('/[^a-zA-Z0-9_]/','',$ix['Key_name']); if($kn) $conn->query("ALTER TABLE resumes DROP INDEX `$kn`"); } } }
$idx2=$conn->query("SHOW INDEX FROM resumes WHERE Key_name='idx_resumes_user_id'"); if(!$idx2 || $idx2->num_rows===0) $conn->query("CREATE INDEX idx_resumes_user_id ON resumes(user_id)");
$conn->query("UPDATE resumes SET resume_name=CONCAT('Resume ',id) WHERE resume_name='' OR resume_name IS NULL");
// Migrate the previous Summary/Education content into the new field names without losing existing data.
$conn->query("UPDATE resumes SET career_objective=summary WHERE (career_objective IS NULL OR TRIM(career_objective)='') AND summary IS NOT NULL AND TRIM(summary)<>''");
$conn->query("UPDATE resumes SET academic_qualification=education WHERE (academic_qualification IS NULL OR TRIM(academic_qualification)='') AND education IS NOT NULL AND TRIM(education)<>''");
$conn->query("CREATE TABLE IF NOT EXISTS tab_sessions (token VARCHAR(64) PRIMARY KEY,user_id INT NOT NULL,expires_at DATETIME NOT NULL,INDEX(user_id),INDEX(expires_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS admin_tab_sessions (token VARCHAR(64) PRIMARY KEY,username VARCHAR(80) NOT NULL,expires_at DATETIME NOT NULL,INDEX(expires_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS job_alerts (id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NULL,email VARCHAR(180) NOT NULL,role VARCHAR(150) NOT NULL,location VARCHAR(150) DEFAULT '',frequency VARCHAR(30) DEFAULT 'weekly',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(email),INDEX(user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS cover_letters (id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL UNIQUE,recipient VARCHAR(180) DEFAULT '',company VARCHAR(180) DEFAULT '',role VARCHAR(180) DEFAULT '',opening TEXT,body TEXT,closing TEXT,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,CONSTRAINT fk_cover_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("DELETE FROM tab_sessions WHERE expires_at < NOW()");
$conn->query("DELETE FROM admin_tab_sessions WHERE expires_at < NOW()");
$tab_token='';
if(isset($_GET['tab']))$tab_token=preg_replace('/[^a-zA-Z0-9_-]/','',(string)$_GET['tab']);
if(isset($_POST['tab']))$tab_token=preg_replace('/[^a-zA-Z0-9_-]/','',(string)$_POST['tab']);
$page_token=bin2hex(random_bytes(24));
unset($_SESSION['user_id'],$_SESSION['user_name']);
if($tab_token!==''){$s=$conn->prepare('SELECT u.id,u.name FROM tab_sessions t JOIN users u ON u.id=t.user_id WHERE t.token=? AND t.expires_at>NOW() LIMIT 1');if($s){$s->bind_param('s',$tab_token);$s->execute();$tab_user=$s->get_result()->fetch_assoc();if($tab_user){$_SESSION['user_id']=(int)$tab_user['id'];$_SESSION['user_name']=$tab_user['name'];$u=$conn->prepare('UPDATE tab_sessions SET expires_at=DATE_ADD(NOW(), INTERVAL 12 HOUR) WHERE token=?');if($u){$u->bind_param('s',$tab_token);$u->execute();}}else{$tab_token='';}}else{$tab_token='';}}
$admin_tab_token='';
if(isset($_GET['admin_tab']))$admin_tab_token=preg_replace('/[^a-zA-Z0-9_-]/','',(string)$_GET['admin_tab']);
if(isset($_POST['admin_tab']))$admin_tab_token=preg_replace('/[^a-zA-Z0-9_-]/','',(string)$_POST['admin_tab']);
$admin_page_token=bin2hex(random_bytes(24));
unset($_SESSION['admin'],$_SESSION['admin_username']);
if($admin_tab_token!==''){$as=$conn->prepare('SELECT username FROM admin_tab_sessions WHERE token=? AND expires_at>NOW() LIMIT 1');if($as){$as->bind_param('s',$admin_tab_token);$as->execute();$admin_user=$as->get_result()->fetch_assoc();if($admin_user){$_SESSION['admin']=true;$_SESSION['admin_username']=$admin_user['username'];$au=$conn->prepare('UPDATE admin_tab_sessions SET expires_at=DATE_ADD(NOW(), INTERVAL 12 HOUR) WHERE token=?');if($au){$au->bind_param('s',$admin_tab_token);$au->execute();}}else{$admin_tab_token='';}}else{$admin_tab_token='';}}
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function require_user(){global $tab_token;if(empty($_SESSION['user_id'])||$tab_token===''){header('Location: '.tab_url('login.php'));exit;}}
function require_admin(){global $admin_tab_token;if(empty($_SESSION['admin'])||$admin_tab_token===''){header('Location: login.php');exit;}}
function tab_url($url='index.php'){global $tab_token;if($tab_token==='')return $url;$join=strpos($url,'?')===false?'?':'&';return $url.$join.'tab='.rawurlencode($tab_token);}
function admin_tab_url($url='dashboard.php'){global $admin_tab_token;if($admin_tab_token==='')return $url;$join=strpos($url,'?')===false?'?':'&';return $url.$join.'admin_tab='.rawurlencode($admin_tab_token);}
function get_resume($conn,$uid,$resume_id=0){if($resume_id>0){$s=$conn->prepare('SELECT * FROM resumes WHERE id=? AND user_id=? LIMIT 1');if(!$s)return null;$s->bind_param('ii',$resume_id,$uid);}else{$s=$conn->prepare('SELECT * FROM resumes WHERE user_id=? ORDER BY updated_at DESC,id DESC LIMIT 1');if(!$s)return null;$s->bind_param('i',$uid);} $s->execute();return $s->get_result()->fetch_assoc();}
function get_resumes($conn,$uid){$s=$conn->prepare('SELECT * FROM resumes WHERE user_id=? ORDER BY updated_at DESC,id DESC');if(!$s)return []; $s->bind_param('i',$uid);$s->execute();return $s->get_result()->fetch_all(MYSQLI_ASSOC);}
function completion($r){if(!$r)return 0;$fields=['full_name','headline','email','phone','summary','education','skills','projects','experience'];$n=0;foreach($fields as $f)if(trim($r[$f]??''))$n++;return(int)round($n/count($fields)*100);}
function region_data($key){$all=['india'=>['India','IN','Two-column professional CV','Saffron / warm editorial'],'usa'=>['United States','US','ATS-friendly concise resume','Clean blue / corporate'],'canada'=>['Canada','CA','Professional resume','Cool blue / structured'],'australia'=>['Australia','AU','Modern professional CV','Teal / spacious']];return$all[$key]??$all['india'];}
function allowed_color($c){return preg_match('/^#[0-9a-fA-F]{6}$/',$c)?$c:'#d97706';}
?>