"use strict";

var hackery = {};

function hackeryInit(config) {
	var singleton = config.singleton || false;
	jQuery(config.query).each(function(i, el) {
		var obj = new config.constructor(el);
		if (singleton) {
			hackery[config.id] = obj;
		} else if (!hackery[config.id]) {
			hackery[config.id] = [obj];
		} else {
			hackery[config.id].push(obj);
		}
	});
}

jQuery('.content').fitVids();
