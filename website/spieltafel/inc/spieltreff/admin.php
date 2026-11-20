<?php
/**
 * Spieltreff im Backend: Einstellungen (Freischaltung), Übersicht der Einträge,
 * Meldungen, Konten sperren und Text für die Datenschutzerklärung.
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

/* Einstellungen: Spieltreff → Einstellungen */

add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'edit.php?post_type=' . SPIELTREFF_TYPE,
			__( 'Spieltreff-Einstellungen', 'spieltafel' ),
			__( 'Einstellungen', 'spieltafel' ),
			'manage_options',
			'spieltreff-einstellungen',
			'spieltreff_settings_page'
		);

		// Zahl der Einträge in Prüfung im Menü.
		global $menu;
		$pending = (int) wp_count_posts( SPIELTREFF_TYPE )->pending;
		if ( $pending ) {
			foreach ( $menu as $i => $item ) {
				if ( 'edit.php?post_type=' . SPIELTREFF_TYPE === $item[2] ) {
					$menu[ $i ][0] .= sprintf( ' <span class="awaiting-mod">%d</span>', $pending ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				}
			}
		}
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'spieltreff',
			'spieltafel_spieltreff',
			array(
				'type'              => 'array',
				'sanitize_callback' => function ( $in ) {
					$in = is_array( $in ) ? $in : array();
					return array(
						'live'          => ! empty( $in['live'] ),
						'register_test' => ! empty( $in['register_test'] ),
						'moderation'    => in_array( $in['moderation'] ?? '', array( 'keine', 'erster', 'alle' ), true ) ? $in['moderation'] : 'erster',
						'report_limit'  => max( 1, min( 20, (int) ( $in['report_limit'] ?? 3 ) ) ),
						'keep_days'     => max( 1, min( 365, (int) ( $in['keep_days'] ?? 30 ) ) ),
						'gesuch_days'   => max( 7, min( 365, (int) ( $in['gesuch_days'] ?? 90 ) ) ),
						'min_age'       => max( 0, min( 18, (int) ( $in['min_age'] ?? 16 ) ) ),
						'notify_email'  => sanitize_email( $in['notify_email'] ?? '' ),
						'notify_new'    => ! empty( $in['notify_new'] ),
						'teaser_text'   => sanitize_textarea_field( $in['teaser_text'] ?? '' ),
					);
				},
			)
		);
	}
);

function spieltreff_settings_page() {
	$s    = spieltreff_settings();
	$name = 'spieltafel_spieltreff';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Spieltreff-Einstellungen', 'spieltafel' ); ?></h1>
		<?php if ( ! $s['live'] ) : ?>
			<div class="notice notice-info inline"><p>
				<?php esc_html_e( 'Der Spieltreff ist noch im Testbetrieb: Besucher sehen nur einen Hinweis, angemeldete Benutzer können ihn schon nutzen. Testkonten legst du unter Benutzer → Neu hinzufügen mit der Rolle «Spieler» an.', 'spieltafel' ); ?>
			</p></div>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'spieltreff' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Freischaltung', 'spieltafel' ); ?></th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[live]" value="1" <?php checked( $s['live'] ); ?>> <strong><?php esc_html_e( 'Spieltreff für alle freischalten', 'spieltafel' ); ?></strong></label>
						<p class="description"><?php esc_html_e( 'Danach sehen alle Besucher die Einträge und können sich registrieren.', 'spieltafel' ); ?></p>
						<p><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[register_test]" value="1" <?php checked( $s['register_test'] ); ?>> <?php esc_html_e( 'Registrierung schon vor der Freischaltung öffnen (zum Testen)', 'spieltafel' ); ?></label></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Hinweis vor dem Start', 'spieltafel' ); ?></th>
					<td>
						<textarea class="large-text" rows="3" name="<?php echo esc_attr( $name ); ?>[teaser_text]" placeholder="<?php echo esc_attr( spieltreff_default_teaser() ); ?>"><?php echo esc_textarea( $s['teaser_text'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Leer lassen für den Standardtext.', 'spieltafel' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Prüfung neuer Einträge', 'spieltafel' ); ?></th>
					<td>
						<?php
						$modes = array(
							'keine'  => __( 'Keine: Einträge erscheinen sofort', 'spieltafel' ),
							'erster' => __( 'Erster Eintrag jeder Person wird geprüft (empfohlen)', 'spieltafel' ),
							'alle'   => __( 'Alle Einträge und Änderungen werden geprüft', 'spieltafel' ),
						);
						foreach ( $modes as $value => $label ) :
							?>
							<label style="display:block;margin-bottom:4px"><input type="radio" name="<?php echo esc_attr( $name ); ?>[moderation]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $s['moderation'], $value ); ?>> <?php echo esc_html( $label ); ?></label>
						<?php endforeach; ?>
						<p class="description"><?php esc_html_e( 'Einträge in Prüfung findest du unter Spieltreff → Alle Einträge → Ausstehend. Mit «Veröffentlichen» gibst du sie frei.', 'spieltafel' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="st-report"><?php esc_html_e( 'Ausblenden nach Meldungen', 'spieltafel' ); ?></label></th>
					<td><input id="st-report" type="number" min="1" max="20" name="<?php echo esc_attr( $name ); ?>[report_limit]" value="<?php echo esc_attr( $s['report_limit'] ); ?>" class="small-text"> <?php esc_html_e( 'Meldungen von verschiedenen Personen blenden einen Eintrag aus, bis du ihn prüfst.', 'spieltafel' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="st-keep"><?php esc_html_e( 'Aufbewahrung', 'spieltafel' ); ?></label></th>
					<td>
						<?php
						printf(
							/* translators: 1: Eingabefeld Tage, 2: Eingabefeld Tage */
							esc_html__( 'Vergangene Einträge %1$s Tage nach dem Termin löschen. Gesuche laufen nach %2$s Tagen ab (verlängerbar).', 'spieltafel' ),
							'<input id="st-keep" type="number" min="1" max="365" class="small-text" name="' . esc_attr( $name ) . '[keep_days]" value="' . esc_attr( $s['keep_days'] ) . '">',
							'<input type="number" min="7" max="365" class="small-text" name="' . esc_attr( $name ) . '[gesuch_days]" value="' . esc_attr( $s['gesuch_days'] ) . '">'
						);
						?>
						<p class="description"><?php esc_html_e( 'Unbestätigte Konten werden nach 7 Tagen gelöscht.', 'spieltafel' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="st-age"><?php esc_html_e( 'Mindestalter', 'spieltafel' ); ?></label></th>
					<td><input id="st-age" type="number" min="0" max="18" class="small-text" name="<?php echo esc_attr( $name ); ?>[min_age]" value="<?php echo esc_attr( $s['min_age'] ); ?>"> <?php esc_html_e( 'Jahre (wird bei der Registrierung bestätigt)', 'spieltafel' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="st-mail"><?php esc_html_e( 'E-Mail der Moderation', 'spieltafel' ); ?></label></th>
					<td>
						<input id="st-mail" type="email" class="regular-text" name="<?php echo esc_attr( $name ); ?>[notify_email]" value="<?php echo esc_attr( $s['notify_email'] ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
						<p><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[notify_new]" value="1" <?php checked( $s['notify_new'] ); ?>> <?php esc_html_e( 'Auch bei jedem neuen, sofort veröffentlichten Eintrag eine E-Mail', 'spieltafel' ); ?></label></p>
						<p class="description"><?php esc_html_e( 'Absender der E-Mails wie bei den Spielwünschen: Design → Customizer → Spieltafel.', 'spieltafel' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<h2><?php esc_html_e( 'Seiten', 'spieltafel' ); ?></h2>
		<ul>
			<li><a href="<?php echo esc_url( spieltreff_url() ); ?>"><?php esc_html_e( 'Spieltreff ansehen', 'spieltafel' ); ?></a></li>
			<li><a href="<?php echo esc_url( spieltreff_rules_url() ); ?>"><?php esc_html_e( 'Regeln und Nutzungsbedingungen (bitte prüfen und anpassen)', 'spieltafel' ); ?></a></li>
			<li><a href="<?php echo esc_url( admin_url( 'options-privacy.php?tab=policyguide' ) ); ?>"><?php esc_html_e( 'Textvorschlag für die Datenschutzerklärung', 'spieltafel' ); ?></a></li>
		</ul>
		<h2><?php esc_html_e( 'Bilder für Social Media', 'spieltafel' ); ?></h2>
		<p><?php esc_html_e( 'Fertige Bilder zur Kampagne «Zurück an den Tisch» zum Posten auf Instagram, Facebook oder WhatsApp. Die Adresse der Webseite steht schon drauf. Vor der Freischaltung mit «Bald neu», danach mit «Jetzt mitspielen».', 'spieltafel' ); ?></p>
		<?php
		$formats = spieltreff_campaign_formats();
		foreach ( array( 'bald' => __( 'Vor dem Start', 'spieltafel' ), 'live' => __( 'Nach der Freischaltung', 'spieltafel' ) ) as $phase => $phase_label ) :
			?>
			<h3><?php echo esc_html( $phase_label ); ?></h3>
			<div style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-start">
				<?php foreach ( spieltreff_slogans() as $nr => $slogan ) : ?>
					<div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:10px;width:240px">
						<img src="<?php echo esc_url( spieltreff_campaign_url( 'post', $nr, $phase ) ); ?>" alt="" width="220" height="220" loading="lazy" style="display:block;border-radius:4px">
						<p style="margin:8px 0 4px"><strong><?php echo esc_html( $slogan ); ?></strong></p>
						<?php foreach ( $formats as $f => $info ) : ?>
							<a href="<?php echo esc_url( add_query_arg( 'dl', 1, spieltreff_campaign_url( $f, $nr, $phase ) ) ); ?>"><?php echo esc_html( $info[2] ); ?></a><br>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/** Nach dem Speichern die Adressen neu einlesen. */
add_action( 'update_option_spieltafel_spieltreff', 'flush_rewrite_rules' );

/* Übersicht der Einträge */

add_filter(
	'manage_' . SPIELTREFF_TYPE . '_posts_columns',
	function () {
		return array(
			'cb'        => '<input type="checkbox" />',
			'title'     => __( 'Titel', 'spieltafel' ),
			'st_typ'    => __( 'Typ', 'spieltafel' ),
			'st_wann'   => __( 'Wann', 'spieltafel' ),
			'st_ort'    => __( 'Ort', 'spieltafel' ),
			'st_teil'   => __( 'Anmeldungen', 'spieltafel' ),
			'st_melden' => __( 'Meldungen', 'spieltafel' ),
			'author'    => __( 'Von', 'spieltafel' ),
			'date'      => __( 'Erstellt', 'spieltafel' ),
		);
	}
);

add_action(
	'manage_' . SPIELTREFF_TYPE . '_posts_custom_column',
	function ( $column, $post_id ) {
		$e = spieltreff_entry( $post_id );
		switch ( $column ) {
			case 'st_typ':
				echo esc_html( spieltreff_kinds()[ $e['typ'] ] );
				if ( $e['abgesagt'] ) {
					echo ' · <strong>' . esc_html__( 'abgesagt', 'spieltafel' ) . '</strong>';
				}
				break;
			case 'st_wann':
				echo esc_html( spieltreff_when( $e, true ) );
				break;
			case 'st_ort':
				echo esc_html( spieltreff_where( $e ) );
				break;
			case 'st_teil':
				$c = spieltreff_counts( $post_id );
				/* translators: 1: bestätigt, 2: Plätze, 3: angefragt, 4: Warteliste */
				echo esc_html( sprintf( __( '%1$d/%2$s bestätigt, %3$d offen, %4$d Warteliste', 'spieltafel' ), $c['bestaetigt'], $e['max'] ? $e['max'] : '∞', $c['angefragt'], $c['warteliste'] ) );
				break;
			case 'st_melden':
				$n = count( get_post_meta( $post_id, '_st_meldung' ) );
				echo $n ? '<strong style="color:#b32d2e">' . (int) $n . '</strong>' : '–';
				break;
		}
	},
	10,
	2
);

/** Angaben, Anmeldungen und Meldungen im Eintrag. */
add_action(
	'add_meta_boxes_' . SPIELTREFF_TYPE,
	function () {
		add_meta_box(
			'spieltreff_details',
			__( 'Angaben', 'spieltafel' ),
			function ( $post ) {
				$e      = spieltreff_entry( $post );
				$kinds  = spieltreff_place_kinds();
				$labels = spieltreff_status_labels();
				$rows   = array(
					__( 'Typ', 'spieltafel' )            => spieltreff_kinds()[ $e['typ'] ],
					__( 'Wann', 'spieltafel' )           => spieltreff_when( $e ),
					__( 'Ort', 'spieltafel' )            => spieltreff_where( $e ) . ' · ' . ( $kinds[ $e['ortsart'] ] ?? '' ),
					__( 'Adresse (privat)', 'spieltafel' ) => $e['adresse'],
					__( 'Kontakt (privat)', 'spieltafel' ) => $e['kontakt'],
					__( 'Spiele', 'spieltafel' )         => implode( ', ', spieltreff_game_names( $e ) ),
					__( 'Plätze', 'spieltafel' )         => $e['max'] ? $e['max'] : '∞',
					__( 'Mindestanzahl', 'spieltafel' )  => $e['nur_bei_min'] ? $e['min'] : '–',
				);
				echo '<table class="form-table" role="presentation">';
				foreach ( $rows as $label => $value ) {
					printf( '<tr><th scope="row">%s</th><td>%s</td></tr>', esc_html( $label ), nl2br( esc_html( $value ? $value : '–' ) ) );
				}
				echo '</table><h4>' . esc_html__( 'Anmeldungen', 'spieltafel' ) . '</h4><ul>';
				foreach ( spieltreff_rows( $post->ID ) as $row ) {
					$user = get_userdata( (int) $row->user_id );
					printf( '<li>%s (%s) · %s</li>', esc_html( $user ? $user->display_name : '?' ), esc_html( $user ? $user->user_email : '' ), esc_html( $labels[ $row->status ] ?? $row->status ) );
				}
				echo '</ul>';
				$reports = get_post_meta( $post->ID, '_st_meldung' );
				if ( $reports ) {
					echo '<h4>' . esc_html__( 'Meldungen', 'spieltafel' ) . '</h4><ul>';
					foreach ( $reports as $r ) {
						printf( '<li>%s · %s: %s %s</li>', esc_html( wp_date( 'j.n.Y H:i', (int) $r['zeit'] ) ), esc_html( spieltreff_display_name( (int) $r['user'] ) ), esc_html( $r['grund'] ), esc_html( $r['text'] ) );
					}
					echo '</ul>';
				}
			},
			null,
			'normal',
			'high'
		);
	}
);

/** Wird ein Eintrag aus der Prüfung veröffentlicht: Ersteller informieren, Meldungen zurücksetzen. */
add_action(
	'transition_post_status',
	function ( $new, $old, $post ) {
		if ( SPIELTREFF_TYPE !== $post->post_type || 'publish' !== $new || 'pending' !== $old ) {
			return;
		}
		delete_post_meta( $post->ID, '_st_meldung' );
		spieltreff_mail(
			(int) $post->post_author,
			/* translators: %s: Titel */
			sprintf( __( 'Dein Eintrag ist online: %s', 'spieltafel' ), $post->post_title ),
			array( __( 'Dein Eintrag wurde geprüft und ist jetzt für alle sichtbar.', 'spieltafel' ), get_permalink( $post ) )
		);
	},
	10,
	3
);

/* Konten sperren: Benutzer → Spieltreff sperren */

add_filter(
	'user_row_actions',
	function ( $actions, $user ) {
		if ( ! current_user_can( 'edit_users' ) || user_can( $user, 'edit_posts' ) ) {
			return $actions;
		}
		$blocked             = (bool) get_user_meta( $user->ID, '_st_gesperrt', true );
		$url                 = wp_nonce_url( admin_url( 'admin-post.php?action=spieltreff_block&user=' . $user->ID ), 'spieltreff_block_' . $user->ID );
		$actions['st_block'] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), $blocked ? esc_html__( 'Entsperren', 'spieltafel' ) : esc_html__( 'Sperren', 'spieltafel' ) );
		return $actions;
	},
	10,
	2
);

add_action(
	'admin_post_spieltreff_block',
	function () {
		$uid = isset( $_GET['user'] ) ? (int) $_GET['user'] : 0;
		check_admin_referer( 'spieltreff_block_' . $uid );
		if ( ! current_user_can( 'edit_users' ) || user_can( $uid, 'edit_posts' ) ) {
			wp_die( esc_html__( 'Nicht erlaubt.', 'spieltafel' ) );
		}
		if ( get_user_meta( $uid, '_st_gesperrt', true ) ) {
			delete_user_meta( $uid, '_st_gesperrt' );
		} else {
			update_user_meta( $uid, '_st_gesperrt', time() );
			// Abmelden auf allen Geräten und Einträge ausblenden.
			WP_Session_Tokens::get_instance( $uid )->destroy_all();
			$posts = get_posts(
				array(
					'post_type'      => SPIELTREFF_TYPE,
					'author'         => $uid,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			);
			foreach ( $posts as $id ) {
				wp_update_post( array( 'ID' => $id, 'post_status' => 'pending' ) );
			}
		}
		wp_safe_redirect( admin_url( 'users.php' ) );
		exit;
	}
);

add_filter(
	'manage_users_columns',
	function ( $cols ) {
		$cols['st_status'] = __( 'Spieltreff', 'spieltafel' );
		return $cols;
	}
);

add_filter(
	'manage_users_custom_column',
	function ( $out, $column, $uid ) {
		if ( 'st_status' !== $column ) {
			return $out;
		}
		if ( get_user_meta( $uid, '_st_gesperrt', true ) ) {
			return '<strong style="color:#b32d2e">' . esc_html__( 'gesperrt', 'spieltafel' ) . '</strong>';
		}
		if ( get_user_meta( $uid, '_st_verify', true ) ) {
			return esc_html__( 'E-Mail unbestätigt', 'spieltafel' );
		}
		$user = get_userdata( $uid );
		return $user && in_array( SPIELTREFF_ROLE, (array) $user->roles, true ) ? esc_html__( 'aktiv', 'spieltafel' ) : '';
	},
	10,
	3
);

/* Datenschutzerklärung: Textvorschlag unter Einstellungen → Datenschutz */

add_action(
	'admin_init',
	function () {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
		$days = (int) spieltreff_setting( 'keep_days' );
		wp_add_privacy_policy_content(
			__( 'Spieltafel: Spieltreff', 'spieltafel' ),
			wp_kses_post(
				'<h2>' . __( 'Spieltreff: Konto', 'spieltafel' ) . '</h2><p>' .
				__( 'Für den Spieltreff brauchst du ein Konto. Wir speichern deinen Anzeigenamen, deine E-Mail-Adresse, ein verschlüsseltes Passwort, optional deine Region sowie den Zeitpunkt deiner Zustimmung zu den Regeln. Andere sehen nur deinen Anzeigenamen. Deine E-Mail-Adresse sieht nur, wer einen Eintrag erstellt hat, bei dem du dich anmeldest. Für die Anmeldung setzt WordPress ein technisch notwendiges Cookie.', 'spieltafel' ) .
				'</p><h2>' . __( 'Spieltreff: Einträge und Anmeldungen', 'spieltafel' ) . '</h2><p>' .
				__( 'Einträge (Titel, Beschreibung, Datum, Ort, Spiele) sind öffentlich sichtbar, die genaue Adresse und Kontaktangaben aber nur für bestätigte Teilnehmende. Wenn du dich anmeldest, speichern wir deine Anmeldung und deine Nachricht an den Ersteller. Bestätigte Teilnehmende sehen gegenseitig ihre Anzeigenamen. Einträge sind für Suchmaschinen gesperrt.', 'spieltafel' ) .
				'</p><p>' .
				/* translators: %d: Tage */
				sprintf( __( 'Vergangene Einträge löschen wir %d Tage nach dem Termin automatisch, zusammen mit allen Anmeldungen. Unbestätigte Konten löschen wir nach 7 Tagen. Dein Konto und alle Daten kannst du jederzeit selbst unter «Mein Konto» herunterladen oder löschen.', 'spieltafel' ), $days ) .
				'</p><h2>' . __( 'Spieltreff: Spamschutz und Sicherheit', 'spieltafel' ) . '</h2><p>' .
				__( 'Zum Schutz vor Spam löst dein Browser bei Formularen eine kleine Rechenaufgabe. Dabei werden keine Daten an Dritte übermittelt und keine Cookies gesetzt. Zur Begrenzung von Missbrauch speichern wir einen verschlüsselten Wert (Hash) deiner IP-Adresse für höchstens 24 Stunden. Gemeldete Einträge prüfen wir und können sie oder Konten sperren.', 'spieltafel' ) .
				'</p><h2>' . __( 'Spieltreff: E-Mails', 'spieltafel' ) . '</h2><p>' .
				__( 'Wir schicken dir E-Mails zu deinem Konto und zu deinen Einträgen und Anmeldungen, zum Beispiel wenn sich jemand anmeldet oder deine Anmeldung bestätigt wird. Nachrichten eines Erstellers an alle Teilnehmenden versenden wir in seinem Auftrag; Antworten gehen direkt an ihn.', 'spieltafel' ) .
				'</p><h2>' . __( 'Spieltreff: Teilen auf Facebook, Instagram und WhatsApp', 'spieltafel' ) . '</h2><p>' .
				__( 'Die Knöpfe zum Teilen sind einfache Links. Wir laden keine Skripte, Knöpfe oder Pixel von Meta (Facebook, Instagram, WhatsApp). Erst wenn du selbst auf einen Knopf tippst, öffnet sich der jeweilige Dienst und es gelten dessen Datenschutzbestimmungen. Geteilt werden nur Titel, Zeit, Ort und Spiele eines Eintrags, nie die genaue Adresse.', 'spieltafel' ) .
				'</p>'
			)
		);
	}
);
