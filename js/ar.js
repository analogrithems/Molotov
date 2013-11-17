/* {"defines":"ar"} */
/**------------------------------------------------------------------------**\

	THE NAMESPACE!
	Here's where we dump our global abstractions. You can extend it
	by checking out the mixin function. By default, it comes with:
		$ar.type: unified type checking
		$ar.mixin: extend the namespace
		$ar.init: delay functionality until the dom is ready

\**------------------------------------------------------------------------**/
window.$ar = (function(){
	var self = {};

	// $ar.type:
	//		lets shrink some code. Calling without the type variable
	//		just returns the type, calling it with returns a boolean
	//		you can pass a comma seperated list of types to match against
	self.type = function(variable,type){
		var t = typeof variable,
			trap = false,
			more,ni;
		if(t == 'object'){
			more = Object.prototype.toString.call(variable);
			if(more == '[object Array]')
				t = 'array';
			else if(more == '[object Null]')
				t = 'null';
			else if(variable == window)
				t = 'node';
			else if(variable.nodeType){
				if(variable.nodeType == 1)
					t = 'node';
				else
					t = 'textnode';
			}
		}

		if(!type) return t;
		type = type.split(',');
		for(more = 0; more < type.length; more++)
			trap = trap || (type[more] == t);
		return t == type;
	};

	// $ar.mixin:
	//		This is how we extend the namespace to handle new functionality
	//		it'll overwrite crap, so be carefull
	self.mixin = function(obj){
		if(!self.type(obj,'object'))
			throw new Error('$ar.mixin called with incorrect parameters');
		for(var ni in obj){
			if(/(mixin)/.test(ni))
				throw new Error('mixin isn\'t allowed for $ar.mixin');
			self[ni] = obj[ni];
		}
	};

	// $ar.init:
	//		Stores up function calls until the document.ready
	//		then blasts them all out
	self.init = (function(){
		var c = [], t, ni;
		t = setInterval(function(){
			if(!document.body) return;
			clearInterval(t);
			t = null;
			for(ni = 0; ni < c.length; ni++)
				c[ni]();
		},10);
		return function(_f){
			if(!t)
				_f();
			else
				c.push(_f);
		};
	})();

	return self;
})();