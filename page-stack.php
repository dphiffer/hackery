<?php
/*
Template Name: Stack
*/

global $post;

hackery_header();
hackery_the_post();

hackery_page_class( 'page-stack' );

?>
		<section <?php hackery_page_attributes(); ?>>
			<?php edit_post_link( 'Edit', '', "\n" ); ?>
			<div class="container">
				<div class="content">
					<div class="title">
						<?php hackery_title(); ?>
					</div>
					<?php the_content(); ?>
				</div>
			</div>
		</section>
<?php
		
		$query = new WP_Query( array(
			'post_parent' => get_the_ID(),
			'posts_per_page' => -1,
			'post_type' => 'page',
			'orderby' => 'menu_order',
			'order' => 'ASC'
		) );
		
		while ( $query->have_posts() ) {
			$query->the_post();
			$template = get_page_template();
			if ( empty( $template ) ) {
				$dir = get_template_directory();
				echo "$dir/page-{$post->post_name}.php";
				if ( file_exists( "$dir/page-{$post->post_name}.php" ) ){
					$template = "$dir/page-{$post->post_name}.php";
				} else {
					$template = "$dir/page.php";
				}
			}
			include $template;
		}
		wp_reset_postdata();
		
		?>
<?php
		
hackery_footer();

