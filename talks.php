<?php
/*
Template Name: Talks
*/
?>
<section id="section-talks">
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
        
        $participants = hackery_get_participants('talk');
        $talks = array();
        foreach ($participants as $participant) {
          list($talk) = $participant->fields['talk'];
          if (empty($talks[$talk->ID])) {
            $talk->fields = get_fields($talk->ID);
            $talk->speakers = array($participant);
            $talks[$talk->ID] = $talk;
          } else {
            $talk = $talks[$talk->ID];
            $talk->speakers[] = $participant;
          }
        }
        
        usort($talks, 'hackery_sort_by_date_time');
        
        $curr_date = '';
        
        foreach ($talks as $talk) {
          $speakers = array();
          foreach ($talk->speakers as $participant) {
            $speakers[] = "<a href=\"#/participants/$participant->post_name\">{$participant->fields['display_name']}</a>";
          }
          $speakers = implode(', ', $speakers);
          $date = $talk->fields['date'];
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
          $time = $talk->fields['time'];
          $id = "talk-$talk->post_name";
          $title = $talk->post_title;
          $subtitle = '';
          if (strpos($title, ':') !== false) {
            $index = strpos($title, ':');
            $title = substr($talk->post_title, 0, $index);
            $subtitle = '<br><span class="subtitle">' . substr($talk->post_title, $index + 1) . '</span>';
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
