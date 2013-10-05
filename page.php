<?php

global $section_alt;
$section = $post->post_name;

?>
<section id="section-<?php echo $post->post_name; ?>"<?php if (!empty($section_alt)) { echo ' class="alt"'; } ?>>
  <div class="background">
    <div class="extent">
      <div class="left-column">
        <h2><?php the_title(); ?></h2>
        <?php hackery_subpages($post); ?>
      </div>
      <div class="right-column">
        <div class="viewer">
          <div class="slider">
            <?php
            
            $children = hackery_get_children($post);
            foreach ($children as $child) {
              hackery_page($child);
            }
            
            ?>
            <div class="clear"></div>
          </div>
        </div>
        <div class="clear"></div>
      </div>
      <div class="clear"></div>
    </div>
  </div>
  <script>
  setupSection('<?php echo $section; ?>');
  </script>
</section>
