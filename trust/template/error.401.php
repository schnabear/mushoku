<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title><?php e("401 Unauthorized") ?> | <?php e(t(DURA_TITLE)) ?> | <?php e(t(DURA_SUBTITLE)) ?></title>
<link href="<?php echo DURA_URL; ?>/css/error.css" rel="stylesheet" type="text/css" media="screen" />
</head>
<body>
<div id="main">
<h1 class="error">401 Unauthorized</h1>
<p class="information_JP">このページへの認可アクセスを確認できません。</p>
<p class="information_EN">This server could not verify that you are authorized to access the URL "<?=htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES)?>".</p>
</div>
</body>
</html>