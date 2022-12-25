<div class="container">
<h2><?php e(t("Create Room")) ?></h2>

<?php if ( $dura['error'] ) : ?>
<div class="error">
<?php e($dura['error']) ?>
</div>
<?php endif ?>

<form action="#" method="post">
<table>
	<tr>
		<td><?php e(t("Room Name")) ?></td>
		<td><input type="text" name="name" value="<?php echo $dura['input']['name'] ?>" size="20" maxlength="12" /></td>
	</tr>
	<tr>
		<td><?php e(t("Max Members")) ?></td>
		<td>
			<select name="limit">
<?php for ( $i = $dura['user_min']; $i <= $dura['user_max']; $i++ ): ?>
				<option value="<?php echo $i ?>"<?php if ( $dura['input']['limit'] == $i ) : ?> selected="selected"<?php endif ?>><?php echo $i ?></option>
<?php endfor ?>
			</select><?php e(t("{1} members", '')) ?>
		</td>
	</tr>
	<tr>
		<td><?php e(t("Language")) ?></td>
		<td>
			<select name="language">
<?php foreach ( $dura['languages'] as $langcode => $language ): ?>
				<option value="<?php e($langcode) ?>"<?php if ( $langcode == Dura::user()->getLanguage() ) : ?> selected="selected"<?php endif ?>><?php e($language) ?></option>
<?php endforeach ?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?php e(t("Room Password")) ?></td>
		<td><input type="text" name="password" value="<?php echo $dura['input']['password'] ?>" size="20" maxlength="25" /></td>
	</tr>
<?php if ( Dura::user()->isAdmin() ) : ?>
	<tr>
		<td><?php e(t("Permanent Room")) ?></td>
		<td>
			<select name="permanent">
				<option value="0"<?php if ( $dura['input']['permanent'] == 0 ) : ?> selected="selected"<?php endif ?>><?php e(t("False")) ?></option>
				<option value="1"<?php if ( $dura['input']['permanent'] == 1 ) : ?> selected="selected"<?php endif ?>><?php e(t("True")) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?php e(t("Room Mark")) ?></td>
		<td>
			<select name="mark">
				<option value=""><?php e(t("N/A")) ?></option>
<?php foreach ( $dura['marks'] as $mark => $file ) : ?>
				<option value="<?php echo $mark ?>"<?php if ( $dura['input']['mark'] == $mark ) : ?> selected="selected"<?php endif ?>><?php echo ucfirst($mark) ?></option>
<?php endforeach ?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?php e(t("Room Color")) ?></td>
		<td><input type="text" name="color" value="<?php echo $dura['input']['color'] ?>" size="20" maxlength="10" /></td>
	</tr>
<?php endif ?>
<?php if ( DURA_USE_RECAPTCHA ) : ?>
	<tr>
		<td colspan="2"><div class="g-recaptcha" data-sitekey="<?php e(RECAPTCHA_PUBLIC_KEY) ?>"></div></td>
	</tr>
<?php endif ?>
	<tr>
		<td></td>
		<td>
			<input type="submit" name="submit" value="<?php e(t("CREATE!")) ?>" class="button" />
		</td>
	</tr>
</table>
</form>

<p><?php e(t("Up to {1} rooms can be created.", DURA_ROOM_LIMIT)) ?></p>

<a href="<?php e(Dura::url('lounge')) ?>" class="control"><?php e(t("Back")) ?></a>
</div><!-- /#container -->