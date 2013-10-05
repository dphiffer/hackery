<?php
/*
Template Name: Banner
*/

$image = get_field('header_image');
if (preg_match('/wp-content\/uploads\/(.+)$/', $image, $matches)) {
  $image = "images/$matches[1]";
}

?>
<header>
  <a href="<?php the_field('header_url'); ?>" style="background-image: url(<?php echo $image; ?>);">
    <span><?php the_field('header_caption'); ?></span>
  </a>
</header>
