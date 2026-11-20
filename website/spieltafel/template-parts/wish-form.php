<?php
/**
 * Formular für Spielwünsche.
 *
 * @package Spieltafel
 */

$privacy = get_privacy_policy_url();
?>
<form class="wish-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php spieltafel_wish_notice(); ?>
	<input type="hidden" name="action" value="spieltafel_wunsch">
	<input type="hidden" name="t" value="<?php echo esc_attr( time() ); ?>">
	<?php wp_nonce_field( 'spieltafel_wunsch', '_spieltafel_nonce', false ); ?>
	<div class="hp-field" aria-hidden="true">
		<label for="wunsch-website">Website</label>
		<input type="text" id="wunsch-website" name="website" tabindex="-1" autocomplete="off">
	</div>

	<div class="field-row">
		<div class="field">
			<label for="wunsch-spiel"><?php esc_html_e( 'Welches Spiel?', 'spieltafel' ); ?></label>
			<input type="text" id="wunsch-spiel" name="spiel" required maxlength="120" placeholder="<?php esc_attr_e( 'zum Beispiel Yatzy', 'spieltafel' ); ?>">
		</div>
		<div class="field">
			<label for="wunsch-art"><?php esc_html_e( 'Art', 'spieltafel' ); ?></label>
			<select id="wunsch-art" name="art">
				<?php foreach ( spieltafel_wish_kinds() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div class="field">
		<label for="wunsch-zaehlung"><?php esc_html_e( 'Wie wird gezählt?', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
		<textarea id="wunsch-zaehlung" name="zaehlung" maxlength="3000" placeholder="<?php esc_attr_e( 'Wer bekommt wann Punkte, wann ist Schluss, wer gewinnt? Gerne auch eure Hausregeln.', 'spieltafel' ); ?>"></textarea>
	</div>
	<div class="field">
		<label for="wunsch-link"><?php esc_html_e( 'Link zur Anleitung', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
		<input type="url" id="wunsch-link" name="link" placeholder="https://">
	</div>
	<div class="field-row">
		<div class="field">
			<label for="wunsch-name"><?php esc_html_e( 'Dein Name', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
			<input type="text" id="wunsch-name" name="name" maxlength="120" autocomplete="name">
		</div>
		<div class="field">
			<label for="wunsch-email"><?php esc_html_e( 'E-Mail', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
			<input type="email" id="wunsch-email" name="email" autocomplete="email">
			<small><?php esc_html_e( 'Nur falls wir nachfragen oder dir Bescheid geben sollen.', 'spieltafel' ); ?></small>
		</div>
	</div>
	<div class="field field-check">
		<label>
			<input type="checkbox" name="datenschutz" value="1" required>
			<span>
				<?php esc_html_e( 'Ich bin einverstanden, dass meine Angaben gespeichert werden, um die App zu erweitern.', 'spieltafel' ); ?>
				<?php if ( $privacy ) : ?>
					<a href="<?php echo esc_url( $privacy ); ?>"><?php esc_html_e( 'Datenschutz', 'spieltafel' ); ?></a>
				<?php endif; ?>
			</span>
		</label>
	</div>
	<?php spieltreff_pow_field(); ?>
	<button class="btn" type="submit" data-needs-pow><?php esc_html_e( 'Spiel wünschen', 'spieltafel' ); ?></button>
</form>
