<?php
/*
Template Name: Videos
*/

global $post;
$videos = get_posts(array(
  'post_type' => 'video',
  'orderby' => 'menu_order',
  'order' => 'ASC',
  'numberposts' => -1
));
$first = $videos[0];
$vimeo_url = get_post_meta($first->ID, 'vimeo_url', true);
preg_match('/vimeo\.com\/(\d+)/', $vimeo_url, $matches);
$vimeo_id = $matches[1];
$embed_url = "http://player.vimeo.com/video/$vimeo_id?title=0&amp;byline=0&amp;portrait=0";
$embed = <<<END
<iframe src="$embed_url" width="760" height="427" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
END;

?>
<section id="section-<?php echo $post->post_name; ?>" class="video-section">
  <div class="background">
    <div class="extent">
      <div class="left-column">
        <h2><?php the_title(); ?></h2>
        <div class="menu">
          <?php
            
          foreach ($videos as $index => $video) {
            $selected = ($index == 0) ? ' class="selected"' : '';
            $vimeo_url = get_post_meta($video->ID, 'vimeo_url', true);
            echo "<a href=\"#/{$post->post_name}/{$video->post_name}\" id=\"video-link-{$video->post_name}\" data-vimeo-url=\"$vimeo_url\"$selected>$video->post_title</a>\n";
          }
          
          ?>
        </div>
      </div>
      <div class="right-column">
        <div class="video"><?php echo $embed; ?></div>
        <div class="clear"></div>
        <div class="content">
          <?php
          
          foreach ($videos as $index => $video) {
            $selected = ($index == 0) ? ' selected' : '';
            
            ?>
            <div id="video-<?php echo $video->post_name; ?>" class="video-content<?php echo $selected; ?>">
              <?php echo apply_filters('the_content', $video->post_content); ?>
            </div>
          <?php } ?>
          <div id="video-about-the-video-series" class="video-content">
            <?php the_content(); ?>
          </div>
        </div>
      </div>
      <div class="clear"></div>
    </div>
  </div>
</section>
