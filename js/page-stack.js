(function($) {

	"use strict";	
	
	function PageStack(el) {
		this.el = el;
		this.setupClickHandlers(el);
		this.checkURLHash();
		$(window).on('hashchange', this.checkURLHash);
	}
	
	PageStack.prototype.setupClickHandlers = function(el) {
		var stack = this;
	  $(document).ready(function() {
			$(el).find('a').click(stack.clickHandler);
		});
	};
	
	PageStack.prototype.clickHandler = function(e) {
	  if (getBasePage(this.pathname) &&
		    this.hostname == location.hostname) {
			e.preventDefault();
			window.location = '#' + this.pathname;
		}
	}
	
	PageStack.prototype.checkURLHash = function() {
		var match = location.hash.match(/^#(\/.*)/);
		if (!match) {
			return;
		}
		var path = match[1];
		var basePage = getBasePage(path);
		if (basePage) {
			$('html, body').animate({
				scrollTop: $(basePage).offset().top
			}, 750, 'easeOutQuint');
		}
	};
	
	PageStack.prototype.subpageSelect = function(basePage, path) {
		var $basePage = $(document.getElementById(basePage));
		var $subpage = $(document.getElementById('subpage-' + path));
		if ($subpage.hasClass('loaded')) {
			subpageShow($basePage, $subpage);
		} else {
			$.ajax({
				url: path + '?ajax=1',
				success: function(html) {
					$subpage.html(html);
					$subpage.addClass('loaded');
					$subpage.fitVids();
					subpageShow($basePage, $subpage);
				}
			});
		}
	};
	
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
	
	function getTarget(path) {
		var id = getTargetId(path);
		var el = document.getElementById(id);
		if (!el) {
			return null;
		} else {
			return $(el);
		}
	};
	
	function getTargetId(path) {
		return 'page-' + path;
	}
	
	function subpageShow($basePage, $subpage) {
		$basePage.find('.subpage.selected').removeClass('selected');
		$subpage.addClass('selected');
	}
	
	PageStack.prototype.getBaseId = getBaseId;
	
	hackeryInit({
		id: 'stack',
		query: '.page-stack',
		constructor: PageStack,
		singleton: true
	});

})(jQuery);
