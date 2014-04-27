<?php
/*
Template Name: Import map
*/

echo '<div class="extent"><pre>';

$xml = simplexml_load_file(get_bloginfo('stylesheet_directory') . '/solidaritynyc.kml');
$styles = array();

foreach ($xml->Document->Style as $style) {
  $id = $style['id'] . "";
  $styles[$id] = $style;
  /*$icon = $style->IconStyle->Icon->href . "";
  if (!in_array($icon, $icons)) {
    $icons[] = $icon;
  }*/
}

foreach ($xml->Document->Placemark as $placemark) {
  $title = $placemark->name . "";
  $title = trim($title);
  $content = $placemark->description . "";
  $content = str_replace('<div dir="ltr">', '', $content);
  $content = str_replace('<div>', '', $content);
  $content = str_replace('</div>', '', $content);
  $content = str_replace('<br><br>&nbsp;', '', $content);
  $content = trim($content);
  $url = null;
  $url_regex = "#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie";
  if (preg_match($url_regex, $content, $matches)) {
    $url = $matches[1];
  }
  $coords = $placemark->Point->coordinates . "";
  $coords = trim($coords);
  $coords = explode(',', $coords);
  $latlng = $coords[1] . ',' . $coords[0];
  $style_url = $placemark->styleUrl . "";
  $style_id = substr($style_url, 1);
  $style = $styles[$style_id];
  $icon_url = $style->IconStyle->Icon->href . "";
  $icon_file = basename($icon_url);
  $icon_file = str_replace('+', '_', $icon_file);
  $type = str_replace('.png', '', $icon_file);
  echo htmlentities("$title / $type / $latlng\n");
  $post = array(
    'post_title' => $title,
    'post_content' => $content,
    'post_status' => 'publish',
    'post_type' => 'location'
  );
  $post_id = wp_insert_post($post);
  wp_set_object_terms($post_id, $type, 'location_type');
  add_post_meta($post_id, 'latlng', $latlng);
  if (!empty($url)) {
    add_post_meta($post_id, 'url', $url);
  }
}

echo '</pre></div>';

?>
