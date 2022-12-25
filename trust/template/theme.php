<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" /> 
<meta name="viewport" content="width = 620" />
<meta name="robots" content="INDEX, FOLLOW" />
<meta name="keywords" content="<?php e(t(DURA_KEYWORDS)) ?>" />
<meta name="description" content="<?php e(t(DURA_TITLE)) ?> | <?php e(t(DURA_SUBTITLE)) ?>" />
<title><?php e(t(DURA_TITLE)) ?> | <?php e(t(DURA_SUBTITLE)) ?></title>
<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1/themes/dot-luv/jquery-ui.min.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php e(DURA_URL) ?>/css/style.css?<?php e(DURA_VERSION) ?>" rel="stylesheet" type="text/css" media="screen" />
<?php if ( in_array(Dura::$controller, array('announce', 'room')) && Dura::$action == 'default' ) : ?>
<link href="<?php e(DURA_URL) ?>/css/tipTip.css?<?php e(DURA_VERSION) ?>" rel="stylesheet" type="text/css" />
<?php endif ?>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>
<!--script type="text/javascript" src="http://www.google.com/jsapi"></script--> 
<script type="text/javascript">
/* <![CDATA[ */
// google.load("language", "1");
// google.load("jquery", "1");
// google.load("jqueryui", "1");
duraUrl = "<?php e(DURA_URL) ?>";
GlobalMessageMaxLength = <?php e(DURA_MESSAGE_MAX_LENGTH) ?>;
useComet = <?php e(DURA_USE_COMET) ?>;
accessTime = <?php e(microtime(true)) ?>;
<?php if ( DURA_USE_RECAPTCHA ) : ?>
RecaptchaOptions = {
	theme : 'white'
};
<?php endif ?>
/* ]]> */
</script>
<script type="text/javascript" src="<?php e(DURA_URL) ?>/js/translator.js?<?php e(DURA_VERSION) ?>"></script>
<script type="text/javascript" src="<?php e(DURA_URL) ?>/js/language/<?php e(Dura::$language) ?>.js?<?php e(DURA_VERSION) ?>"></script>
<?php if ( Dura::$controller == 'default' ) : ?>
<script type="text/javascript" src="<?php e(DURA_URL) ?>/js/jquery.bubble.js?<?php e(DURA_VERSION) ?>"></script>
<script type="text/javascript">
/* <![CDATA[ */
function preload(arrayOfImages)
{
	$(arrayOfImages).each(function()
	{
		// (new Image()).src = this;
		$('<img/>')[0].src = this;
	});
}

preload([
<?php
	$icons = Dura_Class_Icon::getIcons();
	unset($icons['admin']);
	$icons = array_map(array('Dura_Class_Icon', 'getIconUrl'), array_keys($icons));
	foreach ( $icons as $key => $file )
	{
		$icons[$key] = "'$file'";
	}
	e(implode(",", $icons));
?>
]);

$(function()
{
	var iconElement = $("ul.icons label");
	iconElement.find('img').css({
		'border' : '1px #fff solid'
	});
	iconElement.click(function()
	{
		iconElement.find('input:not(:checked)').prev().children('img').css({
			'border' : '1px #ffffff solid'
		});
		// If in case clicking the label doesn't provide the checked attribute to its sibling input[type=radio]
		$(this).find('input:radio').attr('checked', 'checked');
		$(this).find('img').css({
			'border' : '1px #d90000 solid'
		});
	});
});
/* ]]> */
</script>
<?php elseif ( Dura::$controller == 'room' && Dura::$action == 'default' ) : ?>
<script type="text/javascript" src="<?php e(DURA_URL) ?>/js/jquery.ui.touch-punch.js?<?php e(DURA_VERSION) ?>"></script>
<script type="text/javascript" src="<?php e(DURA_URL) ?>/js/jquery.tipTip.js?<?php e(DURA_VERSION) ?>"></script>
<script type="text/javascript" src="<?php e(DURA_URL) ?>/js/jquery.notification.js?<?php e(DURA_VERSION) ?>"></script>
<script type="text/javascript" src="<?php e(DURA_URL) ?>/js/soundmanager/soundmanager2-nodebug-jsmin.js?<?php e(DURA_VERSION) ?>"></script>
<script type="text/javascript">
/* <![CDATA[ */
soundManager.url = '<?php e(DURA_URL) ?>/swf/';
soundManager.onready(function() {
	messageSound = soundManager.createSound({
		id: 'messageSound',
		url: '<?php e(DURA_URL) ?>/js/sound.mp3',
		volume: 100
	});
	whisperSound = soundManager.createSound({
		id: 'whisperSound',
		url: '<?php e(DURA_URL) ?>/js/whisper.mp3',
		volume: 100
	});
});
/* ]]> */
</script>
<script type="text/javascript" src="<?php e(DURA_URL) ?>/js/jquery.corner.js?<?php e(DURA_VERSION) ?>"></script>
<script type="text/javascript" src="<?php e(DURA_URL) ?>/js/jquery.chat.js?<?php e(DURA_VERSION) ?>"></script>
<?php elseif ( Dura::$controller == 'announce' ) : ?>
<script type="text/javascript" src="<?php e(DURA_URL) ?>/js/jquery.tipTip.js?<?php e(DURA_VERSION) ?>"></script>
<script type="text/javascript" src="<?php e(DURA_URL) ?>/js/jquery.announce.js?<?php e(DURA_VERSION) ?>"></script>
<?php elseif ( in_array(Dura::$controller, array('create', 'room')) && DURA_USE_RECAPTCHA ) : ?>
<script type="text/javascript" src='//www.google.com/recaptcha/api.js'></script>
<?php endif ?>
<?php if ( file_exists(DURA_TEMPLATE_PATH.'/header.html') ) require(DURA_TEMPLATE_PATH.'/header.html'); ?>
</head>
<body>
<div id="body">
<?php e($content) ?>
</div>
</body>
</html>