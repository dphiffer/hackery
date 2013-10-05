<?php
/*
Template Name: Exhibition
*/
?>
<section id="section-exhibition" class="alt">
  <div class="background">
    <div class="extent">
      <div class="left-column">
        <h2><?php the_title(); ?></h2>
      </div>
      <div class="right-column">
        <div class="intro">
          <?php echo apply_filters('the_content', $post->post_content); ?>
          <?php echo hackery_gallery(); ?>
        </div>
        <dl>
        <?php
        
        $participants = hackery_get_participants('exhibition');
        foreach ($participants as $participant) {
          $title = $participant->fields['art_title'];
          if (!empty($participant->fields['exhibition_url'])) {
            $url = hackery_url($participant->fields['exhibition_url']);
            $title = "<a href=\"$url\">$title</a>";
          }
          $id = "exhibition-$participant->post_name";
          echo "<dt id=\"$id\"><a href=\"#/participants/$participant->post_name\">{$participant->fields['display_name']}</a></dt>\n";
          echo "<dd><h4>$title</h4></dd>\n";
        }
        
        ?>
        </dl>
      </div>
      <div class="clear"></div>
    </div>
  </div>
</section>
