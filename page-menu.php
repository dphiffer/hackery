
<?php
/*
Template Name: Menu
*/

global $post;

hackery_header();
hackery_the_post();

?>
		<section <?php hackery_page_attributes(); ?>>
			<?php edit_post_link( 'Edit', '', "\n" ); ?>
			<div class="title">
				<?php hackery_title(); ?>
			</div>
			<div class="container">
				<nav class="menu">
					<ul>
						<?php
						
						$subpages = '';
						foreach ( hackery_menu_links() as $url => $title ) {
							$url = esc_attr( $url );
							$title = esc_html( $title );
							$link_id = hackery_path_id( $url );
							$subpages .= "<div id=\"subpage-$link_id\" class=\"subpage\"></div>\n";
							echo "<li><a href=\"$url\" id=\"menu-$link_id\">$title</a></li>\n";
						}
						
						?>
					</ul>
				</nav>
				<div class="content">
					<div class="slider">
						<?php echo $subpages; ?>
						<br class="clear">
					</div>
				</div>
				<br class="clear">
			</div>
		</section>
<?php

$page_class = '';
hackery_footer();

?>
