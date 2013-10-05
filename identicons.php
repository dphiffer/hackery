<?php
/*
Template Name: Identicons
*/

global $wpdb;
$identicons = $wpdb->get_col("
  SELECT DISTINCT meta_value
  FROM $wpdb->postmeta
  WHERE meta_key LIKE 'participant_urls_%'
    AND meta_value LIKE '%github.com%'
");

function github_url_to_identicon($url) {
  if (preg_match('/github\.com\/([^\/]+)/', $url, $matches)) {
    return "https://identicons.github.com/{$matches[1]}.png";
  }
  return $url;
}
$identicons = array_map('github_url_to_identicon', $identicons);

$identicons[] = 'https://identicons.github.com/OmerShapira.png';
$identicons[] = 'https://identicons.github.com/ryanbartley.png';
$identicons[] = 'https://identicons.github.com/maxLoveCode.png';
$identicons[] = 'https://identicons.github.com/hdeweyh.png';
$identicons[] = 'https://identicons.github.com/allisonburtch.png';
$identicons[] = 'https://identicons.github.com/auremoser.png';
$identicons[] = 'https://identicons.github.com/nasser.png';

?>
<header>
  <div id="identicons"></div>
  <script>
  
  var identicons = <?php echo json_encode($identicons); ?>;
  window.addEvent('domready', function() {
    var html = '';
    for (var i = 0; i < 25; i++) {
      identicons.sort(function(a, b) {
        return (Math.random() > 0.5) ? -1 : 1;
      });
      identicons.each(function(src) {
        html += '<img src="' + src + '" alt="" width="32" height="32">';
      });
    }
    $('identicons').set('html', html);
  });
  
  </script>
</header>
