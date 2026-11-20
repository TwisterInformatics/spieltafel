<?php
/**
 * Ein Spieltreff-Eintrag mit Anmeldung und, für den Ersteller, der Verwaltung.
 *
 * @package Spieltafel
 */

get_header();

if ( ! spieltreff_can_view() ) {
	get_template_part( 'template-parts/spieltreff/teaser', null, array( 'heading' => 'h1' ) );
	get_footer();
	return;
}

the_post();
$e       = spieltreff_entry( get_post() );
$uid     = get_current_user_id();
$is_host = $uid && ( $e['autor'] === $uid || current_user_can( 'edit_others_posts' ) );
$is_own  = $uid && $e['autor'] === $uid;
$row     = $uid ? spieltreff_row( $e['id'], $uid ) : null;
$mine    = $row ? $row->status : '';
$c       = spieltreff_counts( $e['id'] );
$free    = spieltreff_free_spots( $e );
$closed  = spieltreff_closed( $e );
$state   = spieltreff_state( $e );
$labels  = spieltreff_status_labels();
$kinds   = spieltreff_place_kinds();
$insider = $is_host || 'bestaetigt' === $mine;
$gesuch  = 'gesuch' === $e['typ'];
$action  = esc_url( admin_url( 'admin-post.php' ) );
$hidden  = function ( $do, $extra = array() ) use ( $e ) {
	echo '<input type="hidden" name="action" value="spieltreff_action">';
	printf( '<input type="hidden" name="id" value="%d">', (int) $e['id'] );
	printf( '<input type="hidden" name="do" value="%s">', esc_attr( $do ) );
	foreach ( $extra as $k => $v ) {
		printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $k ), esc_attr( $v ) );
	}
	wp_nonce_field( 'spieltreff_action_' . $e['id'], '_st_nonce', false );
};
?>
<section class="section">
	<div class="wrap st-single">
		<?php get_template_part( 'template-parts/spieltreff/testbanner' ); ?>
		<?php spieltreff_notice(); ?>
		<p><a href="<?php echo esc_url( spieltreff_url( '', $gesuch ? array( 'typ' => 'gesuch' ) : array() ) ); ?>">← <?php $gesuch ? esc_html_e( 'Alle Gesuche', 'spieltafel' ) : esc_html_e( 'Alle Angebote', 'spieltafel' ); ?></a></p>

		<?php if ( 'pending' === $e['status'] ) : ?>
			<div class="hint"><?php esc_html_e( 'Dieser Eintrag wird noch geprüft und ist erst für dich sichtbar.', 'spieltafel' ); ?></div>
		<?php endif; ?>
		<?php if ( $e['abgesagt'] ) : ?>
			<div class="notice notice-error"><?php esc_html_e( 'Abgesagt: Dieser Spieltreff findet nicht statt.', 'spieltafel' ); ?></div>
		<?php elseif ( spieltreff_is_past( $e ) ) : ?>
			<div class="hint"><?php $gesuch ? esc_html_e( 'Dieses Gesuch ist abgelaufen.', 'spieltafel' ) : esc_html_e( 'Dieser Spieltreff ist vorbei.', 'spieltafel' ); ?></div>
		<?php endif; ?>

		<div class="st-layout">
			<article class="st-main">
				<span class="eyebrow"><?php echo esc_html( spieltreff_kinds()[ $e['typ'] ] ); ?></span>
				<h1><?php echo esc_html( $e['titel'] ); ?></h1>
				<ul class="st-facts">
					<li><span><?php esc_html_e( 'Wann', 'spieltafel' ); ?></span><?php echo esc_html( spieltreff_when( $e ) ); ?></li>
					<li><span><?php esc_html_e( 'Wo', 'spieltafel' ); ?></span><?php echo esc_html( spieltreff_where( $e ) ); ?><?php echo isset( $kinds[ $e['ortsart'] ] ) && 'online' !== $e['ortsart'] ? ' · ' . esc_html( $kinds[ $e['ortsart'] ] ) : ''; ?></li>
					<li><span><?php esc_html_e( 'Spiele', 'spieltafel' ); ?></span>
						<span class="st-tags">
							<?php foreach ( spieltreff_game_names( $e, true ) as $g ) : ?>
								<?php if ( $g[1] ) : ?>
									<a class="st-tag" href="<?php echo esc_url( $g[1] ); ?>"><?php echo esc_html( $g[0] ); ?></a>
								<?php else : ?>
									<span class="st-tag"><?php echo esc_html( $g[0] ); ?></span>
								<?php endif; ?>
							<?php endforeach; ?>
						</span>
					</li>
					<li><span><?php $gesuch ? esc_html_e( 'Gruppe', 'spieltafel' ) : esc_html_e( 'Plätze', 'spieltafel' ); ?></span>
						<?php
						if ( $e['max'] ) {
							/* translators: 1: bestätigt, 2: Plätze */
							echo esc_html( sprintf( __( '%1$d von %2$d bestätigt', 'spieltafel' ), $c['bestaetigt'], $e['max'] ) );
						} else {
							/* translators: %d: bestätigt */
							echo esc_html( sprintf( __( 'offen, %d bestätigt', 'spieltafel' ), $c['bestaetigt'] ) );
						}
						if ( $c['warteliste'] ) {
							/* translators: %d: Anzahl auf der Warteliste */
							echo esc_html( sprintf( __( ', %d auf der Warteliste', 'spieltafel' ), $c['warteliste'] ) );
						}
						?>
					</li>
					<?php if ( $e['nur_bei_min'] ) : ?>
						<li><span><?php esc_html_e( 'Findet statt', 'spieltafel' ); ?></span>
							<?php
							if ( 'offen' === $state ) {
								/* translators: 1: Mindestanzahl, 2: fehlende */
								echo esc_html( sprintf( __( 'nur bei genügend Anmeldungen: mindestens %1$d Personen inkl. Ersteller, es fehlen noch %2$d', 'spieltafel' ), $e['min'], $e['min'] - $c['bestaetigt'] - 1 ) );
							} else {
								/* translators: %d: Mindestanzahl */
								echo esc_html( sprintf( __( 'ja, die Mindestanzahl von %d Personen ist erreicht', 'spieltafel' ), $e['min'] ) );
							}
							?>
						</li>
					<?php endif; ?>
					<?php if ( $e['schluss'] ) : ?>
						<li><span><?php esc_html_e( 'Anmeldeschluss', 'spieltafel' ); ?></span><?php echo esc_html( spieltreff_format_day( spieltreff_timestamp( $e['schluss'], '12:00' ) ) ); ?></li>
					<?php endif; ?>
					<li><span><?php esc_html_e( 'Von', 'spieltafel' ); ?></span><?php echo esc_html( spieltreff_display_name( $e['autor'] ) ); ?><?php echo $e['anfaenger'] ? ' · <span class="st-badge">' . esc_html__( 'Anfänger willkommen', 'spieltafel' ) . '</span>' : ''; ?></li>
				</ul>
				<?php if ( $e['text'] ) : ?>
					<div class="st-text"><?php echo wp_kses_post( wpautop( esc_html( $e['text'] ) ) ); ?></div>
				<?php endif; ?>

				<?php if ( $insider && ( $e['adresse'] || $e['kontakt'] || $c['bestaetigt'] ) ) : ?>
					<div class="st-private">
						<h2><?php esc_html_e( 'Nur für Teilnehmende', 'spieltafel' ); ?></h2>
						<?php if ( $e['adresse'] ) : ?>
							<p><strong><?php esc_html_e( 'Genauer Ort:', 'spieltafel' ); ?></strong><br><?php echo nl2br( esc_html( $e['adresse'] ) ); ?></p>
						<?php endif; ?>
						<?php if ( $e['kontakt'] ) : ?>
							<p><strong><?php esc_html_e( 'Kontakt und Hinweise:', 'spieltafel' ); ?></strong><br><?php echo nl2br( esc_html( $e['kontakt'] ) ); ?></p>
						<?php endif; ?>
						<?php
						$names = array( spieltreff_display_name( $e['autor'] ) . ' (' . __( 'Ersteller', 'spieltafel' ) . ')' );
						foreach ( spieltreff_rows( $e['id'], 'bestaetigt' ) as $r ) {
							$names[] = spieltreff_display_name( (int) $r->user_id );
						}
						?>
						<p><strong><?php esc_html_e( 'Dabei:', 'spieltafel' ); ?></strong> <?php echo esc_html( implode( ', ', $names ) ); ?></p>
						<?php if ( ! $gesuch && ! $e['abgesagt'] && ! spieltreff_is_past( $e ) ) : ?>
							<p><a href="<?php echo esc_url( spieltreff_ics_url( $e['id'] ) ); ?>">📅 <?php esc_html_e( 'In meinen Kalender', 'spieltafel' ); ?></a></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</article>

			<aside class="st-side" id="mitmachen">
				<div class="st-box">
					<?php if ( $is_own ) : ?>
						<h2><?php esc_html_e( 'Dein Eintrag', 'spieltafel' ); ?></h2>
						<p><?php esc_html_e( 'Unten verwaltest du die Anmeldungen.', 'spieltafel' ); ?></p>
						<p><a class="btn btn-secondary" href="<?php echo esc_url( spieltreff_url( 'neu', array( 'bearbeiten' => $e['id'] ) ) ); ?>"><?php esc_html_e( 'Bearbeiten', 'spieltafel' ); ?></a></p>
					<?php elseif ( ! $uid ) : ?>
						<h2><?php $gesuch ? esc_html_e( 'Interesse?', 'spieltafel' ) : esc_html_e( 'Mitspielen?', 'spieltafel' ); ?></h2>
						<p><?php esc_html_e( 'Melde dich an oder erstelle ein Konto. Das dauert eine Minute.', 'spieltafel' ); ?></p>
						<p><a class="btn" href="<?php echo esc_url( spieltreff_url( 'anmelden', array( 'zurueck' => rawurlencode( get_permalink() ) ) ) ); ?>"><?php esc_html_e( 'Anmelden', 'spieltafel' ); ?></a></p>
						<?php if ( spieltreff_registration_open() ) : ?>
							<p><a href="<?php echo esc_url( spieltreff_url( 'registrieren' ) ); ?>"><?php esc_html_e( 'Neu hier? Konto erstellen', 'spieltafel' ); ?></a></p>
						<?php endif; ?>
					<?php elseif ( $mine ) : ?>
						<h2><?php esc_html_e( 'Deine Anmeldung', 'spieltafel' ); ?></h2>
						<p class="st-status st-status-<?php echo esc_attr( $mine ); ?>"><?php echo esc_html( $labels[ $mine ] ); ?></p>
						<p>
							<?php
							$explain = array(
								'angefragt'  => __( 'Der Ersteller muss deine Anmeldung noch bestätigen. Du bekommst eine E-Mail.', 'spieltafel' ),
								'bestaetigt' => __( 'Du bist definitiv dabei.', 'spieltafel' ),
								'warteliste' => __( 'Sobald ein Platz frei wird, rückst du nach und bekommst eine E-Mail.', 'spieltafel' ),
								'abgelehnt'  => __( 'Diesmal hat es leider nicht geklappt.', 'spieltafel' ),
							);
							echo esc_html( $explain[ $mine ] );
							if ( 'bestaetigt' === $mine && 'offen' === $state ) {
								echo ' ' . esc_html__( 'Der Abend findet aber nur statt, wenn sich genügend Leute anmelden.', 'spieltafel' );
							}
							?>
						</p>
						<?php if ( 'abgelehnt' !== $mine && ! spieltreff_is_past( $e ) ) : ?>
							<form method="post" action="<?php echo $action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oben escaped. ?>" data-confirm="<?php esc_attr_e( 'Wirklich abmelden?', 'spieltafel' ); ?>">
								<?php $hidden( 'abmelden' ); ?>
								<button class="btn btn-secondary" type="submit"><?php esc_html_e( 'Abmelden', 'spieltafel' ); ?></button>
							</form>
						<?php endif; ?>
					<?php elseif ( $closed ) : ?>
						<h2><?php esc_html_e( 'Anmeldung geschlossen', 'spieltafel' ); ?></h2>
						<p><?php esc_html_e( 'Für diesen Eintrag kann man sich nicht mehr anmelden.', 'spieltafel' ); ?></p>
					<?php elseif ( ! spieltreff_user_ok() ) : ?>
						<h2><?php $gesuch ? esc_html_e( 'Interesse?', 'spieltafel' ) : esc_html_e( 'Mitspielen?', 'spieltafel' ); ?></h2>
						<p><?php esc_html_e( 'Dein Konto ist noch nicht bestätigt oder gesperrt.', 'spieltafel' ); ?></p>
					<?php else : ?>
						<h2><?php $gesuch ? esc_html_e( 'Interesse?', 'spieltafel' ) : esc_html_e( 'Mitspielen?', 'spieltafel' ); ?></h2>
						<p>
							<?php
							if ( $free <= 0 ) {
								esc_html_e( 'Alle Plätze sind vergeben. Du kannst dich auf die Warteliste setzen und rückst automatisch nach.', 'spieltafel' );
							} elseif ( $e['freigabe'] ) {
								esc_html_e( 'Der Ersteller bestätigt jede Anmeldung. Erst dann ist sie definitiv.', 'spieltafel' );
							} else {
								esc_html_e( 'Die Anmeldung ist sofort definitiv.', 'spieltafel' );
							}
							?>
						</p>
						<form method="post" action="<?php echo $action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
							<?php $hidden( 'anmelden' ); ?>
							<div class="field">
								<label for="st-nachricht"><?php esc_html_e( 'Nachricht an den Ersteller', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
								<textarea id="st-nachricht" name="nachricht" maxlength="1000" placeholder="<?php esc_attr_e( 'Zum Beispiel: Ich kenne Jass, Brändi Dog spiele ich zum ersten Mal.', 'spieltafel' ); ?>"></textarea>
							</div>
							<p class="st-small"><?php esc_html_e( 'Der Ersteller sieht deinen Anzeigenamen, deine Nachricht und deine E-Mail-Adresse, damit er dich erreichen kann.', 'spieltafel' ); ?></p>
							<button class="btn" type="submit">
								<?php
								if ( $free <= 0 ) {
									esc_html_e( 'Auf die Warteliste', 'spieltafel' );
								} elseif ( $gesuch ) {
									esc_html_e( 'Interesse melden', 'spieltafel' );
								} else {
									esc_html_e( 'Anmelden', 'spieltafel' );
								}
								?>
							</button>
						</form>
					<?php endif; ?>
				</div>
				<?php
				if ( spieltreff_shareable( $e ) ) {
					get_template_part( 'template-parts/spieltreff/share', null, array( 'entry' => $e ) );
				}
				?>
				<?php if ( $uid && ! $is_own && 'publish' === $e['status'] ) : ?>
					<details class="st-report">
						<summary><?php esc_html_e( 'Eintrag melden', 'spieltafel' ); ?></summary>
						<form method="post" action="<?php echo $action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
							<?php $hidden( 'melden' ); ?>
							<div class="field">
								<label for="st-grund"><?php esc_html_e( 'Grund', 'spieltafel' ); ?></label>
								<select id="st-grund" name="grund">
									<?php foreach ( spieltreff_report_reasons() as $reason ) : ?>
										<option><?php echo esc_html( $reason ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="field">
								<label for="st-melden-text"><?php esc_html_e( 'Was ist los?', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
								<textarea id="st-melden-text" name="text" maxlength="500"></textarea>
							</div>
							<button class="btn btn-secondary" type="submit"><?php esc_html_e( 'Melden', 'spieltafel' ); ?></button>
						</form>
					</details>
				<?php endif; ?>
			</aside>
		</div>

		<?php if ( $is_host ) : ?>
			<section class="st-manage" id="verwalten">
				<h2><?php esc_html_e( 'Anmeldungen verwalten', 'spieltafel' ); ?></h2>
				<?php
				$rows = spieltreff_rows( $e['id'] );
				if ( ! $rows ) :
					?>
					<p><?php esc_html_e( 'Noch keine Anmeldungen. Du bekommst eine E-Mail, sobald sich jemand meldet.', 'spieltafel' ); ?></p>
				<?php else : ?>
					<ul class="st-people">
						<?php
						foreach ( array( 'angefragt', 'bestaetigt', 'warteliste', 'abgelehnt' ) as $group ) :
							foreach ( $rows as $r ) :
								if ( $r->status !== $group ) {
									continue;
								}
								$person = get_userdata( (int) $r->user_id );
								?>
								<li class="st-person">
									<div>
										<strong><?php echo esc_html( $person ? $person->display_name : '?' ); ?></strong>
										<span class="st-status st-status-<?php echo esc_attr( $r->status ); ?>"><?php echo esc_html( $labels[ $r->status ] ?? '' ); ?></span>
										<?php if ( $person ) : ?>
											<a class="st-small" href="mailto:<?php echo esc_attr( $person->user_email ); ?>"><?php echo esc_html( $person->user_email ); ?></a>
										<?php endif; ?>
										<?php if ( $r->nachricht ) : ?>
											<p class="st-msg"><?php echo nl2br( esc_html( $r->nachricht ) ); ?></p>
										<?php endif; ?>
									</div>
									<div class="st-person-actions">
										<?php
										$choices = array(
											'bestaetigt' => __( 'Bestätigen', 'spieltafel' ),
											'warteliste' => __( 'Warteliste', 'spieltafel' ),
											'abgelehnt'  => __( 'Ablehnen', 'spieltafel' ),
										);
										foreach ( $choices as $to => $label ) :
											if ( $to === $r->status || ( 'bestaetigt' === $to && $free <= 0 ) || spieltreff_is_past( $e ) ) {
												continue;
											}
											?>
											<form method="post" action="<?php echo $action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
												<?php
												$hidden(
													'entscheiden',
													array(
														'row'    => $r->id,
														'status' => $to,
													)
												);
												?>
												<button class="btn btn-small<?php echo 'bestaetigt' === $to ? '' : ' btn-secondary'; ?>" type="submit"><?php echo esc_html( $label ); ?></button>
											</form>
										<?php endforeach; ?>
									</div>
								</li>
								<?php
							endforeach;
						endforeach;
						?>
					</ul>
				<?php endif; ?>

				<?php if ( ! $e['abgesagt'] && ! spieltreff_is_past( $e ) ) : ?>
					<div class="st-manage-grid">
						<?php if ( $c['bestaetigt'] + $c['angefragt'] + $c['warteliste'] ) : ?>
							<form class="st-box" method="post" action="<?php echo $action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
								<h3><?php esc_html_e( 'Nachricht an die Angemeldeten', 'spieltafel' ); ?></h3>
								<?php $hidden( 'nachricht' ); ?>
								<div class="field">
									<label for="st-an"><?php esc_html_e( 'An', 'spieltafel' ); ?></label>
									<select id="st-an" name="an">
										<option value="bestaetigt"><?php esc_html_e( 'Alle Bestätigten', 'spieltafel' ); ?></option>
										<option value="alle"><?php esc_html_e( 'Alle, auch Warteliste und offene Anfragen', 'spieltafel' ); ?></option>
									</select>
								</div>
								<div class="field">
									<label for="st-text"><?php esc_html_e( 'Nachricht', 'spieltafel' ); ?></label>
									<textarea id="st-text" name="text" maxlength="2000" required placeholder="<?php esc_attr_e( 'Zum Beispiel: Bitte bringt Getränke mit, Spiele habe ich genug.', 'spieltafel' ); ?>"></textarea>
									<small><?php esc_html_e( 'Wird per E-Mail verschickt. Antworten gehen an deine E-Mail-Adresse.', 'spieltafel' ); ?></small>
								</div>
								<button class="btn btn-secondary" type="submit"><?php esc_html_e( 'Senden', 'spieltafel' ); ?></button>
							</form>
						<?php endif; ?>
						<div class="st-box">
							<h3><?php esc_html_e( 'Eintrag', 'spieltafel' ); ?></h3>
							<p><a class="btn btn-secondary" href="<?php echo esc_url( spieltreff_url( 'neu', array( 'bearbeiten' => $e['id'] ) ) ); ?>"><?php esc_html_e( 'Bearbeiten', 'spieltafel' ); ?></a></p>
							<?php if ( $gesuch ) : ?>
								<form method="post" action="<?php echo $action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
									<?php $hidden( 'verlaengern' ); ?>
									<p class="st-small">
										<?php
										/* translators: %s: Datum */
										echo esc_html( sprintf( __( 'Sichtbar bis %s.', 'spieltafel' ), wp_date( 'j.n.Y', $e['sichtbar'] ) ) );
										?>
										<button class="link-button" type="submit"><?php esc_html_e( 'Verlängern', 'spieltafel' ); ?></button>
									</p>
								</form>
							<?php else : ?>
								<details>
									<summary><?php esc_html_e( 'Absagen', 'spieltafel' ); ?></summary>
									<form method="post" action="<?php echo $action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" data-confirm="<?php esc_attr_e( 'Wirklich absagen? Alle Angemeldeten bekommen eine E-Mail.', 'spieltafel' ); ?>">
										<?php $hidden( 'absagen' ); ?>
										<div class="field">
											<label for="st-absage"><?php esc_html_e( 'Grund', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
											<textarea id="st-absage" name="grund" maxlength="500" placeholder="<?php esc_attr_e( 'Zum Beispiel: Leider zu wenige Anmeldungen.', 'spieltafel' ); ?>"></textarea>
										</div>
										<button class="btn btn-secondary" type="submit"><?php esc_html_e( 'Spieltreff absagen', 'spieltafel' ); ?></button>
									</form>
								</details>
							<?php endif; ?>
							<form method="post" action="<?php echo $action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" data-confirm="<?php esc_attr_e( 'Eintrag mit allen Anmeldungen löschen?', 'spieltafel' ); ?>">
								<?php $hidden( 'loeschen' ); ?>
								<button class="link-button st-danger" type="submit"><?php esc_html_e( 'Eintrag löschen', 'spieltafel' ); ?></button>
							</form>
						</div>
					</div>
				<?php else : ?>
					<?php if ( $gesuch && ! $e['abgesagt'] ) : ?>
						<form method="post" action="<?php echo $action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
							<?php $hidden( 'verlaengern' ); ?>
							<button class="btn" type="submit"><?php esc_html_e( 'Gesuch wieder aktivieren', 'spieltafel' ); ?></button>
						</form>
					<?php endif; ?>
					<form method="post" action="<?php echo $action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" data-confirm="<?php esc_attr_e( 'Eintrag mit allen Anmeldungen löschen?', 'spieltafel' ); ?>">
						<?php $hidden( 'loeschen' ); ?>
						<button class="link-button st-danger" type="submit"><?php esc_html_e( 'Eintrag löschen', 'spieltafel' ); ?></button>
					</form>
				<?php endif; ?>
			</section>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
