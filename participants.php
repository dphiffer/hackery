<?php
/*
Template Name: Participants
*/

$participants = hackery_get_participants();

?>
<section id="section-participants" class="alt">
  <div class="background">
    <div class="extent">
      <div class="left-column">
        <h2><?php the_title(); ?></h2>
        <ul class="subpages">
          <?php
          
          foreach ($participants as $participant) {
            echo <<<END
          <li class="participant$participant->ID">
            <a href="#/participants/$participant->post_name" class="participant-link-$participant->post_name" data-id="$participant->ID">
              {$participant->fields['display_name']}
            </a>
          </li>
END;
          }
          
          ?>
        </ul>
      </div>
      <div class="right-column">
        <?php
            
        foreach ($participants as $participant) {
          participant_page($participant);
        }
        
        ?>
      </div>
      <div class="clear"></div>
    </div>
  </div>
</section>
