<div id="login" class="container">
<h2><?php e(t("Admin Announce")) ?></h2>
<form action="#" method="post" id="message">
<textarea name="message"></textarea>
<div class="submit">
<input type="submit" name="post" value="<?php e(t("POST!")) ?>" />
</div>
</form>
<a href="<?php e(Dura::url('lounge')) ?>" class="control"><?php e(t("Back")) ?></a>
</div>

<div id="talks" class="logs">
<?php foreach ( $dura['talks'] as $time ) foreach ( $time as $talk ) : ?>
<?php if ( !$talk['uid'] ) : ?>
<div class="talk system" id="<?php e($talk['id']) ?>"><?php e($talk['message']) ?></div>
<?php else: ?>
<dl class="talk <?php e($talk['icon']) ?>" id="<?php e($talk['id']) ?>">
<?php if ( $talk['code'] != '' ) : ?>
<dt title="<?php e($talk['code']) ?>"><?php e($talk['name']) ?></dt>
<?php else : ?>
<dt><?php e($talk['name']) ?></dt>
<?php endif ?>
<dd>
	<div class="bubble">
		<p class="body"><?php e($talk['message']) ?></p>
	</div>
</dd>
</dl>
<?php endif ?>
<?php endforeach ?>
</div><!-- /#talks.logs -->