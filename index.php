<?php

$base_url = get_bloginfo('url');
if ("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" != "$base_url/") {
  header("Location: $base_url/#{$_SERVER['REQUEST_URI']}");
  exit;
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title><?php bloginfo('name'); ?></title>
    <meta name="description" content="<?php bloginfo('description'); ?>" />
    <meta name="viewport" content="width=device-width" />
    <link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/reset.min.css" />
    <link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/wordpress.css" />
    <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>">
    <script src="<?php bloginfo('stylesheet_directory'); ?>/mootools-core-1.4.5-full-nocompat-yc.js"></script>
    <script src="<?php bloginfo('stylesheet_directory'); ?>/mootools-more-1.4.0.1.js"></script>
    <script src="<?php bloginfo('stylesheet_directory'); ?>/HashListener.js"></script>
    <!--[if lt IE 9]>
    <script src="<?php bloginfo('stylesheet_directory'); ?>/html5shiv.js"></script>
    <![endif]-->
    <script>
    
    var hash = '';
    function hashChanged(newHash) {
      if (newHash == '') {
        scroll.start(0, 0);
      } else if (newHash != hash) {
        hash = newHash;
        path = newHash.split('/');
        if (path[0] == '') {
          path.shift();
        }
        var first = path.shift();
        if ($(first)) {
          scroll.start(0, Math.max(0, $(first).getPosition().y - 10));
          if (first == 'map') {
            hideMapIntro();
          }
          if (path.length > 0) {
            var second = path.shift();
            if (second && second != '') {
              if (first == 'map') {
                showMapLocation(second);
              } else if (first == 'videos') {
                playVideo(second);
              } else {
                showPage(second);
              }
            }
          }
        }
      }
    }
    
    function showPage(id) {
      var page = $('page-' + id);
      if (page) {
        var section = page.getParent('section');
        var slider = page.getParent('.slider');
        slider.tween('left', page.retrieve('position'));
        section.getElements('.subpages li').removeClass('selected');
        $('subpage-' + id).addClass('selected');
        updateViewerHeight(page);
      }
    }
    
    var scroll, hashListener;
    window.addEvent('domready', function() {
      scroll = new Fx.Scroll(window, {
        duration: 750,
        transition: Fx.Transitions.Quint.easeOut
      });
      var sections = $$('section');
      var last = sections[sections.length - 1];
      var padding = window.getSize().y - last.getSize().y;
      document.body.setStyle('padding-bottom', padding);
      hashListener = new HashListener();
      hashListener.addEvent('hashChanged', hashChanged);
      hashListener.start();
      $$('a[href^="#/"]').addEvent('click', function(e) {
        e.stop();
        var href = this.get('href');
        hashListener.updateHash(href);
      });
    });
    
    function createSlide(el) {
      var handle = el.getElement('.dropdown');
      var options = el.getElement('.dropdown-options');
      var slide = new Fx.Slide(options, {
        duration: 333,
        transition: Fx.Transitions.Quart.easeOut
      });
      options.store('slide', slide);
      options.store('handle', handle);
      slide.hide();
      handle.addEvent('click', function(e) {
        e.stop();
        if (!slide.open) {
          handle.addClass('open');
        } else {
          handle.removeClass('open');
        }
        slide.toggle();
      });
    }
    
    function updateViewerHeight(page, instant) {
      var viewer = page.getParent('.viewer');
      if (instant) {
        viewer.setStyle('height', page.getSize().y);
      } else {
        viewer.tween('height', page.getSize().y);
      }
    }
    
    </script>
    <?php wp_head(); ?>
  </head>
  <body>
    <nav>
      <div class="extent">
        <div class="left-column">
          <h1><a href="#/"><?php bloginfo('name'); ?></a></h1>
        </div>
        <div class="right-column">
          <div class="pages">
            <?php map_nav_items(); ?>
          </div>
        </div>
        <div class="clear"></div>
      </div>
    </nav>
    <?php map_sections(); ?>
    <?php wp_footer(); ?>

  </body>
</html>
