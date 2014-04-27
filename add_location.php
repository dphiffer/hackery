<div id="add_location">
  <a href="#show_add_location" class="dropdown">Add a location</a>
  <div class="relative">
    <a href="#" class="cancel hidden">Cancel <span>&times;</span></a>
  </div>
  <div id="location_options">
    <div class="controls">
      <form action="<?php echo site_url('/wp-admin/admin-ajax.php') ?>" method="post" id="add_location_form" class="selected">
        <label>
          Location Name <span class="help">required</span>
          <input type="text" name="name" />
        </label>
        <label>
          Location URL
          <input type="text" name="url" />
        </label>
        <!--<label>
          Your e-mail address
          <input type="text" name="email" value="<?php if (!empty($_COOKIE['email'])) { echo $_COOKIE['email']; }?>" />
        </label>-->
        <label>
          Location Category
          <select name="category">
            <option value="all">None selected</option>
            <?php
            
            $location_types = get_categories(array(
              'taxonomy' => 'location_type'
            ));
            foreach ($location_types as $index => $type) {
              $slug = $type->category_nicename;
              $label = $type->name;
              echo "<option value=\"$slug\">$label</option>\n";
            }
            
            ?>
          </select>
        </label>
        <input type="submit" value="Continue" class="continue" />
        <div class="clear"></div>
      </form>
      <form action="<?php echo site_url('/wp-admin/admin-ajax.php') ?>" id="location_placement_form">
        <a href="#" class="back">Go back</a>
        <p>Drag the marker onto the map or search for the location’s address.</p>
        <div class="drag-drop">
          Drag the marker
        </div>
        <label>
          Location Address
          <input type="text" name="address" />
        </label>
        <input type="submit" value="Search" />
        <input type="button" value="Continue" class="continue" />
        <div class="clear"></div>
      </form>
      <form action="<?php echo site_url('/wp-admin/admin-ajax.php') ?>" id="confirm_location_form">
        <a href="#" class="back">Go back</a>
        <p>Zoom in closer if you’d like to position your marker more precisely. <strong>You can drag the map marker to move it.</strong></p>
        <div class="zoom">
          <input type="button" class="zoom_out" value="&ndash;" />
          &nbsp;&nbsp;Zoom&nbsp;&nbsp;
          <input type="button" class="zoom_in" value="+" />
        </div>
        <p>Click the submit button when you’re finished.</p>
        <input type="submit" value="Submit" />
      </form>
      <form action="<?php echo site_url('/wp-admin/admin-ajax.php') ?>" id="location_pending_form">
        <p class="thanks">Thank you for contributing to SolidarityNYC!<p>
        <p>We’ve received your submission and it is now pending moderation from our team of volunteers.</p>
        <p>If you have any questions or comments, please feel free to <a href="#/get-involved">contact us</a>.</p>
        <input type="submit" value="Done" />
      </form>
    </div>
  </div>
</div>
<script>

var placeholder, placeholderMarker;

function hideAddLocation() {
  $('location_options').setStyle('height', 0);
  $('add_location').getElement('.dropdown').removeClass('open');
  updateMarkers();
  if (placeholder) {
    placeholder.destroy();
    placeholder = null;
  }
  if (placeholderMarker) {
    placeholderMarker.setMap(null);
    placeholderMarker = null;
  }
  $$('#add_location form.selected').removeClass('selected');
  $('add_location_form').addClass('selected');
  $('add_location').removeClass('open');
  $('add_location').getElement('.cancel').addClass('hidden');
}

function setupNewMarker(latlng) {
  var type_select = $('add_location').getElement('select[name=category]');
  var type = type_select.options[type_select.selectedIndex].value;
  if (placeholder) {
    placeholder.destroy();
  }
  if (!placeholderMarker) {
    var iconSize = new google.maps.Size(32, 32);
    var iconOrigin = new google.maps.Point(0, 0);
    var iconAnchor = new google.maps.Point(15, 14);
    placeholderMarker = new google.maps.Marker({
      position: latlng,
      map: map,
      icon: new google.maps.MarkerImage(location_types[type],
        iconSize, iconOrigin, iconAnchor
      ),
      draggable: true
    });
  } else {
    placeholderMarker.setPosition(latlng);
  }
  $('location_placement_form').getElement('.continue').removeClass('disabled');
}

window.addEvent('domready', function() {
  var title = $('add_location_form').getElement('input[name=name]');
  function addLocationCheck() {
    var continueButton = $('add_location_form').getElement('.continue');
    if (title.value == '') {
      continueButton.addClass('disabled');
    } else {
      continueButton.removeClass('disabled');
    }
  }
  title.addEvent('keyup', addLocationCheck);
  
  var dropdown = $('add_location').getElement('.dropdown');
  $('location_options').set('tween', {
    duration: 500,
    transition: Fx.Transitions.Quart.easeOut
  });
  dropdown.addEvent('click', function(e) {
    e.stop();
    dropdown.toggleClass('open');
    if (dropdown.hasClass('open')) {
      $('add_location').addClass('open');
      hideMapIntro();
      closeMarker();
      var height = $('location_options').getElement('.controls').getSize().y;
      $('location_options').tween('height', height).retrieve('tween').chain(function() {
        $('add_location').getElement('.cancel').removeClass('hidden');
      });
      addLocationCheck();
    } else {
      hideAddLocation();
    }
  });
  
  $('add_location').getElement('.cancel').addEvent('click', function(e) {
    e.stop();
    hideAddLocation();
  });
  
  $('add_location_form').addEvent('submit', function(e) {
    e.stop();
    var continueButton = $('add_location_form').getElement('.continue');
    if (continueButton.hasClass('disabled')) {
      return;
    }
    if (!placeholderMarker) {
      $('location_placement_form').getElement('.continue').addClass('disabled');
    }
    closeMarker();
    hideMapIntro();
    updateMarkers(function() {
      return false;
    });
    $('add_location_form').removeClass('selected');
    $('location_placement_form').addClass('selected');
    var type_select = $('add_location').getElement('select[name=category]');
    var type = type_select.options[type_select.selectedIndex].value;
    var drag_drop = $('location_placement_form').getElement('.drag-drop');
    var rel = $('add_location').getElement('.relative');
    if (!placeholder) {
      placeholder = new Element('img', {
        src: 'wp-content/themes/map/icons/' + type + '.png',
        styles: {
          position: 'absolute',
          cursor: 'move',
          left: drag_drop.getPosition(rel).x + 110,
          top: drag_drop.getPosition(rel).y + 7,
          zIndex: 9
        }
      }).inject(rel);
      var drag = new Drag.Move(placeholder, {
        droppables: $('map_canvas'),
        onDrop: function(element, droppable, event) {
          if (droppable) {
            var pos = $('location_placement_form').getPosition();
            var size = $('location_placement_form').getSize();
            if (event.page.x < pos.x || event.page.x > pos.x + size.x ||
                event.page.y < pos.y || event.page.y > pos.y + size.y) {
              var point = new google.maps.Point(event.page.x, event.page.y - $('map_canvas').getPosition().y);
              var projection = overlay.getProjection();
              var latlng = projection.fromDivPixelToLatLng(point);
              setupNewMarker(latlng);
            }
          }
        }
      });
    }
    scroll.toElement('map');
  });
  
  $('location_placement_form').addEvent('submit', function(e) {
    e.stop();
    var query = $('location_placement_form').getElement('input[name=address]').value;
    new Request.JSON({
      url: 'wp-admin/admin-ajax.php',
      onComplete: function(response) {
        if (!response.results || !response.results.length || response.status != "OK") {
          alert(response.status);
        } else {
          var results = response.results.pop();
          var ll = results.geometry.location;
          var latlng = new google.maps.LatLng(ll.lat, ll.lng);
          if (results.geometry.bounds) {
            var ne = results.geometry.bounds.northeast;
            var neLatLng = new google.maps.LatLng(ne.lat, ne.lng);
            var sw = results.geometry.bounds.southwest;
            var swLatLng = new google.maps.LatLng(sw.lat, sw.lng);
            var bounds = new google.maps.LatLngBounds(swLatLng, neLatLng);
            map.fitBounds(bounds);
          } else {
            map.panTo(latlng);
          }
          setupNewMarker(latlng);
        }
      }
    }).post({
      action: 'address_search',
      query: query
    });
  });
  
  $('location_placement_form').getElement('.back').addEvent('click', function(e) {
    e.stop();
    $('add_location_form').addClass('selected');
    $('location_placement_form').removeClass('selected');
    if (placeholder) {
      placeholder.destroy();
      placeholder = null;
    }
  });
  
  $('location_placement_form').getElement('.continue').addEvent('click', function(e) {
    e.stop();
    if ($('location_placement_form').getElement('.continue').hasClass('disabled')) {
      return;
    }
    $('location_placement_form').removeClass('selected');
    $('confirm_location_form').addClass('selected');
  });
  
  $('confirm_location_form').getElement('.back').addEvent('click', function(e) {
    e.stop();
    $('location_placement_form').addClass('selected');
    $('confirm_location_form').removeClass('selected');
  });
  
  $('confirm_location_form').getElement('.zoom_out').addEvent('click', function(e) {
    e.stop();
    var zoom = map.getZoom();
    map.setZoom(zoom - 1);
  });
  
  $('confirm_location_form').getElement('.zoom_in').addEvent('click', function(e) {
    e.stop();
    var zoom = map.getZoom();
    var latlng = placeholderMarker.getPosition();
    map.setCenter(latlng);
    map.setZoom(zoom + 1);
  });
  
  $('confirm_location_form').addEvent('submit', function(e) {
    e.stop();
    var select = $('add_location_form').getElement('select');
    var category = select.options[select.selectedIndex].value;
    var latlng = placeholderMarker.getPosition();
    new Request.JSON({
      url: $('confirm_location_form').get('action'),
      onComplete: function(response) {
        $('add_location_form').getElement('input[name=name]').value = '';
        $('add_location_form').getElement('input[name=url]').value = '';
        select.selectedIndex = 0;
        $('location_placement_form').getElement('input[name=address]').value = '';
        $('add_location').getElement('.cancel').addClass('hidden');
        $('confirm_location_form').removeClass('selected');
        $('location_pending_form').addClass('selected');
      }
    }).post({
      action: 'add_location',
      title: $('add_location_form').getElement('input[name=name]').value,
      description: $('location_placement_form').getElement('input[name=address]').value,
      url: $('add_location_form').getElement('input[name=url]').value,
      type: category,
      latlng: latlng.lat() + ',' + latlng.lng()
    });
  });
  
  $('location_pending_form').addEvent('submit', function(e) {
    e.stop();
    placeholderMarker.setMap(null);
    placeholderMarker = null;
    map.setCenter(new google.maps.LatLng(40.7149, -73.9740));
    map.setZoom(12);
    hideAddLocation();
  });
  
});

</script>
