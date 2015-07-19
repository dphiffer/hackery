(function($) {

	"use strict";
	
	function PageMenu(el) {
		this.el = el;
		this.firstRun = true;
		var self = this;
		if (hackery.stack) {
			this.checkURLHash();
			$(window).on('hashchange', function() {
			  self.checkURLHash();
			});
			$(el).find('.menu a').click(hackery.stack.clickHandler);
		}
	}
	
	PageMenu.prototype.checkURLHash = function() {
		var locationPath = location.hash.substr(1);
		var basePage = $(this.el).attr('id');
		if (basePage == hackery.stack.getBaseId(locationPath) &&
				basePage != 'page-' + locationPath) {
			this.subpageSelect(locationPath);
		} else if (this.firstRun) {
			var links = $(this.el).find('.menu a');
			this.firstRun = false;
			this.subpageSelect(links[0].pathname);
		}
	}
	
	PageMenu.prototype.subpageSelect = function(path) {
		if (hackery.stack) {
			var basePage = $(this.el).attr('id');
			$(this.el).find('.menu .selected').removeClass('selected');
			$(document.getElementById('menu-' + path)).addClass('selected');
			hackery.stack.subpageSelect(basePage, path);
		}
	};
	
	/*
		
		var menu = this;
		
			
			
		});
	}
	
	if ($(el).hasClass('page-menu')) {
	  	
	  	
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
}*/

	hackeryInit({
		id: 'menu',
		constructor: PageMenu,
		query: '.page-menu'
	});

})(jQuery);
