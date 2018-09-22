<div class="error">
	<p><?php echo WPPS_NAME; ?> error: Your environment doesn't meet all of the system requirements listed below.</p>

	<ul class="ul-disc">
		<li>
			<strong>PHP <?php echo WPF3_REQUIRED_PHP_VERSION; ?>+</strong>
			<em>(You're running php version <?php echo PHP_VERSION; ?>)</em>
		</li>

		<li>
			<strong>WordPress <?php echo WPF3_REQUIRED_WP_VERSION; ?>+</strong>
			<em>(You're running WordPress version <?php echo esc_html( $wp_version ); ?>)</em>
		</li>

        <li>
            <strong>WordPress <?php echo WPF3_REQUIRED_F3_VERSION; ?>+</strong>
            <em>(You're running FatFreeFramework version <?php echo esc_html( $f3_version ); ?>)</em>
        </li>
		<?php //<li><strong>Plugin XYZ</strong> activated</em></li> ?>
	</ul>

	<p>If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href="http://codex.wordpress.org/Upgrading_WordPress">the Codex</a>.To upgrade Fatfree simply download it from <a href="https://github.com/bcosca/fatfree/archive/master.zip">FatFreeFramework.com</a> and replace the /includes/f3/lib with the new directory.</p>
</div>
