// $ar.route
//		LETS DO SOME SINGLE PAGE APPS!
$ar.mixin({
	// before you get all crazy, this just exposes one function that allows one
	// to set up callbacks for when the page is navigated to a hash
	// as well as when it's leaving a hash. also lets you pass variables to the
	// open function by setting up your path.
	// path:  /beans/:id/:username?/cool has an optional username param and always
	//			passes an id to the open function. beans and cool are static
	route : (function(){
		var paths = {},
			current = null;

		function handleChange(){
			var hash = window.location.hash.replace(/^#!?\//,''),
				ni,no,args;

			for(ni in paths){
				if(!paths[ni].regexp.test(hash)) continue;
				if(ni === '' && hash.length) continue;
				if(hash === current) continue;

				if(paths[current]){
					for(no = 0; no < paths[current].after.length; no++){
						if(typeof paths[current].after[no] == 'function')
							paths[current].after[no]();
					}
				}

				args = paths[ni].regexp.exec(hash).splice(1);
				for(no = 0; no < paths[ni].before.length; no++){
					paths[ni].before[no].apply(null,args);
				}
				current = ni;
			}
		}

		window.addEventListener('hashchange',handleChange,false);
		$ar.init(function(){ handleChange(); });

		return function(path,open,close){
			keys = [];
			path = path
				.replace(/\/\(/g, '(?:/')
				.replace(/(\/)?(\.)?:(\w+)(?:(\(.*?\)))?(\?)?/g, function(_, slash, format, key, capture, optional){
					keys.push({ name: key, optional: !! optional });
					slash = slash || '';
					return '' + (optional ? '' : slash) + '(?:' + (optional ? slash : '') + (format || '') + (capture || (format && '([^/.]+?)' || '([^/]+?)')) + ')' + (optional || '');
				})
				.replace(/([\/.])/g, '\\$1')
				.replace(/\*/g, '(.*)');
			if(!paths[path])
				paths[path] = {
					regexp: new RegExp(path),
					keys: keys,
					before: [],
					after: []
				};
			if(typeof open == 'function')
				paths[path].before.push(open);
			if(typeof close == 'function')
				paths[path].after.push(close);
		};
	})()
});