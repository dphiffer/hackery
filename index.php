<?php

$base_url = get_bloginfo('url');
if ("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" != "$base_url/") {
  header("Location: $base_url/#{$_SERVER['REQUEST_URI']}");
  exit;
}

get_header();

?>
<nav>
  <div class="extent">
    <div class="left-column">
      <h1><a href="#/"><?php bloginfo('name'); ?></a></h1>
    </div>
    <div class="right-column">
      <div class="pages">
        <?php hackery_nav_items(); ?>
      </div>
    </div>
    <div class="clear"></div>
  </div>
</nav>
<?php

hackery_sections();
get_footer();

?>
      
