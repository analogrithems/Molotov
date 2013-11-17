/* {"requires":["ar.js"]} */

// $ar.di
//		a simple place to keep our modules lazy loaded and out of global
$ar.mixin({
	di: (function(){
		var ret = {},
			hash = {};

		ret.register = function(lookup,constructor){
			hash[lookup] = { c: constructor, v: null };
		};

		ret.get = function(lookup){
			if(!hash.hasOwnProperty(lookup))
				throw new Error('$ar.di: nothing hooked up to ' + lookup);
			if(!hash[lookup].v)
				hash[lookup].v = new hash[lookup].c();
			return hash[lookup].v;
		};

		return ret;
	})()
});
