<?php
/*
Template Name: Map
*/
?>
<section id="map">
  <div id="hackery_canvas"></div>
  <div id="hackery_intro">
    <div id="logo">
		  <h1><span>SolidarityNYC</span></h1>
    </div>
    <p><strong>The solidarity economy</strong> meets our needs (everything from financial services to food) by utilizing values of justice, sustainability, cooperation, and democracy. Together we can build an economy worth occupying.<br /><a href="#about" class="read">Read more</a></p>
    <a href="#map" class="explore">Explore the map</a>
  </div>
  <a href="#hackery_size" id="hackery_size" data-open-label="Smaller map">Bigger map</a>
  <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
  <script>
  
  var mapStyles = [
    {
      featureType: "landscape",
      elementType: "geometry",
      stylers: [
        { visibility: "on" },
        { lightness: 98 },
        { gamma: 9.99 }
      ]
    }, {
      featureType: "road.arterial",
      stylers: [
        { visibility: "simplified" },
        { saturation: -95 },
        { lightness: 21 }
      ]
    }, {
      featureType: "transit",
      stylers: [
        { visibility: "off" }
      ]
    }, {
      featureType: "poi",
      stylers: [
        { visibility: "off" }
      ]
    }, {
      featureType: "road.local",
      stylers: [
        { visibility: "on" },
        { lightness: 10 }
      ]
    }, {
      featureType: "road.highway",
      stylers: [
        { visibility: "off" }
      ]
    }, {
      featureType: "administrative.neighborhood",
      stylers: [
        { visibility: "on" }
      ]
    }, {
      featureType: "water",
      stylers: [
        { visibility: "simplified" },
        { saturation: -100 },
        { lightness: 57 }
      ]
    }
  ];
  
  var streetsType = new google.maps.StyledMapType(mapStyles, {
    name: "Streets"
  });
  
  var zoom = 13;
  var center = new google.maps.LatLng(40.7149, -73.9740);
  var options = {
    zoom: zoom,
    center: center,
    mapTypeControlOptions: {
      mapTypeIds: [google.maps.MapTypeId.SATELLITE, 'Streets']
    },
    scrollwheel: false
  };
  var map = new google.maps.Map($("hackery_canvas"), options);
  map.mapTypes.set('Streets', streetsType);
  map.setMapTypeId('Streets');
  var overlay = new google.maps.OverlayView();
  overlay.draw = function() {};
  overlay.setMap(map);
  
  var locations = [
    <?php
    
    $locations = hackery_get_locations();
    foreach ($locations as $index => $location) {
      echo json_encode($location);
      if ($index < count($locations) - 1) {
        echo ",";
      }
      echo "\n";
      if ($index < count($locations) - 1) {
        echo "    ";
      }
    }
    
    ?>
  ];
  
  var location_types = {
    <?php
    
    $location_types = get_categories(array(
      'taxonomy' => 'location_type'
    ));
    $base_url = get_bloginfo('stylesheet_directory') . '/icons';
    
    echo "all: '$base_url/all.png',\n";
    
    foreach ($location_types as $index => $type) {
      echo "'$type->category_nicename': '$base_url/$type->category_nicename.png'";
      if ($index < count($location_types) - 1) {
        echo ",";
      }
      echo "\n";
      if ($index < count($location_types) - 1) {
        echo "    ";
      }
    }
    
    ?>
  };
  
  var infowindow = new google.maps.InfoWindow();
  function closeMarker() {
    infowindow.close();
    $$('#directory li.selected').removeClass('selected');
  }
  google.maps.event.addListener(infowindow, 'closeclick', function() {
    hashListener.updateHash('#/map');
    $$('#directory li.selected').removeClass('selected');
  });
  var directory = {};
  var iconSize = new google.maps.Size(32, 32);
  var iconOrigin = new google.maps.Point(0, 0);
  var iconAnchor = new google.maps.Point(15, 14);
  locations.each(function(location) {
    var latlng = location.latlng.match(/^(.+),(.+)$/);
    var marker = new google.maps.Marker({
      position: new google.maps.LatLng(parseFloat(latlng[1]), parseFloat(latlng[2])),
      map: map,
      icon: new google.maps.MarkerImage(location_types[location.type],
        iconSize, iconOrigin, iconAnchor
      )
    });
    var content = '<div class="infowindow">' +
                    '<h3>' + location.title + '</h3>' +
                    location.content +
                  '</div>';
    var openMarker = function() {
      closeMarker();
      hideMapIntro();
      hideAddLocation();
      infowindow.setPosition(marker.getPosition());
      infowindow.setContent(content);
      infowindow.open(map, marker);
      //map.panTo(marker.getPosition());
      var directory = $$('#directory .holder.selected .directory' + location.id);
      if (directory) {
        directory[0].addClass('selected');
      }
    };
    google.maps.event.addListener(marker, 'click', function () {
      hashListener.updateHash('#/map/' + location.slug);
    });
    location.marker = marker;
    location.openMarker = openMarker;
    directory['location' + location.id] = location;
  });
  
  var mapIntroHidden = false;
  var logoInterval;
  function hideMapIntro() {
    if (!mapIntroHidden) {
      mapIntroHidden = true;
      $('hackery_intro').fade('out').retrieve('tween').chain(function() {
        $('hackery_intro').setStyle('display', 'none');
      });
      clearInterval(logoInterval);
    }
  }
  
  function updateMarkers(filter) {
    if (!filter) {
      var link = $('location_type').getElement('a.selected');
      var type = link.get('data-type');
      var filter = function(location) {
        return (type == 'all' || location.type == type);
      }
    }
    Object.each(directory, function(location) {
      var visible = filter(location);
      location.marker.setVisible(visible);
    });
  }
  
  $('hackery_intro').getElement('a.explore').addEvent('click', function(e) {
    e.stop();
    hideMapIntro();
  });
  
  window.addEvent('domready', function() {
    var logo = $$('#logo h1 span')[0];
    var logos = [logo];
    for (var i = 1; i < 12; i++) {
      var clone = logo.clone();
      var num = i + 1;
      clone.setStyle('background-image', 'url("wp-content/themes/map/logo/logo' + num + '.png")');
      clone.fade('hide');
      clone.inject($('logo').getElement('h1'));
      logos.push(clone);
    }
    var index = 0;
    var z = 1;
    logoInterval = setInterval(function() {
      var prevIndex = index;
      index = (index + 1) % logos.length;
      z++;
      logos[index].fade('hide');
      logos[index].setStyle('z-index', z);
      logos[index].fade('in').retrieve('tween').chain(function() {
        logos[prevIndex].fade('hide');
      });
    }, 10000);
    
    
    var smallSize = $('hackery_canvas').getSize().y;
    var origLabel = $('hackery_size').get('html');
    
    $('hackery_size').addEvent('click', function(e) {
      e.stop();
      var largeSize = window.getSize().y - 42;
      if (largeSize < 600) {
        largeSize = 600;
      }
      $('hackery_size').toggleClass('open');
      if ($('hackery_size').hasClass('open')) {
        $('hackery_canvas').setStyle('height', largeSize);
        $('hackery_size').set('html', $('hackery_size').get('data-open-label'));
      } else {
        $('hackery_canvas').setStyle('height', smallSize);
        $('hackery_size').set('html', origLabel);
      }
      google.maps.event.trigger(map, "resize");
    });
  });
  
  </script>
  <div class="extent">
    <?php get_template_part('add_location'); ?>
  </div>
</section>

