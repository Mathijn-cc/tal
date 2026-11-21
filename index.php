<?php
/**
 * Terugval voor alles zonder eigen template.
 */
get_header();
?>
<section class="sectie achtergrond-pagina">
	<div class="binnen tekst-smal">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				echo powerhub_kop( get_the_title(), is_singular() ? 'h1' : 'h2' ); // phpcs:ignore
				is_singular() ? the_content() : the_excerpt();
			endwhile;
		else :
			echo powerhub_kop( 'Niets gevonden', 'h1' ); // phpcs:ignore
		endif;
		?>
	</div>
</section>
<?php
get_footer();
