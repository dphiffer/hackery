function isDebug() {
  return location.href.indexOf('debug') != -1;
}

function debug(msg) {
  if (isDebug()) {
    alert(msg);
  }
}

var hash = '';
function hashChanged(newHash) {
  if (newHash == '' || newHash == '/') {
    scroll.start(0, 0);
  } else if (newHash != hash) {
    hash = newHash;
    path = newHash.split('/');
    if (path[0] == '') {
      path.shift();
    }
    var first = path.shift();
    if (first == 'talks' || first == 'workshops') {
      first = 'schedule';
    }
    var sectionId = 'section-' + first;
    if (first == 'gallery') {
      sectionId = 'section-exhibition';
    } else if ($(document.body).hasClass('web-gallery')) {
      $(document.body).removeClass('web-gallery');
      if ($('gallery-iframe')) {
        $('gallery-iframe').destroy();
      }
      closeGallerySlide();
    }
    if ($('section-gallery')) {
      $('section-gallery').getElements('.selected').removeClass('selected');
    }
    if ($(sectionId)) {
      if (first != 'participants' && first != 'schedule' && first != 'exhibition' || path.length == 0) {
        scroll.start(0, Math.max(0, $(sectionId).getPosition().y - 10));
      }
      if (path.length > 0) {
        var second = path.shift();
        if (second && second != '') {
          if (first == 'participants') {
            scroll.start(0, $('participant-' + second).getPosition().y - 50);
          } else if (first == 'schedule') {
            scroll.start(0, $('event-' + second).getPosition().y - 50);
          } else if (first == 'talks') {
            scroll.start(0, $('event-' + second).getPosition().y - 50);
          } else if (first == 'workshops') {
            scroll.start(0, $('event-' + second).getPosition().y - 50);
          } else if (first == 'exhibition') {
            scroll.start(0, $('exhibition-' + second).getPosition().y - 50);
          } else if (first == 'gallery') {
            $('gallery-' + second).addClass('selected');
            showGallery($('gallery-' + second).get('data-url'));
          } else {
            showPage(second);
          }
        }
      }
    }
  }
}

function getHashPosition(hash) {
  path = hash.split('/');
  if (path[0] == '') {
    path.shift();
  }
  var first = path.shift();
  var sectionId = 'section-' + first;
  if ($(sectionId)) {
    return Math.max(0, $(sectionId).getPosition().y - 10);
  }
  return null;
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
  $(document.body).setStyle('margin-bottom', padding);
  hashListener = new HashListener();
  hashListener.addEvent('hashChanged', hashChanged);
  hashListener.start();
  $$('a[href^="/"]').each(function(link) {
    var path = link.get('href').match(/\/([^\/]+)/g);
    if (path.length > 0 && $('section-' + path[0].substr(1))) {
      var hash = '#' + path[0];
      if (path.length > 1 && $('page-' + path[1].substr(1))) {
        hash += path[1];
      }
      link.addEvent('click', function(e) {
        e.stop();
        hashListener.updateHash(hash);
      });
    }
  });
  $$('a[href^="#/"]').addEvent('click', function(e) {
    e.stop();
    var href = this.get('href');
    if (hash != href.substr(1)) {
      hashListener.updateHash(href);
    } else {
      var pos = getHashPosition(href.substr(1));
      if (pos != null && pos != window.getScroll().y) {
        scroll.start(0, pos);
      }
    }
    
  });
  $$('a[href^="mailto:"]').each(function(link) {
    var href = link.get('href');
    href = href.replace(/\(at\)/gi, '@');
    href = href.replace(/\(dot\)/gi, '.');
    link.set('href', href);
  });
  $$('.view-source').each(function(span) {
    if (Browser.firefox) {
      span.set('html', '<i>Tools &rarr; Web Developer &rarr; Page Source</i> [cmd-U]');
    } else if (Browser.safari) {
      span.set('html', '<i>Develop &rarr; Show Page Source </i> [cmd-opt-U] (Safari hides the Develop menu by default, enable it in the preferences)');
    } else if (Browser.chrome) {
      span.set('html', '<i>View &rarr; Developer &rarr; View Source</i> [cmd-opt-U]');
    }
  });
});

function setupSection(sectionId) {
  var sectionWidth = 0;
  $$('#section-' + sectionId + ' .page').each(function(page) {
    sectionWidth += page.getSize().x;
  });
  $('section-' + sectionId).getElement('.slider').setStyle('width', sectionWidth);
  
  $$('#section-' + sectionId + ' .subpages a').each(function(link, index) {
    var id = 'page-' + link.get('href').match(/\/([^\/]+)$/)[1];
    if (index == 0) {
      updateViewerHeight($(id), true);
    }
    var pos = $(id).getPosition($(id).getParent('.viewer'));
    $(id).store('position', -pos.x);
  });
}

function createSlide(el, onOpen, onClose) {
  var handle = el.getElement('.dropdown');
  var options = el.getElement('.dropdown-options');
  var slide = new Fx.Slide(options, {
    duration: 333,
    transition: Fx.Transitions.Quart.easeOut,
    onComplete: function() {
      if (this.open && onClose) {
        onClose();
      }
    }
  });
  options.store('slide', slide);
  options.store('handle', handle);
  slide.hide();
  handle.addEvent('click', function(e) {
    e.stop();
    if (!slide.open) {
      handle.addClass('open');
      if (onOpen) {
        onOpen();
      }
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

function playVideo(sectionId, id) {
  var link = $('video-link-' + id);
  if (link.hasClass('selected')) {
    return;
  }
  $$('#' + sectionId + ' .content .selected').removeClass('selected');
  $$('#' + sectionId + ' .menu a.selected').removeClass('selected');
  link.addClass('selected');
  $('video-' + id).addClass('selected');
  var vimeoUrl = link.get('data-vimeo-url');
  var iframe = $(sectionId).getElement('.video iframe');
  if (vimeoUrl) {
    var vimeoId = vimeoUrl.match(/vimeo.com\/(\d+)/);
    if (vimeoId) {
      var url = "http://player.vimeo.com/video/" + vimeoId[1] + "?title=0&byline=0&portrait=0&autoplay=1";
      iframe.removeClass('hidden');
      iframe.src = url;
    }
  } else {
    iframe.src = 'about:blank';
    iframe.addClass('hidden');
  }
}
