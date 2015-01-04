<?php

global $post;
hackery_page_class( 'page' );

hackery_header();
hackery_the_post();

?>
		<section <?php hackery_page_attributes(); ?>>
			<?php edit_post_link( 'Edit', '', "\n" ); ?>
			<div class="title">
				<?php hackery_title(); ?>
			</div>
			<div class="container">
				<div class="content">
					<?php the_content(); ?>
				</div>
			</div>
		</section>
<?php

$page_class = '';
hackery_footer();

?>
