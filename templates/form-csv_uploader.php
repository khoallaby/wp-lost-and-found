<?php if (isset($_GET['settings-updated'])) : ?>
	<div class="notice notice-success is-dismissible"><p><?php _e('CSV uploaded'); ?>.</p></div>
<?php endif; ?>
<div class="wrap">
	<form method="post" action="options.php" enctype="multipart/form-data">
		<?php
		settings_fields("section");
		do_settings_sections("laf");
		submit_button();
		?>
	</form>
</div>
