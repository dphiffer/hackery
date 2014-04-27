<?php

function map_init() {
  register_post_type('location', array(
    'label' => 'Map',
    'labels' => array(
      'singular_name' => 'Location',
      'add_new_item' => 'Add New Location',
      'edit_item' => 'Edit Location'
    ),
    'menu_position' => 5,
    'public' => true,
    'hierarchical' => true,
    'supports' => array(
      'title',
      'editor',
      'custom-fields'
    )
  ));
  register_taxonomy('location_type', 'location', array(
    'hierarchical' => true
  ));
  register_taxonomy('location_tag', 'location', array(
    'hierarchical' => false
  ));
  register_post_type('video', array(
    'label' => 'Videos',
    'labels' => array(
      'singular_name' => 'Video',
      'add_new_item' => 'Add New Video',
      'edit_item' => 'Edit Video'
    ),
    'menu_position' => 5,
    'public' => true,
    'hierarchical' => true,
    'supports' => array(
      'title',
      'editor',
      'custom-fields'
    )
  ));
}
add_action('init', 'map_init');

function map_admin_menu() {
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
add_action('admin_menu', 'map_admin_menu');

function map_pages() {
  query_posts(array(
    'post_type' => 'page',
    'post_parent' => 0,
    'order' => 'ASC',
    'orderby' => 'menu_order',
    'numberposts' => -1
  ));
}
add_action('template_redirect', 'map_pages');

function map_nav_items() {
  global $post;
  while (have_posts()) {
    the_post();
    echo "<a href=\"#/$post->post_name\" class=\"$post->post_name\">$post->post_title</a>\n";
  }
  rewind_posts();
}

function map_sections() {
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

function map_get_locations() {
  global $map_locations;
  if (!empty($map_locations)) {
    return $map_locations;
  }
  $map_locations = array();
  $locations = get_posts(array(
    'post_type' => 'location',
    'orderby' => 'title',
    'order' => 'ASC',
    'numberposts' => -1
  ));
  $ids = array_map('map_get_post_id', $locations);
  $terms = wp_get_object_terms($ids, 'location_type', array('fields' => 'all_with_object_id'));
  $types = array();
  foreach ($terms as $term) {
    $types[$term->object_id] = $term->slug;
  }
  foreach ($locations as $index => $location) {
    $content = apply_filters('the_content', $location->post_content);
    $latlng = get_post_meta($location->ID, 'latlng', true);
    $data = array(
      'id' => $location->ID,
      'slug' => $location->post_name,
      'title' => $location->post_title,
      'content' => $content,
      'latlng' => $latlng
    );
    $url = get_post_meta($location->ID, 'url', true);
    if (!empty($url)) {
      $data['url'] = $url;
    }
    if (!empty($types[$location->ID])) {
      $data['type'] = $types[$location->ID];
    }
    $map_locations[] = $data;
  }
  return $map_locations;
}

function map_get_post_id($post) {
  return $post->ID;
}

function map_page($page) {
  ?>
  <div id="page-<?php echo $page->post_name; ?>" class="page">
    <div class="main-content">
      <?php echo apply_filters('the_content', $page->post_content); ?>
    </div>
    <div class="clear"></div>
  </div>
  <?php
}

function map_subpages($page) {
  $children = map_get_children($page);
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

function map_get_children($page) {
  $children = get_posts(array(
    'post_parent' => $page->ID,
    'post_type' => 'page',
    'orderby' => 'menu_order',
    'order' => 'ASC'
  ));
  return $children;
}

function map_address_search() {
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
add_action('wp_ajax_address_search', 'map_address_search');
add_action('wp_ajax_nopriv_address_search', 'map_address_search');

function map_add_location() {
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
add_action('wp_ajax_add_location', 'map_add_location');
add_action('wp_ajax_nopriv_add_location', 'map_add_location');

function map_filter_content($content) {
  $url = get_bloginfo('url');
  $content = preg_replace("#href=.$url/(?!wp-content)#", 'href="#/', $content);
  return $content;
}
add_filter('the_content', 'map_filter_content');



?>
