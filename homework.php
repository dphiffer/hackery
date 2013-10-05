<?php
/*
Template Name: Homework Submission
*/

if (!empty($_GET['subpage'])) {
  get_header();
  global $post;
  $permalink = get_permalink($post->ID);
  $permalink .= ((strpos($permalink, '?') === false) ? '?' : '&');
  $permalink .= "subpage=1";
  ?>
  <form action="<?php echo $permalink; ?>" method="post" enctype="multipart/form-data">
    <?php
    
    $heading = 'Got some homework to submit?';
    $class = '';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && is_user_logged_in()) {
      $class = 'highlight';
      $user = wp_get_current_user();
      $username = $user->user_login;
      $user_email = $user->user_email;
      unset($user);
      if (!empty($_FILES['upload']) && !empty($_FILES['upload']['tmp_name'])) {
        $tmp_name = $_FILES['upload']['tmp_name'];
        $name = $_FILES['upload']['name'];
        $uploads_dir = ABSPATH . "files/students/$username";
        if (!file_exists($uploads_dir)) {
          mkdir($uploads_dir);
        }
        move_uploaded_file($tmp_name, "$uploads_dir/$name");
        $link = site_url("files/students/$username/$name");
        $heading = '<a href="' . $link . '" target="_top">Submission</a> received! You should get an email confirmation.';
        wp_mail($user_email, "Homework submission from $username", "$link\r\n\r\n{$_POST['notes']}");
        wp_mail(get_bloginfo('admin_email'), "Homework submission from $username", "$link\r\n\r\n{$_POST['notes']}");
      } else if (!empty($_POST['url'])) {
        $heading = '<a href="' . $_POST['url'] . '">Submission</a> received! You should get an email confirmation.';
        wp_mail($user_email, "Homework submission from $username", "{$_POST['url']}\r\n\r\n{$_POST['notes']}");
        wp_mail(get_bloginfo('admin_email'), "Homework submission from $username", "{$_POST['url']}\r\n\r\n{$_POST['notes']}");
      } else {
        $heading = 'Oops, you didn’t include a file or URL.';
      }
    }
    
    ?>
    <h4 class="top"><span class="<?php echo $class; ?>"><?php echo $heading; ?></span></h4>
    <label>
      Choose a file to upload
      <input type="file" name="upload" class="upload">
    </label>
    <label>
      Or specify a URL where the project is located
      <input type="text" name="url" placeholder="http://">
    </label>
    <label>
      Notes
      <textarea name="notes" cols="20" rows="4"></textarea>
    </label>
    <input type="submit" value="Submit">
  </form>
  <?php
  get_footer();
}

function hackery_homework_main($post) {
  $permalink = get_permalink($post->ID);
  $permalink .= ((strpos($permalink, '?') === false) ? '?' : '&');
  $permalink .= "subpage=1";
  if (is_user_logged_in()) {
    echo "<iframe src=\"$permalink\" width=\"505\" height=\"320\"></iframe>";
  } else {
    wp_login_form(array(
      'redirect' => get_permalink($post->ID)
    ));
  }
}

function hackery_homework_side($post) {
  if (is_user_logged_in()) {
    $user = wp_get_current_user();
    $username = $user->user_login;
    $logout = wp_logout_url(get_permalink($post->ID));
    echo "Hello, <b>$username</b><br><a href=\"$logout\">Logout</a>";
  } else {
    $lostpassword = wp_lostpassword_url(get_permalink($post->ID));
    echo "<h4 class=\"top\">Having trouble?</h4>\n<p>The login for this site is different from the one you use on wordpress.com.<br>"; 
    echo "&#8618; <a href=\"$lostpassword\">Reset your password</a></p>\n";
    if (get_option('users_can_register')) {
      echo "<h4>Don’t have an account?</h4>\n";
      wp_register('&#8618; ', '');
    }
  }
}

?>
