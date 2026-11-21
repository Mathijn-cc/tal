<?php
/**
 * Pagina met blokken. Zonder blokken: titel en inhoud.
 */
get_header();

while ( have_posts() ) :
	the_post();
	if ( ! powerhub_blokken() ) :
		?>
		<section class="sectie sectie-tekst achtergrond-pagina">
			<div class="binnen tekst-smal">
				<?php echo powerhub_kop( get_the_title(), 'h1' ); // phpcs:ignore ?>
				<div class="opmaak"><?php the_content(); ?></div>
			</div>
		</section>
		<?php
	endif;
endwhile;

get_footer();
