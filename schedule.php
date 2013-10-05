<?php
/*
Template Name: Schedule
*/
?>
<section id="section-schedule">
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
        <p id="bring_laptop"><em>Please bring a laptop, if you have one, to workshops.</em></p>
        <?php
        
        $participants = hackery_get_participants();
        $workshops = array();
        $talks = array();
        foreach ($participants as $participant) {
          if (!empty($participant->fields['workshop'])) {
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
          if (!empty($participant->fields['talk'])) {
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
        }
        
        $schedule = array_merge($workshops, $talks);
        usort($schedule, 'hackery_sort_by_date_time');
        
        $curr_date = '';
        
        foreach ($schedule as $event) {
          $speakers = array();
          foreach ($event->speakers as $participant) {
            $speakers[] = "<a href=\"#/participants/$participant->post_name\">{$participant->fields['display_name']}</a>";
          }
          $speakers = implode(', ', $speakers);
          $date = $event->fields['date'];
          $date = date('D M j, Y', strtotime($date));
          $date_short = date('M j', strtotime($date));
          if ($date != $curr_date) {
            if ($date == 'Sat Oct 5, 2013') {
              echo "<dt id=\"event-art-opening\"><h5>Reception / Oct 4 / 8:30pm–10:30pm</h5></dt>\n";
              echo "<dd><h4><a href=\"#/exhibition\">Art Exhibition Opening Reception</a></h4></dd>\n";
            }
            if (!empty($curr_date)) {
              echo "</dl></div><div class=\"clear\"></div>\n";
            }
            $date_id = 'event-' . strtolower(date('D', strtotime($date)));
            echo "<div class=\"left-column\"><div class=\"day_label\" id=\"$date_id\">$date</div></div>\n";
            echo "<div class=\"right-column\"><dl>\n";
            $curr_date = $date;
          }
          $time = hackery_time($event->fields['time']);
          $id = "event-$event->post_name";
          $title = $event->post_title;
          $subtitle = '';
          if (strpos($title, ':') !== false) {
            $index = strpos($title, ':');
            $title = substr($event->post_title, 0, $index);
            $subtitle = '<br><span class="subtitle">' . substr($event->post_title, $index + 1) . '</span>';
          }
          $label = ucfirst($event->post_type);
          if ($title == 'Cryptoparty') {
            $label = 'Party';
          }
          $content = '';
          $laptop = '';
          if ($event->post_type == 'workshop') {
            $laptop = '<p><em>Please bring a laptop, if you have one.</em></p>';
          }
          if (!empty($event->post_content) || !empty($event->fields['eventbrite_url'])) {
            $rsvp = '';
            if (!empty($event->fields['eventbrite_url'])) {
              $eventbrite_url = hackery_url($event->fields['eventbrite_url']);
              $rsvp = "<p><a href=\"{$eventbrite_url}\">RSVP to $event->post_title</a></p>\n";
            }
            $content = '<div class="content">' . apply_filters('the_content', $event->post_content) . "$rsvp$laptop</div>";
          }
          echo "<dt id=\"$id\"><h5>$label / $date_short / $time</h5></dt>\n";
          echo "<dd><h4><a href=\"#/schedule/$event->post_name\">{$title}{$subtitle}</a></h4>\n$speakers$content</dd>\n";
        }
        
        echo "</dl></div><div class=\"clear\"></div>\n";
        
        ?>
      </div>
    </div>
  </div>
</section>
