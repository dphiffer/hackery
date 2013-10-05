<?php
/*
Template Name: Directory
*/

$locations = hackery_get_locations();
$per_column = 12;
$total = count($locations);

?>
<section id="directory">
  <div class="background">
    <div class="extent">
      <div class="left-column">
        <h2><?php the_title(); ?></h2>
        <div id="location_type" class="filter">
          <a href="#type-all" data-type="all" class="selected first">All categories</a>
          <?php
          
          $location_types = get_categories(array(
            'taxonomy' => 'location_type'
          ));
          $base_url = get_bloginfo('stylesheet_directory') . '/icons';
          foreach ($location_types as $index => $type) {
            $slug = $type->category_nicename;
            $label = $type->name;
            echo "<a href=\"#type-$slug\" style=\"background-image: url($base_url/$slug.png\" data-type=\"$slug\">$label</a>\n";
          }
          
          ?>
        </div>
      </div>
      <div class="right-column">
        <div id="location-count"><?php echo "$total locations"; ?></div>
        <div class="pagination">
          <?php
          
          $pages = ceil($total / ($per_column * 3));
          for ($i = 0; $i < $pages; $i++) {
            $n = $i + 1;
            $selected = ($i == 0) ? ' class="selected"' : '';
            echo "<a href=\"#directory-page$n\"$selected>$n</a>";
          }
          
          ?>
        </div>
        <div class="locations">
          <div class="slider">
            <div id="locations-all" class="holder selected">
              <?php
              
              echo "<ul>\n";
              foreach ($locations as $index => $location) {
                if ($index % $per_column == 0 && $index > 0) {
                  echo "    </ul>\n    <ul>\n";
                }
                echo "      <li class=\"directory{$location['id']} {$location['type']}\"><a href=\"#/map/{$location['slug']}\" class=\"location-link-{$location['slug']}\" data-id=\"{$location['id']}\">{$location['title']}</a></li>\n";
              }
              echo "    </ul>\n";
              
              ?>
              <div class="clear"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="clear"></div>
    </div>
  </div>
  <script>
  
  function resizeLocations() {
    var locations = $('directory').getElement('.locations');
    locations.setStyle('height', $('location_type').getSize().y);
    var totalWidth = 0;
    $$('#directory .locations ul').each(function(list) {
      if (list.getSize().y > locations.getSize().y) {
        locations.setStyle('height', list.getSize().y);
      }
      totalWidth += list.getSize().x;
    });
    $('locations-all').setStyle('width', totalWidth);
  }
  resizeLocations();
  
  /*function directoryLocationClickHandler(e) {
    e.stop();
    var link = e.target;
    var id = link.get('href').match(/location\d+/);
    if (id && directory) {
      var location = directory[id[0]];
      if (location) {
        location.openMarker();
        scroll.toElement('map');
      }
    }
  }*/
  
  function showMapLocation(slug) {
    var link = $('directory').getElement('a.location-link-' + slug);
    if (link) {
      var id = link.get('data-id');
      if (id && directory) {
        var location = directory['location' + id];
        if (location) {
          location.openMarker();
        }
      }
    }
  }
  
  /*$$('#directory .locations li a').each(function(link) {
    link.addEvent('click', directoryLocationClickHandler);
  });*/
  
  var slider = $('directory').getElement('.locations .slider');
  slider.set('tween', {
    duration: 750,
    transition: Fx.Transitions.Quart.easeOut
  });
  $$('#directory .pagination a').each(function(link) {
    link.addEvent('click', function(e) {
      e.stop();
      $$('#directory .pagination a.selected').removeClass('selected');
      var page = link.get('href').match(/directory-page(\d)/);
      if (page) {
        var i = page[1].toInt() - 1;
        slider.tween('left', i * -780);
        link.addClass('selected');
      }
    });
  });
  
  $$('#location_type a').each(function(link) {
    link.addEvent('click', function(e) {
      e.stop();
      var type = link.get('data-type');
      $$('#location_type a.selected').removeClass('selected');
      link.addClass('selected');
      $$('#directory .slider .holder.selected').removeClass('selected');
      var holder = $('locations-' + type);
      if (!holder) {
        holder = new Element('div', {
          id: 'locations-' + type,
          'class': 'holder'
        });
        holder.inject($('directory').getElement('.slider'));
        var list = new Element('ul');
        list.inject(holder);
        $$('#locations-all li').each(function(item) {
          if (item.hasClass(type)) {
            var clone = item.clone();
            clone.inject(list);
            //clone.getElement('a').addEvent('click', directoryLocationClickHandler);
            if (list.getElements('li').length == 12) {
              list = new Element('ul');
              list.inject(holder);
            }
          }
        });
      }
      holder.addClass('selected');
      var locationCount = holder.getElements('li').length;
      var pages = Math.ceil(locationCount / 36);
      $$('#directory .pagination a').each(function(link, index) {
        if (index == 0) {
          link.addClass('selected');
        } else {
          link.removeClass('selected');
        }
        if (index < pages && !(index == 0 && pages == 1)) {
          link.removeClass('hidden');
        } else {
          link.addClass('hidden');
        }
      });
      $('location-count').set('html', locationCount + ' locations');
      $('directory').getElement('.slider').setStyle('left', 0);
      updateMarkers();
      closeMarker();
      resizeLocations();
    });
  });
  
  </script>
</section>
