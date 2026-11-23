<?php
/**
 * Blok: contact (gegevens, formulier en Calendly-agenda).
 */
defined( 'ABSPATH' ) || exit;

$email    = ! empty( $blok['email'] ) ? $blok['email'] : powerhub( 'email' );
$telefoon = ! empty( $blok['telefoon'] ) ? $blok['telefoon'] : powerhub( 'telefoon' );
$tel_link = preg_replace( '/[^0-9+]/', '', (string) $telefoon );
$adres    = powerhub( 'adres' );
$social   = function_exists( 'powerhub_social' ) ? powerhub_social() : array();
$kop_tag  = 0 === (int) ( $blok['_index'] ?? 1 ) ? 'h1' : 'h2';
$anker    = sanitize_title( ! empty( $blok['agenda_anker'] ) ? $blok['agenda_anker'] : 'kennismaking' );
?>
<section <?php echo powerhub_sectie( $blok, 'contact' ); // phpcs:ignore ?>>
	<div class="rij contact-rij">
		<div class="contact-intro">
			<?php if ( ! empty( $blok['label'] ) ) : ?>
				<p class="bovenkop-tal"><?php echo esc_html( $blok['label'] ); ?></p>
			<?php endif; ?>
			<?php echo tal_kop( $blok['kop'] ?? '', $kop_tag, 'contact-kop' ); // phpcs:ignore ?>
			<?php if ( ! empty( $blok['lead'] ) ) : ?>
				<p class="lead"><?php echo esc_html( $blok['lead'] ); ?></p>
			<?php endif; ?>

			<?php if ( $email || $telefoon || $adres || $social ) : ?>
				<ul class="contact-gegevens">
					<?php if ( $email && ! empty( $blok['foto'] ) ) : ?>
						<?php /* Persoonlijk: foto met naam, het e-mailadres eronder. */ ?>
						<li class="contact-persoon">
							<span class="contact-foto"><?php echo powerhub_beeld( $blok['foto'], 'medium', array( 'alt' => '', 'sizes' => '72px', 'style' => 'object-position: 50% 25%;' ) ); // phpcs:ignore ?></span>
							<span class="contact-persoon-tekst">
								<?php if ( ! empty( $blok['naam'] ) ) : ?>
									<span class="contact-naam"><?php echo esc_html( $blok['naam'] ); ?></span>
								<?php endif; ?>
								<a class="lijnlink" href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
							</span>
						</li>
					<?php elseif ( $email ) : ?>
						<li>
							<span class="contact-icoon"><?php echo powerhub_icoon( 'mail' ); // phpcs:ignore ?></span>
							<a class="lijnlink" href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
						</li>
					<?php endif; ?>
					<?php if ( $telefoon && $tel_link ) : ?>
						<li>
							<span class="contact-icoon"><?php echo powerhub_icoon( 'phone' ); // phpcs:ignore ?></span>
							<a class="lijnlink" href="tel:<?php echo esc_attr( $tel_link ); ?>"><?php echo esc_html( $telefoon ); ?></a>
						</li>
					<?php endif; ?>
					<?php if ( $adres ) : ?>
						<li>
							<span class="contact-icoon"><?php echo powerhub_icoon( 'map-pin' ); // phpcs:ignore ?></span>
							<address><?php echo nl2br( esc_html( $adres ) ); // phpcs:ignore ?></address>
						</li>
					<?php endif; ?>
					<?php foreach ( $social as $kanaal ) : ?>
						<li>
							<span class="contact-icoon"><?php echo powerhub_icoon( 'arrow-up-right' ); // phpcs:ignore ?></span>
							<a class="lijnlink" href="<?php echo esc_url( $kanaal['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $kanaal['naam'] ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="contact-formulier">
			<?php if ( ! empty( $blok['formulier_kop'] ) ) : ?>
				<h2 class="kop-m"><?php echo esc_html( $blok['formulier_kop'] ); ?></h2>
			<?php endif; ?>
			<?php echo tal_formulier( $blok['formulier'] ?? 0 ); // phpcs:ignore ?>
		</div>
	</div>

	<?php if ( ! empty( $blok['calendly'] ) ) : ?>
		<div class="rij contact-agenda" id="<?php echo esc_attr( $anker ); ?>">
			<div class="contact-agenda-tekst">
				<?php if ( ! empty( $blok['agenda_label'] ) ) : ?>
					<p class="bovenkop-tal"><?php echo esc_html( $blok['agenda_label'] ); ?></p>
				<?php endif; ?>
				<?php echo tal_kop( $blok['agenda_kop'] ?? '', 'h2', 'contact-agenda-kop' ); // phpcs:ignore ?>
				<?php if ( ! empty( $blok['agenda_tekst'] ) ) : ?>
					<p><?php echo esc_html( $blok['agenda_tekst'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="contact-agenda-kader">
				<?php /* Calendly laadt pas als de agenda bijna in beeld is (assets/js/blokken/contact.js). */ ?>
				<div class="calendly-inline-widget" data-url="<?php echo esc_url( tal_calendly_url( $blok['calendly'] ) ); ?>" data-lenis-prevent></div>
				<noscript><a class="knop knop-actie" href="<?php echo esc_url( $blok['calendly'] ); ?>"><span>Plan een kennismaking</span></a></noscript>
				<p class="contact-agenda-terugval tekst-klein">Lukt het niet om de agenda te laden? <a href="<?php echo esc_url( $blok['calendly'] ); ?>" target="_blank" rel="noopener">Open de agenda van Calendly</a>.</p>
			</div>
		</div>
	<?php endif; ?>
</section>
