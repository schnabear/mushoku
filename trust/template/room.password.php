<div class="container">
<h2><?php e(t("Password Protected")) ?></h2>

<?php if ( $dura['error'] ) : ?>
<div class="error">
<?php echo $dura['error'] ?>
</div>
<?php endif ?>

<form action="#" method="post">
<table>
	<tr>
		<td><?php e(t("Room Password")) ?></td>
		<td><input type="text" name="password" value="<?php echo $dura['password'] ?>" size="20" maxlength="25" /></td>
	</tr>
<?php if ( DURA_USE_RECAPTCHA ) : ?>
	<tr>
		<td colspan="2"><div class="g-recaptcha" data-sitekey="<?php e(RECAPTCHA_PUBLIC_KEY) ?>"></div></td>
	</tr>
<?php endif ?>
	<tr>
		<td></td>
		<td>
			<input type="submit" name="submit" value="<?php e(t("LOGIN")) ?>" class="button" />
		</td>
	</tr>
</table>
</form>

<a href="<?php e(Dura::url('lounge')) ?>" class="control"><?php e(t("Back")) ?></a>
</div><!-- /#container -->