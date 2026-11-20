<?php
/**
 * Angebot oder Gesuch erstellen und bearbeiten.
 *
 * @package Spieltafel
 */

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- nur Anzeige.
$edit_id = isset( $_GET['bearbeiten'] ) ? (int) $_GET['bearbeiten'] : 0;
$typ     = isset( $_GET['typ'] ) && 'gesuch' === $_GET['typ'] ? 'gesuch' : 'angebot';
// phpcs:enable
$e = null;
if ( $edit_id ) {
	$post = get_post( $edit_id );
	if ( ! $post || SPIELTREFF_TYPE !== $post->post_type || ( (int) $post->post_author !== get_current_user_id() && ! current_user_can( 'edit_others_posts' ) ) ) {
		echo '<p>' . esc_html__( 'Diesen Eintrag kannst du nicht bearbeiten.', 'spieltafel' ) . '</p>';
		return;
	}
	$e   = spieltreff_entry( $post );
	$typ = $e['typ'];
}
$v = function ( $key, $default = '' ) use ( $e ) {
	return $e ? $e[ $key ] : $default;
};
$user_region = get_user_meta( get_current_user_id(), '_st_kanton', true );
$gesuch      = 'gesuch' === $typ;
$picked      = $v( 'spiele', array() );
?>
<div class="st-form-wrap">
	<span class="eyebrow"><?php esc_html_e( 'Spieltreff', 'spieltafel' ); ?></span>
	<h1>
		<?php
		if ( $e ) {
			esc_html_e( 'Eintrag bearbeiten', 'spieltafel' );
		} else {
			$gesuch ? esc_html_e( 'Mitspieler suchen', 'spieltafel' ) : esc_html_e( 'Spielabend anbieten', 'spieltafel' );
		}
		?>
	</h1>

	<?php if ( ! spieltreff_user_ok() ) : ?>
		<p class="hint"><?php esc_html_e( 'Bitte bestätige zuerst deine E-Mail-Adresse.', 'spieltafel' ); ?></p>
		<?php
		return;
	endif;
	?>

	<?php if ( ! $e ) : ?>
		<nav class="st-tabs" aria-label="<?php esc_attr_e( 'Art des Eintrags', 'spieltafel' ); ?>">
			<a href="<?php echo esc_url( spieltreff_url( 'neu', array( 'typ' => 'angebot' ) ) ); ?>" <?php echo $gesuch ? '' : 'aria-current="page"'; ?>><?php esc_html_e( 'Angebot: konkreter Termin', 'spieltafel' ); ?></a>
			<a href="<?php echo esc_url( spieltreff_url( 'neu', array( 'typ' => 'gesuch' ) ) ); ?>" <?php echo $gesuch ? 'aria-current="page"' : ''; ?>><?php esc_html_e( 'Gesuch: Gruppe oder Mitspieler', 'spieltafel' ); ?></a>
		</nav>
	<?php endif; ?>

	<form class="wish-form st-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php spieltreff_notice(); ?>
		<input type="hidden" name="action" value="spieltreff_save">
		<input type="hidden" name="typ" value="<?php echo esc_attr( $typ ); ?>">
		<input type="hidden" name="id" value="<?php echo (int) $edit_id; ?>">
		<?php wp_nonce_field( 'spieltreff_save', '_st_nonce', false ); ?>

		<fieldset>
			<legend><?php esc_html_e( 'Worum geht es?', 'spieltafel' ); ?></legend>
			<div class="field">
				<label for="st-titel"><?php esc_html_e( 'Titel', 'spieltafel' ); ?></label>
				<input type="text" id="st-titel" name="titel" required minlength="5" maxlength="80" value="<?php echo esc_attr( $v( 'titel' ) ); ?>" placeholder="<?php echo $gesuch ? esc_attr__( 'zum Beispiel: Suche Gruppe für Brändi Dog', 'spieltafel' ) : esc_attr__( 'zum Beispiel: Brettspielabend in Winterthur', 'spieltafel' ); ?>">
			</div>
			<div class="field">
				<label for="st-text"><?php esc_html_e( 'Beschreibung', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
				<textarea id="st-text" name="text" maxlength="2000" placeholder="<?php esc_attr_e( 'Wer seid ihr, wie läuft der Abend, was soll man mitbringen, gibt es einen Unkostenbeitrag?', 'spieltafel' ); ?>"><?php echo esc_textarea( $v( 'text' ) ); ?></textarea>
			</div>
		</fieldset>

		<fieldset>
			<legend><?php esc_html_e( 'Welche Spiele?', 'spieltafel' ); ?></legend>
			<div class="st-games" data-game-picker>
				<input class="game-search" type="search" placeholder="<?php esc_attr_e( 'Spiel suchen', 'spieltafel' ); ?>" data-picker-search hidden>
				<div class="st-games-list">
					<?php foreach ( spieltafel_games() as $game ) : ?>
						<label class="st-game" data-search="<?php echo esc_attr( strtolower( remove_accents( $game['name'] . ' ' . implode( ' ', $game['keywords'] ?? array() ) ) ) ); ?>">
							<input type="checkbox" name="spiele[]" value="<?php echo esc_attr( $game['id'] ); ?>" <?php checked( in_array( $game['id'], $picked, true ) ); ?>>
							<span><?php echo esc_html( $game['name'] ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="field">
				<label for="st-frei"><?php esc_html_e( 'Andere Spiele', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(mit Komma getrennt)', 'spieltafel' ); ?></span></label>
				<input type="text" id="st-frei" name="spiele_frei" maxlength="200" value="<?php echo esc_attr( $v( 'spiele_frei' ) ); ?>" placeholder="<?php esc_attr_e( 'zum Beispiel Brändi Dog, Catan, Azul', 'spieltafel' ); ?>">
				<small><?php esc_html_e( 'Mindestens ein Spiel aus der Liste oder hier eingeben.', 'spieltafel' ); ?></small>
			</div>
		</fieldset>

		<fieldset>
			<legend><?php esc_html_e( 'Wann?', 'spieltafel' ); ?></legend>
			<?php if ( $gesuch ) : ?>
				<div class="field">
					<label for="st-wann"><?php esc_html_e( 'Wann ungefähr?', 'spieltafel' ); ?></label>
					<input type="text" id="st-wann" name="wann" required maxlength="80" value="<?php echo esc_attr( $v( 'wann' ) ); ?>" placeholder="<?php esc_attr_e( 'zum Beispiel: jeden Donnerstagabend', 'spieltafel' ); ?>">
				</div>
			<?php else : ?>
				<div class="field-row field-row-3">
					<div class="field">
						<label for="st-datum"><?php esc_html_e( 'Datum', 'spieltafel' ); ?></label>
						<input type="date" id="st-datum" name="datum" required min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" max="<?php echo esc_attr( wp_date( 'Y-m-d', time() + YEAR_IN_SECONDS ) ); ?>" value="<?php echo esc_attr( $v( 'datum' ) ); ?>">
					</div>
					<div class="field">
						<label for="st-zeit"><?php esc_html_e( 'Beginn', 'spieltafel' ); ?></label>
						<input type="time" id="st-zeit" name="zeit" required value="<?php echo esc_attr( $v( 'zeit', '19:00' ) ); ?>">
					</div>
					<div class="field">
						<label for="st-ende"><?php esc_html_e( 'Ende', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
						<input type="time" id="st-ende" name="ende" value="<?php echo esc_attr( $v( 'ende' ) ); ?>">
					</div>
				</div>
			<?php endif; ?>
		</fieldset>

		<fieldset>
			<legend><?php esc_html_e( 'Wo?', 'spieltafel' ); ?></legend>
			<div class="field-row field-row-3">
				<div class="field">
					<label for="st-plz"><?php esc_html_e( 'PLZ', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
					<input type="text" id="st-plz" name="plz" inputmode="numeric" pattern="\d{4}" maxlength="4" value="<?php echo esc_attr( $v( 'plz' ) ); ?>">
				</div>
				<div class="field">
					<label for="st-ort"><?php esc_html_e( 'Ort oder Quartier', 'spieltafel' ); ?></label>
					<input type="text" id="st-ort" name="ort" required maxlength="60" value="<?php echo esc_attr( $v( 'ort' ) ); ?>" placeholder="<?php esc_attr_e( 'zum Beispiel Bern Breitenrain', 'spieltafel' ); ?>">
				</div>
				<div class="field">
					<label for="st-kanton"><?php esc_html_e( 'Kanton', 'spieltafel' ); ?></label>
					<select id="st-kanton" name="kanton" required>
						<option value=""><?php esc_html_e( 'Bitte wählen', 'spieltafel' ); ?></option>
						<?php foreach ( spieltreff_regions() as $code => $name ) : ?>
							<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $v( 'kanton', $user_region ), $code ); ?>><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="field">
				<label for="st-ortsart"><?php esc_html_e( 'Art des Orts', 'spieltafel' ); ?></label>
				<select id="st-ortsart" name="ortsart">
					<?php foreach ( spieltreff_place_kinds() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $v( 'ortsart', 'privat' ), $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="field">
				<label for="st-adresse"><?php esc_html_e( 'Genaue Adresse oder Link', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig, nur für Bestätigte sichtbar)', 'spieltafel' ); ?></span></label>
				<textarea id="st-adresse" name="adresse" maxlength="300" rows="2" placeholder="<?php esc_attr_e( 'Strasse, Hausnummer, Klingel, oder der Link zum Online-Treffen', 'spieltafel' ); ?>"><?php echo esc_textarea( $v( 'adresse' ) ); ?></textarea>
				<small>🔒 <?php esc_html_e( 'Öffentlich sichtbar sind nur PLZ, Ort und Kanton.', 'spieltafel' ); ?></small>
			</div>
			<div class="field">
				<label for="st-kontakt"><?php esc_html_e( 'Kontakt und Hinweise für Bestätigte', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
				<textarea id="st-kontakt" name="kontakt" maxlength="300" rows="2" placeholder="<?php esc_attr_e( 'zum Beispiel Handynummer für kurzfristige Fragen', 'spieltafel' ); ?>"><?php echo esc_textarea( $v( 'kontakt' ) ); ?></textarea>
			</div>
		</fieldset>

		<fieldset>
			<legend><?php esc_html_e( 'Wer kann mitmachen?', 'spieltafel' ); ?></legend>
			<div class="field-row">
				<div class="field">
					<label for="st-max"><?php $gesuch ? esc_html_e( 'Wie viele suchst du?', 'spieltafel' ) : esc_html_e( 'Plätze (ohne dich)', 'spieltafel' ); ?><?php if ( $gesuch ) : ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span><?php endif; ?></label>
					<input type="number" id="st-max" name="max" min="<?php echo $gesuch ? 0 : 1; ?>" max="99" <?php echo $gesuch ? '' : 'required'; ?> value="<?php echo esc_attr( $v( 'max', $gesuch ? '' : 4 ) ? $v( 'max', $gesuch ? '' : 4 ) : '' ); ?>">
					<small><?php $gesuch ? esc_html_e( 'Leer lassen, wenn offen.', 'spieltafel' ) : esc_html_e( 'Sind alle Plätze bestätigt, kommen weitere auf die Warteliste.', 'spieltafel' ); ?></small>
				</div>
				<?php if ( ! $gesuch ) : ?>
					<div class="field">
						<label for="st-schluss"><?php esc_html_e( 'Anmeldeschluss', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
						<input type="date" id="st-schluss" name="schluss" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" value="<?php echo esc_attr( $v( 'schluss' ) ); ?>">
					</div>
				<?php endif; ?>
			</div>
			<?php if ( ! $gesuch ) : ?>
				<div class="field field-check">
					<label>
						<input type="checkbox" name="nur_bei_min" value="1" data-toggle="#st-min-wrap" <?php checked( $v( 'nur_bei_min' ) ); ?>>
						<span><?php esc_html_e( 'Findet nur statt bei genügend Anmeldungen', 'spieltafel' ); ?></span>
					</label>
				</div>
				<div class="field" id="st-min-wrap">
					<label for="st-min"><?php esc_html_e( 'Mindestens so viele Personen, dich eingerechnet', 'spieltafel' ); ?></label>
					<input type="number" id="st-min" name="min" min="2" max="99" value="<?php echo esc_attr( $v( 'min' ) ? $v( 'min' ) : 4 ); ?>">
					<small><?php esc_html_e( 'Bis die Zahl erreicht ist, steht beim Eintrag «nur bei genügend Anmeldungen». Kommen zu wenige zusammen, sag den Abend ab, dann bekommen alle eine E-Mail.', 'spieltafel' ); ?></small>
				</div>
			<?php endif; ?>
			<div class="field field-check">
				<label>
					<input type="checkbox" name="freigabe" value="1" <?php checked( $e ? $e['freigabe'] : true ); ?>>
					<span><?php esc_html_e( 'Ich bestätige jede Anmeldung selbst', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(empfohlen; sonst ist jede Anmeldung sofort definitiv)', 'spieltafel' ); ?></span></span>
				</label>
			</div>
			<div class="field field-check">
				<label>
					<input type="checkbox" name="anfaenger" value="1" <?php checked( $v( 'anfaenger' ) ); ?>>
					<span><?php esc_html_e( 'Anfänger willkommen, Regeln werden erklärt', 'spieltafel' ); ?></span>
				</label>
			</div>
		</fieldset>

		<p class="st-small">
			<?php esc_html_e( 'Mit dem Veröffentlichen bestätigst du, dass dein Eintrag den', 'spieltafel' ); ?>
			<a href="<?php echo esc_url( spieltreff_rules_url() ); ?>" target="_blank"><?php esc_html_e( 'Regeln', 'spieltafel' ); ?></a>
			<?php esc_html_e( 'entspricht (zum Beispiel kein Glücksspiel um Geld).', 'spieltafel' ); ?>
		</p>
		<button class="btn" type="submit"><?php $e ? esc_html_e( 'Speichern', 'spieltafel' ) : esc_html_e( 'Veröffentlichen', 'spieltafel' ); ?></button>
		<?php if ( $e ) : ?>
			<a class="btn btn-secondary" href="<?php echo esc_url( get_permalink( $e['id'] ) ); ?>"><?php esc_html_e( 'Abbrechen', 'spieltafel' ); ?></a>
		<?php endif; ?>
	</form>
</div>
