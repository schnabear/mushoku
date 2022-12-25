<ul id="profile">
<li class="icon"><img src="<?php echo $dura['profile']['icon'] ?>" /></li>
<li class="name"><?php echo $dura['profile']['name'] ?></li>
<?php if ( Dura::user()->isAdmin() ) : ?>
<li class="admin">
<a href="<?php e(Dura::url('announce')) ?>"><?php e(t("Announce")) ?></a>
</li>
<?php endif ?>
<li class="logout">
<form action="<?php echo Dura::url('logout') ?>" method="post">
<input type="submit" class="button" value="<?php e(t("LOGOUT")) ?>" />
</form>
</li>
</ul>

<div class="clear"></div>

<div class="container">

<h2><?php e(t("Lounge")) ?></h2>

<div class="right"><?php e(t("{1} users online!", $dura['active_user'])) ?></div>

<div class="clear"></div>

<?php if ( Dura::user()->isAdmin() ) : ?>
<div class="right"><?php e(t("{1} allotted capacity!", $dura['active_capacity'])) ?></div>

<div class="clear"></div>

<?php endif ?>
<div id="create_room">
<form action="<?php e(Dura::url('create')) ?>" method="post">
<input type="submit" class="button" value="<?php e(t("CREATE ROOM")) ?>" />
</form>
</div>

<div class="clear"></div>

<?php $url = Dura::url('room'); ?>
<?php foreach ( $dura['rooms'] as $sameLanguageGroup ) : ?>
<?php foreach ( $sameLanguageGroup as $language => $languageGroup ) : ?>
<p class="clear"><img src="<?php e(DURA_URL . "/images/flag/" . $language . ".png") ?>" alt="<?php e($language) ?>" /></p>
<?php foreach ( $languageGroup as $retentionGroup ) : ?>
<?php foreach ( $retentionGroup as $room ) : ?>
<ul class="rooms">
<li class="name">
	<?php if ( $room['mark'] != '' ) : ?><span class="mark"><img src="<?php e(Dura_Class_Mark::getMarkUrl($room['mark'])) ?>" width="14" height="14" alt="<?php e($room['name']) ?>" /></span><?php endif ?>
	<?php if ( $room['password'] != '' ) : ?><span class="key"><img src="<?php e(DURA_URL) ?>/css/key.png" width="14" height="14" alt="<?php e(t('Password Protected')) ?>" /></span><?php endif ?>
	<span<?php if ( Dura::trim($room['color']) != '' ) : ?> style="color: <?php e($room['color'])?>;"<?php endif ?>><?php e($room['name']) ?></span>
</li>
<li class="users">
<?php if ( !empty($room['users']) ) : ?>
<?php foreach ( $room['users'] as $user ) : ?>
<div class="name" title="<?php e(( $room['host'] == $user['id'] ? 'Host: ' : 'Member: ' ) . $user['name']) ?>"><img src="<?php e(Dura_Class_Icon::getIconUrl($user['icon'])) ?>" /><?php e($user['name']) ?></div>
<?php endforeach ?>
<?php else : ?>
<div class="empty"><?php e(t('Room Empty'))?></div>
<?php endif ?>
</li>
<li class="member"><?php e($room['total']) ?> / <?php e($room['limit']) ?></li>
<li class="login">
<?php if ( $room['unavailable'] ) : ?>
<?php e(t("N/A")) ?>
<?php elseif ( $room['total'] >= $room['limit'] ) : ?>
<?php e(t("Full")) ?>
<?php else : ?>
<form action="<?php e($url) ?>" method="post">
<input type="submit" name="login" value="<?php e(t("ENTER")) ?>" class="button" />
<input type="hidden" name="id" value="<?php e($room['id']) ?>" />
</form>
<?php endif ?>
<?php if ( Dura::user()->isAdmin() ) : ?>
<form action="<?php e($url) ?>" method="post">
<input type="submit" name="source" value="<?php e(t("SOURCE")) ?>" class="button subcontrol" />
<input type="hidden" name="id" value="<?php e($room['id']) ?>" />
</form>
<form action="<?php e($url) ?>" method="post">
<input type="submit" name="download" value="<?php e(t("DOWNLOAD")) ?>" class="button subcontrol" />
<input type="hidden" name="id" value="<?php e($room['id']) ?>" />
</form>
<form action="<?php e($url) ?>" method="post">
<input type="submit" name="delete" value="<?php e(t("DELETE")) ?>" class="button subcontrol" />
<input type="hidden" name="id" value="<?php e($room['id']) ?>" />
</form>
<?php endif ?>
</li>
</ul>
<?php endforeach ?>
<?php endforeach ?>
<?php endforeach ?>
<?php endforeach ?>

<div class="clear"></div>

</div>