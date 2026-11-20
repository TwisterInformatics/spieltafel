<?php
/**
 * Knöpfe zum Teilen eines Eintrags. Keine fremden Skripte: Facebook und WhatsApp
 * sind normale Links, Instagram läuft über das Teilen-Menü des Handys mit einem Bild.
 *
 * @package Spieltafel
 * @var array $args { entry: array }
 */

$e     = $args['entry'];
$url   = get_permalink( $e['id'] );
$text  = $e['titel'] . ' · ' . spieltreff_share_text( $e );
$story = spieltreff_image_url( $e['id'], 'story' );
?>
<div class="st-box st-share" data-share data-url="<?php echo esc_url( $url ); ?>" data-title="<?php echo esc_attr( $e['titel'] ); ?>" data-text="<?php echo esc_attr( $text ); ?>">
	<h2><?php 'gesuch' === $e['typ'] ? esc_html_e( 'Gesuch teilen', 'spieltafel' ) : esc_html_e( 'Spielabend teilen', 'spieltafel' ); ?></h2>
	<div class="st-share-buttons">
		<a class="st-share-btn st-share-fb" href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ) ); ?>" target="_blank" rel="noopener noreferrer">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 21v-7.5h2.6l.4-3h-3V8.6c0-.9.3-1.5 1.5-1.5h1.6V4.4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H7.8v3h2.6V21h3.1z"/></svg>
			Facebook
		</a>
		<button class="st-share-btn st-share-ig" type="button" data-share-image="<?php echo esc_url( $story ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7.3a4.7 4.7 0 1 0 0 9.4 4.7 4.7 0 0 0 0-9.4zm0 7.7a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm6-7.9a1.1 1.1 0 1 1-2.2 0 1.1 1.1 0 0 1 2.2 0zM21 8.1c-.1-1.5-.4-2.8-1.5-3.8S17.4 3 15.9 2.9C14.4 2.8 9.6 2.8 8.1 2.9 6.6 3 5.3 3.3 4.3 4.3S3 6.6 2.9 8.1c-.1 1.5-.1 6.3 0 7.8.1 1.5.4 2.8 1.5 3.8s2.3 1.4 3.8 1.5c1.5.1 6.3.1 7.8 0 1.5-.1 2.8-.4 3.8-1.5s1.4-2.3 1.5-3.8c.1-1.5.1-6.3-.3-7.8zm-2.1 9.6a3 3 0 0 1-1.7 1.7c-1.2.5-3.9.4-5.2.4s-4 .1-5.2-.4a3 3 0 0 1-1.7-1.7c-.5-1.2-.4-3.9-.4-5.2s-.1-4 .4-5.2a3 3 0 0 1 1.7-1.7C8 4.6 10.7 4.7 12 4.7s4-.1 5.2.4a3 3 0 0 1 1.7 1.7c.5 1.2.4 3.9.4 5.2s.1 4-.4 5.2z"/></svg>
			Instagram
		</button>
		<a class="st-share-btn st-share-wa" href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( $text . "\n" . $url ) ); ?>" target="_blank" rel="noopener noreferrer">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.2a9.8 9.8 0 0 0-8.4 14.8L2.2 21.8l4.9-1.3A9.8 9.8 0 1 0 12 2.2zm0 17.9c-1.5 0-3-.4-4.2-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8.1 8.1 0 1 1 12 20.1zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1-.7-.3-1.5-.7-2.3-1.6-.7-.6-1.1-1.4-1.3-1.6-.1-.2 0-.4.1-.5l.4-.4.3-.4v-.4l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.4.1-.6.3-.2.2-.8.8-.8 2s.8 2.3.9 2.5c.1.2 1.6 2.5 4 3.5 1.5.6 2.1.7 2.8.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3z"/></svg>
			WhatsApp
		</a>
		<button class="st-share-btn" type="button" data-copy-link data-done="<?php esc_attr_e( 'Kopiert!', 'spieltafel' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.6 13.4a1 1 0 0 1 0-1.4l3.5-3.5a1 1 0 1 1 1.4 1.4L12 13.4a1 1 0 0 1-1.4 0zM8.5 19a4.5 4.5 0 0 1-3.2-7.7l2-2a1 1 0 0 1 1.4 1.4l-2 2a2.5 2.5 0 0 0 3.6 3.6l2-2a1 1 0 0 1 1.4 1.4l-2 2A4.5 4.5 0 0 1 8.5 19zm7.5-4.3a1 1 0 0 1-.7-1.7l2-2a2.5 2.5 0 0 0-3.6-3.6l-2 2a1 1 0 1 1-1.4-1.4l2-2a4.5 4.5 0 0 1 6.4 6.4l-2 2a1 1 0 0 1-.7.3z"/></svg>
			<span><?php esc_html_e( 'Link kopieren', 'spieltafel' ); ?></span>
		</button>
	</div>
	<p class="st-small st-share-hint" data-ig-hint hidden>
		<?php esc_html_e( 'Das Bild ist gespeichert. Öffne Instagram, poste es in deiner Story und füge den Link mit dem Link-Sticker ein (der Link ist schon kopiert).', 'spieltafel' ); ?>
	</p>
	<p class="st-small"><?php esc_html_e( 'Beim Teilen sehen andere nur Titel, Zeit, Ort und Spiele, nie die genaue Adresse.', 'spieltafel' ); ?></p>
</div>
