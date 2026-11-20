<?php
/**
 * Mein Konto: eigene Einträge, Anmeldungen, Profil, Passwort, Daten herunterladen, Konto löschen.
 *
 * @package Spieltafel
 */

global $wpdb;
$user    = wp_get_current_user();
$uid     = $user->ID;
$labels  = spieltreff_status_labels();
$action  = admin_url( 'admin-post.php' );
$nonce   = function ( $do ) {
	echo '<input type="hidden" name="action" value="spieltreff_account">';
	printf( '<input type="hidden" name="do" value="%s">', esc_attr( $do ) );
	wp_nonce_field( 'spieltreff_account', '_st_nonce', false );
};
$own     = get_posts(
	array(
		'post_type'      => SPIELTREFF_TYPE,
		'author'         => $uid,
		'post_status'    => array( 'publish', 'pending' ),
		'posts_per_page' => 50,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);
$rows    = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . spieltreff_table() . ' WHERE user_id = %d ORDER BY id DESC', $uid ) ); // phpcs:ignore WordPress.DB
$region  = get_user_meta( $uid, '_st_kanton', true );
$is_team = user_can( $uid, 'edit_posts' );
?>
<div class="st-konto">
	<div class="st-konto-head">
		<div>
			<span class="eyebrow"><?php esc_html_e( 'Spieltreff', 'spieltafel' ); ?></span>
			<h1>
				<?php
				/* translators: %s: Anzeigename */
				echo esc_html( sprintf( __( 'Hallo %s', 'spieltafel' ), $user->display_name ) );
				?>
			</h1>
		</div>
		<div class="st-intro-actions">
			<a class="btn" href="<?php echo esc_url( spieltreff_url( 'neu' ) ); ?>"><?php esc_html_e( 'Neuer Eintrag', 'spieltafel' ); ?></a>
			<a class="btn btn-secondary" href="<?php echo esc_url( spieltreff_logout_url() ); ?>"><?php esc_html_e( 'Abmelden', 'spieltafel' ); ?></a>
		</div>
	</div>
	<?php spieltreff_notice(); ?>
	<?php if ( get_user_meta( $uid, '_st_verify', true ) ) : ?>
		<p class="hint"><?php esc_html_e( 'Bitte bestätige noch deine E-Mail-Adresse. Den Link findest du in unserer E-Mail.', 'spieltafel' ); ?> <a href="<?php echo esc_url( spieltreff_url( 'bestaetigen' ) ); ?>"><?php esc_html_e( 'Neuen Link anfordern', 'spieltafel' ); ?></a></p>
	<?php endif; ?>

	<div class="st-konto-grid">
		<section class="st-box">
			<h2><?php esc_html_e( 'Meine Einträge', 'spieltafel' ); ?></h2>
			<?php if ( ! $own ) : ?>
				<p><?php esc_html_e( 'Du hast noch keine Einträge.', 'spieltafel' ); ?></p>
			<?php else : ?>
				<ul class="st-mini">
					<?php
					foreach ( $own as $post ) :
						$e = spieltreff_entry( $post );
						$c = spieltreff_counts( $e['id'] );
						?>
						<li>
							<a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><strong><?php echo esc_html( $e['titel'] ); ?></strong></a>
							<span class="st-meta"><?php echo esc_html( spieltreff_kinds()[ $e['typ'] ] . ' · ' . spieltreff_when( $e, true ) ); ?></span>
							<span class="st-meta">
								<?php
								if ( 'pending' === $e['status'] ) {
									echo '<span class="st-badge st-badge-open">' . esc_html__( 'in Prüfung', 'spieltafel' ) . '</span> ';
								}
								if ( $e['abgesagt'] ) {
									echo '<span class="st-badge">' . esc_html__( 'abgesagt', 'spieltafel' ) . '</span> ';
								} elseif ( spieltreff_is_past( $e ) ) {
									echo '<span class="st-badge">' . ( 'gesuch' === $e['typ'] ? esc_html__( 'abgelaufen', 'spieltafel' ) : esc_html__( 'vorbei', 'spieltafel' ) ) . '</span> ';
								}
								/* translators: 1: bestätigt, 2: offen, 3: Warteliste */
								echo esc_html( sprintf( __( '%1$d bestätigt, %2$d offen, %3$d Warteliste', 'spieltafel' ), $c['bestaetigt'], $c['angefragt'], $c['warteliste'] ) );
								?>
							</span>
							<?php if ( 'gesuch' === $e['typ'] && ! $e['abgesagt'] && $e['sichtbar'] < time() + 14 * DAY_IN_SECONDS ) : ?>
								<form method="post" action="<?php echo esc_url( $action ); ?>">
									<input type="hidden" name="action" value="spieltreff_action">
									<input type="hidden" name="id" value="<?php echo (int) $e['id']; ?>">
									<input type="hidden" name="do" value="verlaengern">
									<?php wp_nonce_field( 'spieltreff_action_' . $e['id'], '_st_nonce', false ); ?>
									<button class="link-button" type="submit"><?php esc_html_e( 'Gesuch verlängern', 'spieltafel' ); ?></button>
								</form>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>

		<section class="st-box">
			<h2><?php esc_html_e( 'Meine Anmeldungen', 'spieltafel' ); ?></h2>
			<?php if ( ! $rows ) : ?>
				<p><?php esc_html_e( 'Du bist noch nirgends angemeldet.', 'spieltafel' ); ?> <a href="<?php echo esc_url( spieltreff_url() ); ?>"><?php esc_html_e( 'Einträge ansehen', 'spieltafel' ); ?></a></p>
			<?php else : ?>
				<ul class="st-mini">
					<?php
					foreach ( $rows as $row ) :
						$post = get_post( (int) $row->post_id );
						if ( ! $post ) {
							continue;
						}
						$e = spieltreff_entry( $post );
						?>
						<li>
							<a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><strong><?php echo esc_html( $e['titel'] ); ?></strong></a>
							<span class="st-meta"><?php echo esc_html( spieltreff_when( $e, true ) . ' · ' . spieltreff_where( $e ) ); ?></span>
							<span class="st-status st-status-<?php echo esc_attr( $row->status ); ?>"><?php echo esc_html( $e['abgesagt'] ? __( 'Abgesagt', 'spieltafel' ) : ( $labels[ $row->status ] ?? '' ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>

		<section class="st-box">
			<h2><?php esc_html_e( 'Profil', 'spieltafel' ); ?></h2>
			<form method="post" action="<?php echo esc_url( $action ); ?>">
				<?php $nonce( 'profil' ); ?>
				<div class="field">
					<label for="st-name"><?php esc_html_e( 'Anzeigename', 'spieltafel' ); ?></label>
					<input type="text" id="st-name" name="anzeigename" required minlength="3" maxlength="30" value="<?php echo esc_attr( $user->display_name ); ?>">
				</div>
				<div class="field">
					<label for="st-kanton"><?php esc_html_e( 'Region', 'spieltafel' ); ?></label>
					<select id="st-kanton" name="kanton">
						<option value=""><?php esc_html_e( 'Keine Angabe', 'spieltafel' ); ?></option>
						<?php foreach ( spieltreff_regions() as $code => $name ) : ?>
							<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $region, $code ); ?>><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<p class="st-small"><?php esc_html_e( 'E-Mail:', 'spieltafel' ); ?> <?php echo esc_html( $user->user_email ); ?></p>
				<button class="btn btn-secondary" type="submit"><?php esc_html_e( 'Speichern', 'spieltafel' ); ?></button>
			</form>
		</section>

		<section class="st-box" id="passwort">
			<h2><?php esc_html_e( 'Passwort ändern', 'spieltafel' ); ?></h2>
			<form method="post" action="<?php echo esc_url( $action ); ?>">
				<?php $nonce( 'passwort' ); ?>
				<div class="field">
					<label for="st-pw-alt"><?php esc_html_e( 'Aktuelles Passwort', 'spieltafel' ); ?></label>
					<input type="password" id="st-pw-alt" name="passwort" required autocomplete="current-password">
				</div>
				<div class="field">
					<label for="st-pw-neu"><?php esc_html_e( 'Neues Passwort', 'spieltafel' ); ?></label>
					<input type="password" id="st-pw-neu" name="neu" required minlength="10" autocomplete="new-password">
				</div>
				<button class="btn btn-secondary" type="submit"><?php esc_html_e( 'Passwort ändern', 'spieltafel' ); ?></button>
			</form>
		</section>

		<section class="st-box" id="loeschen">
			<h2><?php esc_html_e( 'Meine Daten', 'spieltafel' ); ?></h2>
			<p><?php esc_html_e( 'Du kannst alle Daten, die wir über dich gespeichert haben, herunterladen oder dein Konto mit allen Einträgen und Anmeldungen löschen.', 'spieltafel' ); ?></p>
			<form method="post" action="<?php echo esc_url( $action ); ?>">
				<?php $nonce( 'export' ); ?>
				<button class="btn btn-secondary" type="submit"><?php esc_html_e( 'Daten herunterladen (JSON)', 'spieltafel' ); ?></button>
			</form>
			<?php if ( ! $is_team ) : ?>
				<details class="st-delete">
					<summary><?php esc_html_e( 'Konto löschen', 'spieltafel' ); ?></summary>
					<form method="post" action="<?php echo esc_url( $action ); ?>" data-confirm="<?php esc_attr_e( 'Konto mit allen Daten endgültig löschen?', 'spieltafel' ); ?>">
						<?php $nonce( 'loeschen' ); ?>
						<p class="st-small"><?php esc_html_e( 'Deine Einträge werden gelöscht, Angemeldete bekommen eine Absage. Das lässt sich nicht rückgängig machen.', 'spieltafel' ); ?></p>
						<div class="field">
							<label for="st-pw-del"><?php esc_html_e( 'Zur Sicherheit dein Passwort', 'spieltafel' ); ?></label>
							<input type="password" id="st-pw-del" name="passwort" required autocomplete="current-password">
						</div>
						<button class="btn st-btn-danger" type="submit"><?php esc_html_e( 'Konto endgültig löschen', 'spieltafel' ); ?></button>
					</form>
				</details>
			<?php endif; ?>
		</section>
	</div>
</div>
