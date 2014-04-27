<?php

global $section_alt;

?>
<section id="<?php echo $post->post_name; ?>"<?php if (!empty($section_alt)) { echo ' class="alt"'; } ?>>
  <div class="background">
    <div class="extent">
      <div class="left-column">
        <h2><?php the_title(); ?></h2>
        <?php map_subpages($post); ?>
      </div>
      <div class="right-column">
        <div class="viewer">
          <div class="slider">
            <?php
            
            $children = map_get_children($post);
            foreach ($children as $child) {
              map_page($child);
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
  
  var sectionId = '<?php echo $post->post_name; ?>';
  var sectionWidth = 0;
  $$('#' + sectionId + ' .page').each(function(page) {
    sectionWidth += page.getSize().x;
  });
  $(sectionId).getElement('.slider').setStyle('width', sectionWidth);
  
  $$('#' + sectionId + ' .subpages a').each(function(link, index) {
    var id = 'page-' + link.get('href').match(/\/([^\/]+)$/)[1];
    if (index == 0) {
      updateViewerHeight($(id), true);
    }
    var pos = $(id).getPosition($(id).getParent('.viewer'));
    $(id).store('position', -pos.x);
  });
  
  </script>
</section>
