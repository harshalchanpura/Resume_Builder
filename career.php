<?php
include 'config.php';
require_user();
$uid=(int)$_SESSION['user_id'];
$r=get_resume($conn,$uid) ?: [];
$name=trim($r['full_name']??'') ?: ($_SESSION['user_name']??'You');
$job=trim($r['target_job']??'');
$headline=trim($r['headline']??'');
$summary=trim($r['summary']??'');
$skills=trim($r['skills']??'');
$experience=trim($r['experience']??'');
$education=trim($r['education']??'');
$projects=trim($r['projects']??'');
$links=0;
foreach(['linkedin','github','portfolio'] as $f) if(trim($r[$f]??'')) $links++;
$checks=[
 ['Profile identity',!empty($r['full_name'])&&(!empty($r['headline'])||!empty($r['target_job'])), 'Add your name and a clear target role.'],
 ['Contact details',!empty($r['email'])&&(!empty($r['phone'])||!empty($r['location'])), 'Add an email plus phone or location.'],
 ['Professional summary',strlen($summary)>=80, 'Write a short value-focused summary.'],
 ['Skills',strlen($skills)>=40, 'List relevant technical and professional skills.'],
 ['Experience',strlen($experience)>=80, 'Show achievements, not only job duties.'],
 ['Education',strlen($education)>=30, 'Add your degree, institution and year.'],
 ['Projects',strlen($projects)>=40, 'Add projects with results and technologies.'],
 ['Online profile',$links>0, 'Add LinkedIn, GitHub or portfolio if relevant.']
];
$score=0; foreach($checks as $c) if($c[1]) $score+=round(100/count($checks));
$score=min(100,$score);
$region=$r['region']??'india';
$rd=region_data($region);
$roleTips=[
 'developer'=>['Use measurable results such as performance, users, features and technologies.','Put technical skills near the top.','Link GitHub projects when possible.'],
 'engineer'=>['Highlight systems, tools, testing and measurable improvements.','Use action verbs such as designed, optimized and automated.','Keep project details technically specific.'],
 'designer'=>['Show outcomes, not just tools.','Add portfolio links and 2–3 strongest projects.','Mention research, collaboration and measurable product impact.'],
 'manager'=>['Lead with team size, scope, budgets or business outcomes.','Use numbers to show impact.','Keep bullets focused on leadership and decisions.'],
 'analyst'=>['Highlight data, dashboards, models and business decisions.','Mention tools such as SQL, Python, Excel or Power BI when relevant.','Quantify accuracy, savings or reporting improvements.'],
 'student'=>['Put education, projects, internships and skills prominently.','Use coursework only when it supports the target role.','Add GitHub or portfolio links for technical work.']
];
$tipKey='student';
$low=strtolower($job.' '.$headline);
foreach(array_keys($roleTips) as $k){if(strpos($low,$k)!==false){$tipKey=$k;break;}}
$regionTips=[
 'india'=>['Keep education, skills and project details easy to scan.','A photo is optional; use it only when appropriate for the role or employer.'],
 'usa'=>['Keep the resume concise and achievement-led.','Avoid unnecessary personal details; focus on job-relevant information.'],
 'canada'=>['Use clear, factual experience and skills sections.','Keep formatting professional and easy for recruiters to scan.'],
 'australia'=>['Use clear section headings and practical achievements.','Tailor keywords and skills to the advertised position.']
];
?>
<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Career Toolkit — ResumeFlow</title>
<link rel="stylesheet" href="assets/css/app.css"></head>
<body>
<header class="topbar appbar">
<a class="brand" href="<?php echo e(tab_url('index.php')); ?>"><span class="brand-mark">R</span> ResumeFlow</a>
<nav>
<a href="<?php echo e(tab_url('dashboard.php')); ?>">Dashboard</a>
<a href="<?php echo e(tab_url('builder.php')); ?>">Builder</a>
<a class="active" href="<?php echo e(tab_url('career.php')); ?>">Career Toolkit</a>
<?php if($r): ?><a href="<?php echo e(tab_url('preview.php')); ?>">Preview</a><?php endif; ?>
<a class="nav-btn" href="<?php echo e(tab_url('logout.php')); ?>">Log out</a>
</nav></header>
<main class="page toolkit-page">
<div class="welcome">
<div><span class="eyebrow">CAREER TOOLKIT</span><h1>Make your resume work harder.</h1>
<p>Check your profile, improve weak sections and tailor your resume before you send it.</p></div>
<a class="primary" href="<?php echo e(tab_url('builder.php')); ?>">Edit resume →</a>
</div>
<section class="toolkit-hero panel">
<div class="toolkit-score">
<span class="muted">RESUME READINESS</span>
<div class="big-score"><?php echo $score; ?><small>/100</small></div>
<div class="progress"><i style="width:<?php echo $score; ?>%"></i></div>
<p><?php echo $score>=80?'Strong foundation — focus on tailoring it to each job.':($score>=55?'Good start — complete the missing sections before applying.':'Build the core sections first, then polish the wording.'); ?></p>
</div>
<div class="toolkit-target">
<span class="muted">TARGET ROLE</span>
<h2><?php echo e($job ?: 'Choose a target role'); ?></h2>
<p><?php echo e($headline ?: 'A focused headline helps recruiters understand your direction quickly.'); ?></p>
<div class="tool-badges"><span><?php echo e($rd[0]); ?></span><span><?php echo e(ucfirst($r['layout']??'split')); ?> layout</span><span><?php echo e(strtoupper($r['accent']??'#d97706')); ?></span></div>
</div>
</section>
<div class="toolkit-grid">
<section class="panel toolkit-card">
<div class="panel-title"><div><span class="muted">CHECKLIST</span><h2>Profile essentials</h2></div></div>
<div class="check-list">
<?php foreach($checks as $c): ?>
<div class="check-item <?php echo $c[1]?'done':''; ?>"><span><?php echo $c[1]?'✓':'○'; ?></span><div><b><?php echo e($c[0]); ?></b><small><?php echo e($c[1]?'Complete':$c[2]); ?></small></div></div>
<?php endforeach; ?>
</div>
</section>
<section class="panel toolkit-card">
<div class="panel-title"><div><span class="muted">ROLE PLAYBOOK</span><h2><?php echo e(ucfirst($tipKey)); ?> tips</h2></div></div>
<ul class="tip-list"><?php foreach($roleTips[$tipKey] as $tip): ?><li><?php echo e($tip); ?></li><?php endforeach; ?></ul>
<div class="tip-divider"></div>
<span class="muted"><?php echo e($rd[0]); ?> FORMAT</span>
<ul class="tip-list compact"><?php foreach($regionTips[$region] as $tip): ?><li><?php echo e($tip); ?></li><?php endforeach; ?></ul>
</section>
</div>
<section class="panel toolkit-card keyword-card">
<div><span class="muted">NEXT BEST ACTION</span><h2>Tailor before you apply.</h2><p>Copy important terms from the job description into your skills, summary or experience only when they genuinely match your experience. This keeps the resume relevant without keyword stuffing.</p></div>
<div class="action-row"><a class="primary" href="<?php echo e(tab_url('builder.php')); ?>">Improve sections →</a><?php if($r): ?><a class="ghost" href="<?php echo e(tab_url('preview.php')); ?>">Preview PDF</a><?php endif; ?></div>
</section>
</main>
<script src="assets/js/tab-auth.js"></script>
</body></html>