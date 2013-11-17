/* {"requires":["ar.js"]} */
$ar.mixin({
	// $ar.clone:
	//		lets keep our data clean and seperated
	clone: function(obj){
		var type = $ar.type(obj);
		if(!/^(object||array||date)$/.test(type))
			return obj;
		if(type == 'date')
			return (new Date()).setTime(obj.getTime());
		if(type == 'array')
			return obj.slice(0);

		var copy = {},
			ni;

		for(ni in obj) {
			if(obj.hasOwnProperty(ni))
				copy[ni] = $ar.clone(obj[ni]);
		}

		return copy;
	},

	// $ar.extend:
	//		throw a bunch of objects in and it smashes
	//		them together from right to left
	//		returns a new object
	extend : function(){
		if(!arguments.length)
			throw new Error('$ar.extend called with too few parameters');

		var out = {},
			ni,no;

		for(ni = 0; ni < arguments.length; ni++){
			if(!$ar.type(arguments[ni],'object'))
				continue;
			for(no in arguments[ni])
				out[no] = arguments[ni][no];
		}

		return out;
	},

	// $ar.cache
	//		interface for dealing with frontend variable caching
	cache : {
		read: function(key,inLocalStorage){
			if(!inLocalStorage){
				return ((new RegExp("(?:^" + key + "|;\\s*"+ key + ")=(.*?)(?:;|$)", "g")).exec(document.cookie)||[null,null])[1];
			}
			throw new Error('nobody has written me yet');
		},
		write: function(key, value, expires, inLocalStorage){
			if(!inLocalStorage){
				document.cookie = key + "=" + escape(value) + ";path=/;domain=" + window.location.host;
				return;
			}
			throw new Error('nobody has written me yet');
		},
		remove: function(key, inLocalStorage){
			if(!inLocalStorage){
				if(!$ar.cache.read(key)) return;
				$ar.cache.write(key, "");
				return;
			}
			throw new Error('nobody has written me yet');
		}
	},

	// $ar.data_mapper:
	//		Throw some crap and shift it around
	data_mapper : function(map,data){
		if(!map || !data) return;
		for(var ni in map){
			if(!(ni in data)) continue;
			data[map[ni]] = data[ni];
			delete data[ni];
		}

		return data;
	},

	// $ar.load:
	//		Load some content dynamically
	load : function(path,callback){
		var d = document,
			html = /\.(htm|html|php)$/.test(path),
			js = /\.js$/.test(path),
			elem;

		if(html) throw new Error('I still need to be written');
		if(!/\.(js|css)$/.test(path)) return;
		elem = d.createElement(js?'script':'link');
		elem[js?'src':'href'] = path;
		if(!js) elem.rel = 'stylesheet';

		if(typeof callback == 'function')
		elem.onload = callback;

		d.body.appendChild(elem);
	}
});
