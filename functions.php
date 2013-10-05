<?php

function hackery_init() {
  register_post_type('participant', array(
    'label' => 'Participants',
    'labels' => array(
      'singular_name' => 'Participant',
      'add_new_item' => 'Add New Participant',
      'edit_item' => 'Edit Participant'
    ),
    'public' => true,
    'hierarchical' => true,
    'supports' => array(
      'title',
      'editor',
      'custom-fields',
      'page-attributes'
    )
  ));
  
  register_post_type('talk', array(
    'label' => 'Talks',
    'labels' => array(
      'singular_name' => 'Talk',
      'add_new_item' => 'Add New Talk',
      'edit_item' => 'Edit Talk'
    ),
    'public' => true,
    'hierarchical' => true,
    'supports' => array(
      'title',
      'editor',
      'custom-fields',
      'page-attributes'
    )
  ));
  
  register_post_type('workshop', array(
    'label' => 'Workshops',
    'labels' => array(
      'singular_name' => 'Workshop',
      'add_new_item' => 'Add New Workshop',
      'edit_item' => 'Edit Workshop'
    ),
    'public' => true,
    'hierarchical' => true,
    'supports' => array(
      'title',
      'editor',
      'custom-fields',
      'page-attributes'
    )
  ));
}
add_action('init', 'hackery_init');

remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

// Rearrange the admin menu
function custom_menu_order($menu_ord) {

  if (!$menu_ord) {
    return true;
  }

  return array( 'index.php'                       // Dashboard
              , 'edit.php?post_type=page'         // Pages
              , 'edit.php?post_type=participant'  // Participants
              , 'edit.php?post_type=talk'         // Talks
              , 'edit.php?post_type=workshop'     // Workshops
              , 'upload.php'                      // Media
              , 'separator1'                      // Separator
              , 'themes.php'                      // Appearance
              , 'plugins.php'                     // Plugins
              , 'tools.php'                       // Tools
              , 'users.php'                       // Users
            );
}

add_filter('custom_menu_order', 'custom_menu_order'); // Activate custom_menu_order
add_filter('menu_order', 'custom_menu_order');

add_image_size('banner', 320, 999999, false);

function hackery_admin_menu() {
  global $menu;
	$restricted = array(__('Posts'), __('Links'), __('Comments'));
	end($menu);
	while (prev($menu)) {
		$value = explode(' ', $menu[key($menu)][0]);
		if (in_array($value[0] != NULL ? $value[0] : "", $restricted)) {
		  unset($menu[key($menu)]);
		}
	}
}
add_action('admin_menu', 'hackery_admin_menu');

function hackery_pages() {
  query_posts(array(
    'post_type' => 'page',
    'post_parent' => 0,
    'order' => 'ASC',
    'orderby' => 'menu_order',
    'numberposts' => -1
  ));
}
if (empty($_GET['subpage'])) {
  add_action('template_redirect', 'hackery_pages');
}

function hackery_nav_items() {
  global $post;
  while (have_posts()) {
    the_post();
    if (hackery_page_template() == 'banner.php') {
      continue;
    }
    echo "<a href=\"#/$post->post_name\" class=\"$post->post_name\">$post->post_title</a>\n";
  }
  rewind_posts();
}

function hackery_sections() {
  global $section_alt;
  $section_alt = 0;
  while (have_posts()) {
    the_post();
    $template_path = get_page_template();
    $template_file = basename($template_path);
    if (preg_match('/^(\w+)/', $template_file, $matches)) {
      $template = $matches[1];
      if (!empty($last_template) && $last_template == $template) {
        if (empty($section_alt)) {
          $section_alt = 1;
        } else {
          $section_alt = 0;
        }
      }
      get_template_part($template);
      $last_template = $template;
    }
  }
}

function hackery_get_participants($filter = null) {
  global $cached_participants;
  if (!empty($cached_participants)) {
    $participants = $cached_participants;
  } else {
    $participants = get_posts(array(
      'post_type' => 'participant',
      'orderby' => 'title',
      'order' => 'ASC',
      'numberposts' => -1
    ));
    $cached_participants = $participants;
  }
  $filtered = array();
  foreach ($participants as $participant) {
    $participant->fields = get_fields($participant->ID);
    if (!empty($filter) && !in_array($filter, $participant->fields['participation'])) {
      continue;
    }
    $participant->post_content = apply_filters('the_content', $participant->post_content);
    $filtered[] = $participant;
  }
  return $filtered;
}

function hackery_sort_by_date_time($a, $b) {
  $a_sort = "{$a->fields['date']}-{$a->fields['time']}";
  $b_sort = "{$b->fields['date']}-{$b->fields['time']}";
  if ($a_sort == $b_sort) {
    return 0;
  }
  return ($a_sort < $b_sort) ? -1 : 1;
}

function hackery_get_post_id($post) {
  return $post->ID;
}

function hackery_page($page) {
  ?>
  <div id="page-<?php echo $page->post_name; ?>" class="page">
    <div class="main-content">
      <h3 class="page"><?php echo apply_filters('the_title', $page->post_title); ?></h3>
      <?php
      
      $template = get_post_meta($page->ID, '_wp_page_template', true);
      if ($template == 'default') {
        echo apply_filters('the_content', $page->post_content);
      } else {
        require_once __DIR__ . "/$template";
        $template_id = str_replace('.php', '', $template);
        $main_function = "hackery_{$template_id}_main";
        if (function_exists($main_function)) {
          $main_function($page);
        }
      }
      
      edit_post_link('Edit', '<div class="edit-post">', '</div>', $page->ID);
      
      ?>
    </div>
    <?php
    
    if ($template == 'default') {
      $side_content = get_post_meta($page->ID, 'side_content', true);
      if ($side_content) {
        echo "<div class=\"side-content\">$side_content</div>\n";
      }
    } else {
      $side_function = "hackery_{$template_id}_side";
      if (function_exists($side_function)) {
        echo "<div class=\"side-content\">\n";
        $side_function($page);
        echo "</div>\n";
      }
    }
    
    ?>
    <div class="clear"></div>
  </div>
  <?php
}

function hackery_url($value) {
  if (strpos($value, '@') !== false) {
    $value = "mailto:$value";
  }
  if (strpos($value, ':') === false) {
    $value = "http://$value";
  }
  return $value;
}

function hackery_url_title($url) {
  if (preg_match('/twitter.com\/(.+)/', $url, $matches)) {
    return '@' . $matches[1];
  }
  $title = preg_replace('/^https?:\/\//', '', $url);
  $title = preg_replace('/^mailto:/', '', $title);
  $title = preg_replace('/\/$/', '', $title);
  $title = preg_replace('/^www./', '', $title);
  return $title;
}

function hackery_time($time) {
  $display_time = array();
  $time_range = explode('-', $time);
  foreach ($time_range as $time) {
    $time_parts = explode(':', $time);
    $am_pm = 'am';
    if ($time_parts[0] > 11) {
      if ($time_parts[0] > 12) {
        $time_parts[0] = $time_parts[0] - 12;
      }
      $am_pm = 'pm';
    }
    $time = implode(':', $time_parts) . $am_pm;
    $display_time[] = $time;
  }
  return implode('–', $display_time);
}

function participant_page($page) {
  
  ?>
  <div id="participant-<?php echo $page->post_name; ?>" class="participant">
    <div class="main-content">
      <h3 class="page"><a href="#/participants/<?php echo $page->post_name; ?>"><?php echo $page->fields['display_name']; ?></a><br class="clear"></h3>
      <?php
      
      if (in_array('talk', $page->fields['participation'])) {
        list($talk) = $page->fields['talk'];
        $date = get_field('date', $talk->ID);
        $date = date('M j, Y', strtotime($date));
        $time = hackery_time(get_field('time', $talk->ID));
        echo "<div class=\"talk participation\"><h5>Talk</h5>\n";
        echo "<h4><a href=\"#/schedule/$talk->post_name\">" . $talk->post_title . "</a></h4>";
        echo "<p>$date / $time</p></div>\n";
      }
      
      if (in_array('workshop', $page->fields['participation'])) {
        list($workshop) = $page->fields['workshop'];
        $date = get_field('date', $workshop->ID);
        $date = date('M j, Y', strtotime($date));
        $time = hackery_time(get_field('time', $workshop->ID));
        echo "<div class=\"workshop participation\"><h5>Workshop</h5>\n";
        echo "<h4><a href=\"#/schedule/$workshop->post_name\">" . $workshop->post_title . "</a></h4>";
        echo "<p>$date / $time</p></div>\n";
      }
      
      if (in_array('exhibition', $page->fields['participation'])) {
        echo "<div class=\"exhibition participation\"><h5>Artwork</h5>\n";
        echo "<h4><a href=\"#/exhibition/$page->post_name\">" . $page->fields['art_title'] . "</a></h4>";
        if (!empty($page->fields['exhibition_url'])) {
          $url = hackery_url($page->fields['exhibition_url']);
          $title = hackery_url_title($url);
          echo "<p><a href=\"$url\">$title</a></p>";
        }
        echo "</div>\n";
      }
      
      edit_post_link('Edit', '<p>', '</p>', $page->ID);
      
      ?>
    </div>
    <div class="side-content">
      <?php
      
      echo apply_filters('the_content', $page->post_content);
      $links = array();
      $github_link = '';
      foreach ($page->fields['participant_urls'] as $url) {
        $url = hackery_url($url['url']);
        $title = hackery_url_title($url);
        if (preg_match('/github\.com\/([^\/]+)/', $url, $matches)) {
          $img = "https://identicons.github.com/{$matches[1]}.png";
          $identicon = "<img src=\"$img\" alt=\"GitHub user {$matches[1]}\" width=\"32\" height=\"32\" class=\"identicon\">";
          $github_link = "<a href=\"$url\">$identicon$title</a>";
        } else {
          $links[] = "<a href=\"$url\">$title</a>";
        }
      }
      
      if (empty($github_link) && !empty($identicon)) {
        echo "<!-- tk github stuff -->";
      }
      
      $links = implode(' / ', $links);
      echo "<p>$links</p>\n";
      if (!empty($github_link)) {
        echo "<p>$github_link</p>\n";
      }
      
      ?>
    </div>
    <div class="clear"></div>
  </div>
  <?php
}

function hackery_subpage($page) {
  $template = get_post_meta($page->ID, '_wp_page_template', true);
  if ($template == 'default') {
    echo apply_filters('the_content', $page->post_content);
  } else {
    
  }
}

function hackery_subpages($page) {
  $children = hackery_get_children($page);
  if (count($children) > 0) {
    ?>
    <ul class="subpages">
      <?php foreach ($children as $index => $child) { ?>
        <?php $selected = ($index == 0) ? ' class="selected"' : ''; ?>
        <li id="subpage-<?php echo $child->post_name; ?>" <?php echo $selected; ?>><a href="#/<?php echo $page->post_name; ?>/<?php echo $child->post_name; ?>"><?php echo $child->post_title; ?></a></li>
      <?php } ?>
    </ul>
    <?php
  }
}

function hackery_get_children($page) {
  global $current_user;
  $orderby = get_field('children_order_by', $page->ID);
  $order = get_field('children_order', $page->ID);
  if (!$orderby) {
    $orderby = 'post_date';
  }
  if (!$order) {
    $order = 'DESC';
  }
  $query = array(
    'post_parent' => $page->ID,
    'post_type' => 'page',
    'orderby' => $orderby,
    'order' => $order,
    'numberposts' => -1
  );
  $children = get_posts($query);
  if (is_user_logged_in() &&
      (in_array('administrator', $current_user->roles) ||
       $page->post_author == $current_user->ID)) {
    $query['post_status'] = 'draft';
    $drafts = get_posts($query);
    foreach ($drafts as $post) {
      $post->post_title = "{$post->post_title} (draft)";
      $post->post_name = "post{$post->ID}";
    }
    $query['post_status'] = 'future';
    $scheduled = get_posts($query);
    foreach ($scheduled as $post) {
      $post->post_title = "{$post->post_title} (scheduled)";
    }
    $children = array_merge($drafts, $scheduled, $children);
  }
  return $children;
}

function hackery_address_search() {
  $address = urlencode($_POST['query']);
  $ch = curl_init();
  $bounds = urlencode("40.679392,-73.9414823|40.709094,-73.9013609");
  curl_setopt($ch, CURLOPT_URL, "http://maps.googleapis.com/maps/api/geocode/json?address=$address&bounds=$bounds&sensor=false");
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  curl_close($ch);
  header('Content-Type: application/json');
  echo $response;
  exit;
}
add_action('wp_ajax_address_search', 'hackery_address_search');
add_action('wp_ajax_nopriv_address_search', 'hackery_address_search');

function hackery_add_location() {
  $current_user = wp_get_current_user();
  if ($current_user->ID == 0) {
    $author = 1;
  } else {
    $author = $current_user->ID;
  }
  $title = $_POST['title'];
  $content = $_POST['description'];
  $url = $_POST['url'];
  $type = $_POST['type'];
  $latlng = $_POST['latlng'];
  $post = array(
    'post_title' => $title,
    'post_content' => $content,
    'post_author' => $author,
    'post_status' => 'pending',
    'post_type' => 'location'
  );
  $post_id = wp_insert_post($post);
  if ($type != 'all') {
    wp_set_object_terms($post_id, $type, 'location_type');
  }
  if (!empty($url)) {
    add_post_meta($post_id, 'url', $url);
  }
  add_post_meta($post_id, 'latlng', $latlng);
  header('Content-Type: application/json');
  $post['ID'] = $post_id;
  $post['location_type'] = $type;
  $post['url'] = $url;
  $post['latlng'] = $latlng;
  $response = array(
    'status' => 'OK',
    'post' => $post
  );
  echo json_encode($response);
  exit;
}
add_action('wp_ajax_add_location', 'hackery_add_location');
add_action('wp_ajax_nopriv_add_location', 'hackery_add_location');

function hackery_filter_content($content) {
  $url = get_bloginfo('url');
  $content = preg_replace("#href=.$url/(?!(wp-content|files))#", 'href="#/', $content);
  return $content;
}
add_filter('the_content', 'hackery_filter_content');

function hackery_page_template() {
  global $post;
  return get_post_meta($post->ID, '_wp_page_template', true);
}

add_filter('show_admin_bar', '__return_false');

function hackery_gallery() {
  $options = '';
  $posts = get_posts(array(
    'post_type' => 'participant',
    'orderby' => 'title',
    'order' => 'ASC',
    'posts_per_page' => -1,
    'meta_query' => array(
      array(
        'key' => 'web_gallery',
        'value' => '"web_gallery"',
        'compare' => 'LIKE'
      )
    )
  ));
  foreach ($posts as $post) {
    $post->fields = get_fields($post->ID);
    $url = hackery_url($post->fields['exhibition_url']);
    if (!empty($post->fields['web_gallery_url'])) {
      $url = hackery_url($post->fields['web_gallery_url']);
    }
    $title = "<strong><i>{$post->fields['art_title']}</i></strong> by {$post->fields['display_name']}";
    $options .= "<li><a href=\"#/gallery/$post->post_name\" id=\"gallery-$post->post_name\" data-url=\"$url\">$title</a></li>\n";
  }
  return <<<END
    <div id="section-gallery">
      <div id="hackery-gallery">
        <a href="#" class="dropdown">Web Gallery</a>
        <div class="dropdown-options">
          <ul>
            <li class="close"><a href="#/exhibition">Return to PRISM Breakup website</a></li>
            $options
          </ul>
        </div>
      </div>
      <div class="clear"></div>
      <script>
      
      createSlide($('hackery-gallery'), function() {
        $('hackery-gallery').setStyle('width', 380);
      }, function() {
        $('hackery-gallery').setStyle('width', $('hackery-gallery').getElement('a.dropdown').getSize().x + 2);
      });
      function showGallery(url) {
        $('hackery-gallery').setStyle('width', $('hackery-gallery').getElement('a.dropdown').getSize().x + 2);
        $(document.body).addClass('web-gallery');
        if (!$('gallery-iframe')) {
          new Element('iframe', {
            id: 'gallery-iframe',
            src: url,
            width: window.getSize().x,
            height: window.getSize().y,
            frameborder: 0
          }).inject(document.body);
        } else {
          $('gallery-iframe').src = url;
        }
        closeGallerySlide();
      }
      
      function closeGallerySlide() {
        var gallerySlide = $('hackery-gallery').getElement('.dropdown-options')
                                           .retrieve('slide');
        gallerySlide.hide();
        $('hackery-gallery').removeClass('open');
        $('hackery-gallery').getElement('.dropdown').removeClass('open');
      }
      
      window.addEvent('resize', function() {
        if ($('gallery-iframe')) {
          $('gallery-iframe').setStyles({
            width: window.getSize().x,
            height: window.getSize().y
          });
        }
      });
      
      if (location.href.match(/kiosk/)) {
        $('hackery-gallery').getElement('.close').destroy();
      }
      
      </script>
    </div>
END;
}

?>
