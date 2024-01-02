<div class="cf-cnt-box-body">
<p><?php printf( '%s', esc_html__( 'Multicollab defaults to English, syncs with the chosen WordPress Admin language, and supports other languages.', 'content-collaboration-inline-commenting' ) ); ?></p>
	<?php
	$lang          = get_bloginfo( 'language' );

	$cf_edd = new CF_EDD();
	if( $cf_edd->is_free() ) {
		$lang          = 'en-US';
	} else {
		$lang          = get_bloginfo( 'language' );
	}
	$supported_lan = array(
		'English' => 'en-US',
		'Chinese' => 'zh-CN',
		'Hindi'   => 'hi-IN',
		'Spanish' => 'es',
		'French'  => 'fr-FR',
		'Bengali' => 'bn-BD',
		'German'  => 'de-DE',
	);

	?>
	<ul>
		<?php
		foreach ( $supported_lan as $key => $val ) {
			?>
				<li><input type="checkbox" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $key ); ?>" disabled 
														  <?php
															if ( $lang === $val ) {
																echo esc_html( 'checked' ); }
															?>
				>
				<label for="<?php echo esc_attr( $key ); ?>">
					<?php
					if ( $lang === $val ) {
						?>
						<?php esc_html_e( $key, 'content-collaboration-inline-commenting' ); ?>
						<?php
					} else {
						esc_html_e( $key, 'content-collaboration-inline-commenting' );
					}
					?>
				</label></li>
				<?php
		}
		?>
	</ul>
</div>
