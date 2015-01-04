<?php

get_header();

while (have_posts()) {
	get_template_part('post');
}

get_footer();

?>
