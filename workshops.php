<?php
/*
Template Name: Workshops
*/
?>
<section id="section-workshops" class="alt">
  <div class="background">
    <div class="extent">
      <div class="left-column">
        <h2><?php the_title(); ?></h2>
      </div>
      <div class="right-column">
        <div class="intro"><?php echo apply_filters('the_content', $post->post_content); ?></div>
      </div>
      <div class="clear"></div>
      <div class="listings">
        <?php
        
        $participants = hackery_get_participants('workshop');
        $workshops = array();
        foreach ($participants as $participant) {
          list($workshop) = $participant->fields['workshop'];
          if (empty($workshops[$workshop->ID])) {
            $workshop->fields = get_fields($workshop->ID);
            $workshop->speakers = array($participant);
            $workshops[$workshop->ID] = $workshop;
          } else {
            $workshop = $workshops[$workshop->ID];
            $workshop->speakers[] = $participant;
          }
        }
        
        usort($workshops, 'hackery_sort_by_date_time');
        
        $curr_date = '';
        
        foreach ($workshops as $workshop) {
          $speakers = array();
          foreach ($workshop->speakers as $participant) {
            $speakers[] = "<a href=\"#/participants/$participant->post_name\">{$participant->fields['display_name']}</a>";
          }
          $speakers = implode(', ', $speakers);
          $date = $workshop->fields['date'];
          $date = date('D M j, Y', strtotime($date));
          $date_short = date('M j', strtotime($date));
          if ($date != $curr_date) {
            if (!empty($curr_date)) {
              echo "</dl></div><div class=\"clear\"></div>\n";
            }
            echo "<div class=\"left-column\"><div class=\"day_label\">$date</div></div>\n";
            echo "<div class=\"right-column\"><dl>\n";
            $curr_date = $date;
          }
          $time = $workshop->fields['time'];
          $id = "workshop-$workshop->post_name";
          $title = $workshop->post_title;
          $subtitle = '';
          if (strpos($title, ':') !== false) {
            $index = strpos($title, ':');
            $title = substr($workshop->post_title, 0, $index);
            $subtitle = '<br><span class="subtitle">' . substr($workshop->post_title, $index + 1) . '</span>';
          }
          echo "<dt id=\"$id\"><h5>$date_short / $time</h5></dt>\n";
          echo "<dd><h4>{$title}{$subtitle}</h4>\n$speakers</dd>\n";
        }
        
        echo "</dl></div><div class=\"clear\"></div>\n";
        
        ?>
      </div>
    </div>
  </div>
</section>
