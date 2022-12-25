<div id="login" class="container">
<?php if ( $dura['error'] ) : ?>
<div class="error">
<?php echo $dura['error'] ?>
</div>
<?php endif ?>

<form action="#" method="post">

<div class="language">
<select name="language">
<?php foreach ( $dura['languages'] as $langcode => $language ) : ?>
	<option value="<?php e($langcode) ?>"<?php if ( $langcode == $dura['default_language']): ?> selected="selected"<?php endif ?>><?php e($language) ?></option>
<?php endforeach ?>
</select>
</div>

<ul class="icons" id="avatar">
<?php foreach ( $dura['icons'] as $icon => $file ) : ?>
<li>
	<label>
		<img src="<?php echo Dura_Class_Icon::getIconUrl($icon) ?>" />
		<input type="radio" name="icon" value="<?php echo $icon ?>" />
	</label>
</li>
<?php endforeach ?>
</ul>

<div class="logo">
<img src="<?php e(DURA_URL) ?>/images/mushoku.png" />
</div>

<div class="field">
<input type="text" name="name" value="" size="10" maxlength="40" class="textbox" />
<input type="submit" name="login" value="<?php e(t("LOGIN")) ?>" class="button" />
</div>

<div class="insider">
<p><?php e(t("{1} users online!", $dura['active_user'])) ?></p>
<p class="rooms"><?php e(t("ROOMS")) ?> (<?php e($dura['room_count']) ?>) >> <?php if ( $dura['room_count'] ) : ?><?php foreach ( $dura['rooms'] as $category ) : ?><?php foreach ( $category as $language ) : ?><?php foreach ( $language as $rooms ) : ?><?php foreach ( $rooms as $room ) : ?><?php e($room['name']) ?> / <?php endforeach ?><?php endforeach ?><?php endforeach ?><?php endforeach ?><?php else : ?>No Room<?php endif ?></p>
</div>

<input type="hidden" name="token" value="<?php echo $dura['token'] ?>" />

</form>

<?php if ( file_exists(DURA_TEMPLATE_PATH.'/footer.html') ) : ?>
<div class="footer">
<?php require DURA_TEMPLATE_PATH.'/footer.html' ?>
</div>
<?php endif ?>
<div class="languageLinks">
<?php foreach ( $dura['languages'] as $langcode => $language ) : ?>
<a href="<?php e(Dura::url(null, null, array('language' => $langcode))) ?>"><?php e($language) ?></a> | 
<?php endforeach ?>
</div>
<div class="copyright">
<a href="<?php e(Dura::url('admin')) ?>"><?php e("Admin") ?></a> | DLC &copy; 2010 | MUSHOKU &copy; 2012 | <a href="http://github.com/schnabear/mushoku/">GitHub</a> | <a href="<?php e(Dura::url('page', 'readme')) ?>"><?php e("Read Me") ?></a> | <a href="<?php e(Dura::url('page', 'about')) ?>"><?php e("About") ?></a>
</div>
</div>