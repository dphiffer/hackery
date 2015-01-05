(function($) {

var $root = $('html, body');
function checkURLHash() {
	var match = location.hash.match(/^#(\/.*)/);
	if (!match) {
		return;
	}
	var path = match[1];
	var basePage = getBasePage(path);
	if (basePage) {
		$root.animate({
			scrollTop: $(basePage).offset().top
		}, 750, 'easeOutQuint');
	}
	var link = document.getElementById('menu-' + path);
	if (link) {
		menuSelect(path);
	} else if ($(basePage).hasClass('page-gallery')) {
		gallerySelect(path);
	}
}

function getTarget(path) {
  var id = getTargetId(path);
	if ($('#' + id).length == 0) {
		return null;
	} else {
		return $('#' + id);
	}
}

function getBasePage(path) {
	var baseId = getBaseId(path);
	if (baseId) {
		return document.getElementById(baseId);
	} else {
		return null;
	}
}

function getBaseId(path) {
	var baseId = null;
	if (path == '/') {
		return 'page-/';
	}
  var match = path.match(/^(\/[^\/]*\/)/);
  if (match) {
  	baseId = 'page-' + match[1];
  }
	return baseId;
}

function getTargetId(path) {
	return 'page-' + path;
}

function menuSelect(path) {
	var basePage = getBasePage(path);
	var link = document.getElementById('menu-' + path);
	if (!basePage || !link || link.hostname != location.hostname) {
		return;
	}
	$(basePage).find('.menu .selected').removeClass('selected');
  $(link).addClass('selected');
  subpageSelect(basePage, path);
}

function gallerySelect(path) {
  var basePage = getBasePage(path);
  if (!basePage) {
  	return;
  }
  subpageSelect(basePage, path);
}

function subpageSelect(basePage, path) {
  var $subpage = $(document.getElementById('subpage-' + path));
  if ($subpage.hasClass('loaded')) {
  	subpageShow(basePage, $subpage);
  } else {
		$.ajax({
			url: path + '?ajax=1',
			success: function(html) {
				$subpage.html(html);
				$subpage.addClass('loaded');
				$subpage.fitVids();
				$subpage.find('img, iframe').each(function(i, el) {
				  $(el).load(function() {
						updatePageHeight(basePage, $subpage, el);
				  });
				});
				subpageShow(basePage, $subpage);
			}
		});
	}
}

function subpageShow(basePage, $subpage) {
	var $slider = $(basePage).find('.content .slider');
  $slider.animate({
		left: -$subpage.position().left
	}, 750, 'easeOutQuint');
	updatePageHeight(basePage, $subpage);
	if ($(basePage).hasClass('page-gallery')) {
		subpageBreadcrumbs(basePage, $subpage);
	}
}

function updatePageHeight(basePage, $subpage) {
	setTimeout(function() {
	  var $content = $(basePage).find('.content');
		var height = $subpage.data('height');
		if (!height) {
			height = $subpage.height();
			$subpage.data('height', height);
		}
		$content.css('height', height);
	}, 500);
}

function subpageBreadcrumbs(basePage, $subpage) {
	var html = $subpage.find('.breadcrumbs').html();
	var $breadcrumbs = $(basePage).find('> .title > .breadcrumbs');
  var orig = $breadcrumbs.find('.orig');
  if (orig.length == 0) {
  	if ($subpage.hasClass('basepage')) {
  		html = $breadcrumbs.html();
  	}
  	$breadcrumbs.html('<div class="orig">' + $breadcrumbs.html() + '</div>' +
  	                  '<div class="curr">' + html + '</div>');
  } else if ($subpage.hasClass('basepage')) {
  	html = $breadcrumbs.find('.orig').html();
  	$breadcrumbs.find('.curr').html(html);
  } else {
  	$breadcrumbs.find('.curr').html(html);
  }
}

$(document).ready(function() {
	$('a').live('click', function(e) {
		if (getBasePage(this.pathname) &&
				this.hostname == location.hostname) {
			e.preventDefault();
			window.location = '#' + this.pathname;
		}
	});
	$('section').each(function(i, el) {
	  var image = $(el).data('image');
	  if (image) {
	  	$(el).css('background-image', 'url(' + image + ')');
	  }
	  if ($(el).hasClass('page-menu')) {
	  	var links = $(el).find('.menu a');
	  	var locationPath = location.hash.substr(1);
	  	$(el).find('.slider').css('width', 664 * links.length);
	  	if ($(el).attr('id') != getBaseId(locationPath) ||
	  	    $(el).attr('id') == 'page-' + locationPath) {
	  		menuSelect(links[0].pathname);
	  	}
	  } else if ($(el).hasClass('page-gallery')) {
	  	var links = $(el).find('.gallery-icon a');
	  	var locationPath = location.hash.substr(1);
	  	$(el).find('.slider').css('width', 996 * (links.length + 1));
	  	if ($(el).attr('id') != getBaseId(locationPath) ||
	  	    $(el).attr('id') == 'page-' + locationPath) {
	  		gallerySelect($(el).attr('id').substr(5));
	  	}
	  }
	});
	$(window).on('hashchange', checkURLHash);
	checkURLHash();
	$('.content').fitVids();
	
});
	
})(jQuery);
