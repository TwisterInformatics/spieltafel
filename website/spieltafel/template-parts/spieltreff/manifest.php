<?php
/**
 * Kernbotschaft «Zurück an den Tisch»: drei Gründe und die Handystapel-Regel.
 *
 * @package Spieltafel
 * @var array $args { heading: h2|h3 }
 */

$tag = ( $args['heading'] ?? 'h2' ) === 'h3' ? 'h3' : 'h2';
?>
<div class="st-manifest">
	<<?php echo esc_html( $tag ); ?> class="st-manifest-title"><?php esc_html_e( 'Warum es den Spieltreff gibt', 'spieltafel' ); ?></<?php echo esc_html( $tag ); ?>>
	<div class="st-pillars">
		<?php foreach ( spieltreff_pillars() as $pillar ) : ?>
			<div class="st-pillar">
				<span class="st-pillar-icon" aria-hidden="true"><?php echo esc_html( $pillar['icon'] ); ?></span>
				<h3><?php echo esc_html( $pillar['title'] ); ?></h3>
				<p><?php echo esc_html( $pillar['text'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="st-stack">
		<svg class="st-stack-icon" viewBox="0 0 64 64" aria-hidden="true"><rect x="14" y="30" width="38" height="22" rx="5" fill="#3a443e" transform="rotate(-8 33 41)"/><rect x="12" y="22" width="38" height="22" rx="5" fill="#1d2420" transform="rotate(6 31 33)"/><rect x="14" y="14" width="38" height="22" rx="5" fill="#1d2420"/><circle cx="21" cy="21" r="2.4" fill="#6b726d"/><circle cx="27" cy="21" r="2.4" fill="#6b726d"/></svg>
		<div>
			<strong><?php esc_html_e( 'Unsere Spieltreff-Regel: der Handystapel', 'spieltafel' ); ?></strong>
			<p><?php esc_html_e( 'Zu Beginn kommen alle Handys umgedreht auf einen Stapel. Wer zuerst danach greift, mischt die nächste Runde. Einzige Ausnahme: ein Handy mit der Spieltafel-App, das die Punkte zählt.', 'spieltafel' ); ?></p>
		</div>
	</div>
	<p class="st-wink"><?php esc_html_e( 'Ja, wir wissen es: Das hier ist eine Webseite. Sie hat ein Ziel: dass du sie bald wieder schliesst und dich an einen Tisch setzt.', 'spieltafel' ); ?></p>
</div>
