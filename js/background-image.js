(function($) {
  
	"use strict";
	
	function BackgroundImage(el) {
	  var src = $(el).data('background-image');
	  if (src) {
	  	$(el).css('background-image', 'url(' + src + ')');
	  }
	};
	
	hackeryInit({
		id: 'background_image',
		constructor: BackgroundImage,
		query: '.format-image'
	});
	
})(jQuery);
