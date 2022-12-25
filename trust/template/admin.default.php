<div id="login" class="container">
<?php if ( $dura['error'] ) : ?>
<div class="error">
<?php echo $dura['error'] ?>
</div>
<?php endif ?>

<form action="#" method="post">

<img src="<?php e(DURA_URL) ?>/images/N33T.png" id="splash" />

<div class="field">
<?php e(t("Admin ID")) ?><br />
<input type="text" name="name" value="" size="10" maxlength="10" class="textbox" />
<?php e(t("Password")) ?><br />
<input type="password" name="pass" value="" size="10" class="textbox" />
<input type="submit" name="login" value="<?php e(t("LOGIN")) ?>" class="button" />
</div>

<input type="hidden" name="token" value="<?php echo $dura['token'] ?>" />

</form>

<a href="<?php e(Dura::url()) ?>" class="control clear"><?php e(t("Back")) ?></a>
</div>