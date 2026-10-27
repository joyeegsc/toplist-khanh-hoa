<?php
/**
 * Static pages (about, policies...).
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="bg-surface-container-low border-b border-slate-border/60">
		<div class="max-w-[880px] mx-auto px-margin-mobile md:px-margin py-10 md:py-14">
			<?php tkh_breadcrumb( array( array( get_the_title(), null ) ) ); ?>
			<h1 class="font-display-hero-mobile text-display-hero-mobile md:font-display-hero md:text-display-hero text-on-surface"><?php the_title(); ?></h1>
		</div>
	</section>
	<div class="max-w-[880px] mx-auto px-margin-mobile md:px-margin py-10 md:py-14">
		<div class="entry-content"><?php the_content(); ?></div>
	</div>
	<?php
endwhile;

get_footer();
