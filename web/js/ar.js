/**------------------------------------------------------------------------**\

	THE NAMESPACE!
	Here's where we dump our global abstractions. You can extend it
	by checking out the mixin function. By default, it comes with:
		$ar.type: unified type checking
		$ar.mixin: extend the namespace
		$ar.init: delay functionality until the dom is ready

		$ar.extend: smash objects together
		$ar.cache: access to cookies/local storage
		$ar.data_mapper: translate object keys
		$ar.load: lazy load some resources

		$ar.pub: publish data to the pubsub
		$ar.sub: subscribe to messages comming from the pubsub
		$ar.unsub: stop listening to that crap

		$ar.model: keep your data model clean
		$ar.modelToKo: make a model observable

		$ar.api: talking to the database with ajax

		$ar.dom: query selectors and basic manipulation

\**------------------------------------------------------------------------**/
window.$ar = window.$ar || (function(){
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
			else if(variable.hasOwnProperty('nodeType')){
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

//here's some util functions
$ar.mixin({
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

// $ar.pubsub
//		loose coupling for all your custom modules
//		make sure to document your messages, as they're
//		easy to bury in the code
$ar.mixin((function(){
	var cache = {};
	return {
		pub : function(){
			var topic = arguments[0],
				args = Array.prototype.slice.call(arguments, 1)||[],
				ni, t;

			for(t in cache){
				if(!(new RegExp(t)).test(topic))
					continue;
				for(ni = 0; ni < cache[t].length; ni++)
					cache[t][ni].apply($ar, args);
			}
		},
		sub : function(topic, callback){
			topic = '^' + topic.replace(/\*/,'.*');
			if(!cache[topic])
				cache[topic] = [];
			cache[topic].push(callback);
			return [topic, callback];
		},
		unsub : function(handle){
			var t = handle[0], ni;
			if(!cache[t]) return;
			for(ni in cache[t]){
				if(cache[t][ni] == handle[1])
					cache[t].splice(ni, 1);
			}
		}
	};
})());


// $ar.model is teh bones of the application
// requires $ar.stringToObject (sometimes)
$ar.mixin({
	model : (function(){
		// stuff to exclude from the serialization
		var blacklist = /^(_.*|def|pre|post|serialize|extend|map|type|watch|errors|validate)$/;

		// lets only add clean data to our models
		function _cleanNumbers(obj){
			var type = $ar.type(obj),
				ni;

			if(/^(b.*|nu.*|f.*)$/.test(type))
				return obj;

			if(type == 'string'){
				if(!obj || obj == 'null')
					obj = null;
				else if(!isNaN(parseFloat(obj)) && isFinite(obj))
					return parseFloat(obj);

				return obj;
			}

			if(type == 'array'){
				for(ni = 0; ni < obj.length; ni++)
					obj[ni] = _cleanNumbers(obj[ni]);
			}

			if(type == 'object'){
				for(ni in obj)
					obj[ni] = _cleanNumbers(obj[ni]);
			}

			return obj;
		}

		// something needed to normalize knockout stuff
		function _cleanRead(model,key){
			if(model.def[key].observable)
				return model[key]();
			return model[key];
		}
		// something needed to normalize knockout stuff
		function _cleanWrite(model,key,val){
			if(model.def[key].observable)
				model[key](val);
			else
				model[key] = val;
		}

		// does the heavy lifting for importing an object into a model
		function _sin(model,data){
			var ni, na, no, a;
			if(!data){
				// reset to default values
				for(ni in model.def)
					_cleanWrite(model,ni,model.def[ni]['default']);
				return model;
			}

			if($ar.type(model._pre,'array')){
				for(ni = 0; ni < model._pre.length; ni++)
					model._pre[ni](data);
			}

			for(ni in model.def){
				na = 'external' in model.def[ni]?model.def[ni].external:ni;
				if(!data.hasOwnProperty(na))
					continue;

				a = null;
				if(!model.def[ni].type){
					_cleanWrite(model,ni,_cleanNumbers(data[na]));
					continue;
				}
				if(!$ar.type(model.def[ni]['default'], 'array')){
					_cleanWrite(model,ni, new model.dev[ni].type(data[na]));
					continue;
				}

				a = [];
				data[na] = data[na]||[];
				for(no = 0; no < data[na].length; no++)
					a.push(new model.def[ni].type(data[na][no]));

				_cleanWrite(model,ni,a);
			}

			return model;
		}

		// does the same as _sin, but for exporting
		function _sout(model){
			var obj = {},
				uwrap = window.ko?ko.utils.unwrapObservable:function(a){ return a; },
				tmp, ni, na, no, a;
			for(ni in model.def){
				if(blacklist.test(ni))
					continue;

				tmp = uwrap(model[ni]);

				na = 'external' in model.def[ni]?model.def[ni].external:ni;

				//gotta look for models WITHIN models
				if(!tmp){
					obj[na] = tmp;
				} else if(tmp.hasOwnProperty('serialize')){
					obj[na] = tmp.serialize();
				} else if($ar.type(tmp,'array')){
					obj[na] = [];
					for(no = 0; no < tmp.length; no++){
						a = uwrap(tmp[no]);
						if($ar.type(a,'function')) continue;
						if($ar.type(a,'object') && a.hasOwnProperty('serialize'))
							a = a.serialize();
						obj[na].push(a);
					}
				} else if($ar.type(tmp,'object')){
					obj[na] = {};
					for(no in tmp){
						a = uwrap(tmp[no]);
						if($ar.type(a,'function')) continue;
						if($ar.type(a,'object') && a.hasOwnProperty('serialize'))
							a = a.serialize();
						obj[na][no] = a;
					}
				} else {
					if($ar.type(tmp,'function')) continue;
					obj[na] = tmp;
				}
			}

			if($ar.type(model._post,'array')){
				for(ni = 0; ni < model._post.length; ni++)
					model._post[ni](obj);
			}

			return obj;
		}

		// mmmmmm factory
		return function(def){
			var self = {
				_pre: [],
				_post: [],
				errors: [],
				def: {}
			};

			// all these functions chain!!!! GO NUTS!
			self.serialize = function(data){
				// no arguments, you export data from the model
				// with an object, you import
				if(arguments.length === 0)
					return _sout(self);
				return _sin(self,data);
			};
			self.extend = function(_def){
				// use models to make bigger models!
				var ni;
				for(ni in _def){
					if(blacklist.test(ni))
						continue;
					if(ni in self.def)
						continue;
					if(!$ar.type(_def[ni],'object'))
						_def[ni] = { 'default': _def[ni] };

					self.def[ni] = $ar.extend({
						'default':'',
						validation: []
					},_def[ni]);

					self[ni] = _def[ni]['default'];
				}

				return self;
			};
			self.map = function(_maps){
				// internal name on the left side, external on the right
				// for keeping your clean data model in sync with your ugly api
				for(var ni in _maps){
					if(!self.def.hasOwnProperty(ni)) continue;
					self.def[ni].external = _maps[ni];
				}
				return self;
			};
			self.type = function(_types){
				// to have hierarchical chains of models, we need to be able
				// to specify a model type for those properties 
				for(var ni in _types){
					if(!self.def.hasOwnProperty(ni)) continue;
					self.def[ni].type = _types[ni];
				}
				return self;
			};
			self.pre = function(filter){
				// here we add filters that edit the json data before it enters
				self._pre.push(filter);
				return self;
			};
			self.post = function(filter){
				// here we add filters that edit the json data before it leaves
				self._post.push(filter);
				return self;
			};
			self.watch = function(_map){
				var ni;
				//make all the things observable!
				if(!arguments.length){
					_map = {};
					for(ni in self.def)
						_map[ni] = true;
				}
				// this bad boy controls which properties are observable
				var pass_through = function(val){ return val; };
				for(ni in _map){
					if(!self.def.hasOwnProperty(ni)) continue;
					self.def[ni].observable = _map[ni];
					self[ni] = (_map[ni]?ko['observable' + ($ar.type(self.def[ni]['default'],'array')?'Array':'')]:pass_through)(ko.utils.unwrapObservable(self[ni]));
				}
				return self;
			};
			self.validate = function(_map){
				var ni,no,v,e;
				if(!arguments.length){
					self.errors = [];

					for(ni in self.def){
						v = self.def[ni].validation||[];
						for(no = 0; no < v.length; no++){
							e = v[no](_cleanRead(self,ni));
							if(!$ar.type(e,'array')) continue;
							self.errors = self.errors.concat(e);
						}
					}
					if(!self.errors.length)
						return true;
					return false;
				}

				for(ni in _map){
					self.def[ni].validation.push(_map[ni]);
				}

				return self;
			};
			self.sync = function(opt){
				if(opt.channel){
					$ar.sub(opt.channel, function(obj){
						if(!opt.key || !obj.hasOwnProperty(opt.key) || !self.hasOwnProperty(opt.key))
							return;
						var o_id = _cleanRead(obj,opt.key),
							s_id = _cleanRead(self,opt.key);
						if(!o_id || !s_id || o_id != s_id)
							return;
						self.serialize(obj.serialize());
					});
				}

				var _pre = [],
					_post = [];
				self.loading = ko.observable(false);
				self.pre_save = function(fun){
					if(fun){
						if(!$ar.type(fun,'function'))
							return;
						_pre.push(fun);
						return self;
					}
					var fine = true,
						ni;
					for(ni = 0; ni < _pre.length; ni++){
						fine = fine && _pre[ni](self);
					}
					return fine;
				};
				self.save = function(){
					if(!self.pre_save()) return;

					if(!opt.api && !opt.channel)
						return;
					if(!opt.api){
						$ar.pub(opt.channel,self);
						return;
					}
					self.loading(true);
					opt.api(self.serialize(),function(resp){
						self.loading(false);
						self.post_save();
						$ar.pub(opt.channel,self);
					});
				};
				self.post_save = function(fun){
					if(fun){
						if(!$ar.type(fun,'function'))
							return;
						_post.push(fun);
						return self;
					}
					var fine = true,
						ni;
					for(ni = 0; ni < _post.length; ni++){
						fine = fine && _post[ni](self);
					}
					return fine;
				};

				return self;
			};

			//initialization
			return self.extend(def);
		};
	})()
});

// $ar.dom:
//		A dom navigator. Experimental.
$ar.mixin({
	dom : (function(){
		//querySelectorAll Polyfill
		document.querySelectorAll = document.querySelectorAll||function(selector){
			var doc = document,
			head = doc.documentElement.firstChild,
			styleTag = doc.createElement('style');
			head.appendChild(styleTag);
			doc._qsa = [];

			styleTag.styleSheet.cssText = selector + "{x:expression(document._qsa.push(this))}";
			window.scrollBy(0, 0);

			return doc._qsa;
		};
		//matchesSelector Polyfill
		Element.prototype.matchesSelector = Element.prototype.webkitMatchesSelector || Element.prototype.mozMatchesSelector || function(selector){
			var els = document.querySelectorAll(selector),
				ni,len;
			for(ni=0, len=els.length; ni<len; ni++ ){
				if(els[ni] == this)
					return true;
			}
			return false;
		};

		var cleanSelector = function(selector,_context){
			if(!selector.length)
				return [];
			var sels = selector.split(','),
				context = _context||document,
				res = [],
				ni,idpos,ctx;
			for(ni = 0; ni < sels.length; ni++){
				idpos = sels[ni].lastIndexOf('#');
				ctx = context;
				if(idpos > 0){
					ctx = document.getElementById(sels[ni].substr(idpos).match(/^#[^\s]*/)[0]);
					sels[ni] = sels[ni].substr(idpos).replace(/^#[^\s]*[\s]*/,'');
				}
				if(!sels[ni].length) continue;
				res = res.concat(Array.prototype.slice.call(ctx.querySelectorAll(sels[ni]),0));
			}

			return res;
		};

		var cssCap = function(a,x){ return x.toUpperCase(); };

		var _css = function(dom,obj){
			if($ar.type(obj,'string'))
				return dom[0].style[obj.replace(/-(\w)/g,cssCap)];

			var ni,no;
			for(ni = 0; ni < dom._len; ni++){
				for(no in obj)
					dom[ni].style[no.replace(/-(\w)/g,cssCap)] = obj[no];
			}
			return dom;
		},
		_addClass = function(dom,selector){
			var sels = selector.split(','),
				len = dom._len,
				ni,no;

			dom.removeClass(selector);

			for(ni = 0; ni < len; ni++){
				for(no = 0; no < sels.length; no++)
					dom[ni].className += ' ' + sels[no].replace(/(^\s*|\s*$)/g,'');
			}

			return dom;
		},
		_removeClass = function(dom,selector){
			var sels = selector.split(','),
				len = dom._len,
				ni,no,cname;
			for(ni = 0; ni < len; ni++){
				cname = ' ' + dom[ni].className.replace(/\s+/g,' ');
				for(no = 0; no < sels.length; no++)
					cname = cname.replace(new RegExp('\\s' + sels[no].replace(/(^\s*|\s*$)/g,''),'g'),'');
				dom[ni].className = cname.slice(1);
			}

			return dom;
		},
		_matches = function(dom,selector){
			var has = false,
				ni;
			for(ni = 0; ni < dom._len; ni++){
				has = has || !!dom[ni].matchesSelector(selector);
			}
			return has;
		},
		_find = function(dom,selector){
			return DomObj(selector,dom);
		},
		_closest = function(dom,selector){
			var elems = [],
				cap = document.documentElement,
				ni,no,len,curr,found;

			if(typeof selector != 'string' && !selector.hasOwnProperty('_len'))
				throw new Error('invalid selector passed to $ar.dom.closest');

			for(ni = 0; ni < dom._len; ni++){
				curr = dom[ni];
				while(curr != cap){
					if(typeof selector != 'string'){
						found = false;
						for(no = 0; no < selector._len; no++){
							if(curr != selector[no]) continue;
							found = true;
							break;
						}
						if(found) break;
					} else if(curr.matchesSelector(selector)) break;

					try {
						curr = curr.parentNode;
					} catch(e){
						console.log(selector);
						console.log(curr);
						throw e;
					}
				}
				if(curr == cap) continue;
				elems.push(curr);
			}
			len = elems.length;
			for(ni = 0; ni < len; ni++){
				for(no = ni+1; no < len;no++){
					if(elems[ni]!=elems[no]) continue;
					elems.splice(no--,1);
					len--;
				}
			}

			curr = DomObj(null,this);
			len = curr._len = elems.length;
			for(ni = 0; ni < len; ni++)
				curr[ni] = elems[ni];
			return curr;
		},
		_remove = function(dom){
			var ni,len;
			for(ni = 0, len = this._len; ni < len; ni++){
				if(!dom[ni].parentNode) continue;
				dom[ni].parentNode.removeChild(dom[ni]);
			}
			return dom;
		},
		_before = function(dom,elem){
			var ni, no;
			if(!elem.hasOwnProperty('_len'))
				elem = $ar.dom(elem);
			for(ni = 0; ni < dom._len; ni++){
				if(!dom[ni].parentNode) continue;
				for(no = 0; no < elem._len; no++){
					dom[ni].parentNode.insertBefore(elem[no],dom[ni]);
				}
			}
			return dom;
		},
		_after = function(dom,elem){
			var ni, no;
			if(!elem.hasOwnProperty('_len'))
				elem = $ar.dom(elem);
			for(ni = 0; ni < dom._len; ni++){
				if(!dom[ni].parentNode) continue;
				for(no = 0; no < elem._len;no++)
					dom[ni].parentNode.insertBefore(elem[no],dom[ni].nextSibling);
			}
		};

		var DomObj = function(selector, context){
			var self = {
				_back: null,
				_len: 0,
				_selector: ''
			};

			//some static functions
			self.css = function(obj){ return _css(self,obj); };
			self.addClass = function(selector){ return _addClass(self,selector); };
			self.removeClass = function(selector){ return _removeClass(self, selector); };
			self.matches = function(selector){ return _matches(self,selector); };
			self.find = function(selector){ return _find(dom,selector); };
			self.closest = function(selector){ return _closest(self,selector); };
			self.remove = function(){ return _remove(self); };
			self.back = function(){ return self._back; };
			self.length = function(){ return self._len; };
			self.get = function(index){
				if(index < 0 || index > self._len)
					return;
				return DomObj(self[index],self);
			};
			self.before = function(elem){ return _before(self,elem); };
			self.after = function(elem){ return _after(self,elem); };
			self.html = function(str){
				if(!arguments.length)
					return self[0].innerHTML||'';
				var ni;
				for(ni = 0; ni <= self._len; ni++){
					self[ni].innerHTML = str;
				}
				return self;
			};
			self.append = function(elem){
				var ni,no;
				for(ni = 0; ni < self._len; ni++){
					for(no = 0; no < elem._len; no++)
						self[ni].appendChild(elem[no]);
				}
			};
			(function(dom){
				var events = {};

				dom.on = function(evt,fun){
					if(!events[evt]){
						events[evt] = (function(){
							var s = {
								fun: null,
								routes: []
							};
							s.fun = function(_e){
								var t = $ar.dom(_e.target),
									ni;
								for(ni = 0; ni < s.routes.length; ni++){
									if(!_closest(t,s.routes[ni].dom).hasOwnProperty('_len')){
										console.log('not found');
										continue;
									}
									s.routes[ni].callback(_e);
								}
							};
							return s;
						})();

						if(window.addEvent){
							window.addEvent('on'+evt, events[evt].fun);
						} else if(window.addEventListener){
							window.addEventListener(evt,events[evt].fun,false);
						}
					}

					events[evt].routes.push({ dom: dom, callback: fun });
				};
				dom.off = function(evt,fun){
					if(!events[evt] || !events[evt].routes.length) return;
					var r = events[evt].routes, ni;
					for(ni = r.length; ni > 0;){
						if(r[--ni].dom != dom) continue;
						if(fun && r[ni].callback != fun) continue;
						r.splice(ni,1);
					}
				};
			})(self);

			self._back = context;
			if(!selector) return self;
			if($ar.type(selector,'node')){
				self[0] = selector;
				self._len = 1;
				return self;
			}
			if(/^[^<]*(<[\w\W]+>)[^>]*$/.test(selector)){
				var elem = document.createElement('div'),
					no,c;
				elem.innerHTML = selector.replace(/(^\s*|\s*$)/g,'');
				c = elem.childNodes;
				self._len = c.length;
				for(no = 0; no < self._len; no++){
					self[no] = c[no];
				}
				return self;
			}
			//need to add ability to create element or take normal element
			self._selector = selector;

			if(!selector) return self;

			var res = [],ni;
			if(context && context._len){
				for(ni = 0; ni < context._len; ni++){
					res = res.concat(cleanSelector(selector,context[ni]));
				}
			} else {
				res = cleanSelector(selector);
			}
			for(ni = 0; ni < res.length; ni++){
				self[ni] = res[ni];
			}
			self._len = res.length;

			return self;
		};

		return function(selector){
			return DomObj(selector);
		};
	})()
});

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

// $ar.api:
//		Here we normalize how we talk to the backend
//		You can change options for the api calls by manipulating
//		$ar.api.config
// requires $ar.extend, $ar.cache
$ar.mixin({
	api : (function(config){
		var self = {
				config: $ar.extend({
					crossDomain: false,
					url: 'http://devmanage.activityrez.com/api',
					cache_key: 'ACTIVITYREZ',
					useLocalStorage: false,
					token: ''
				},config)
			};
		self.config.token = $ar.cache.read(self.config.cache_key,self.config.useLocalStorage);
		function postString(obj, prefix){
			var str = [], p, k, v;
			if($ar.type(obj,'array')){
				if(!prefix)
					throw new Error('Sorry buddy, your object is wrong');
				for(p = 0; p < obj.length; p++){
					k = prefix + "[" + p + "]";
					v = obj[p];
					str.push(typeof v == "object"?postString(v,k):encodeURIComponent(k) + "=" + encodeURIComponent(v));
				}
			}
			for(p in obj) {
				if(prefix)
					k = prefix + "[" + p + "]";
				else
					k = p;
				v = obj[p];
				str.push(typeof v == "object"?postString(v,k):encodeURIComponent(k) + "=" + encodeURIComponent(v));
			}
			return str.join("&");
		}

		self.raw = (function(){
			function xhr(options){
				var origin, parts, crossDomain, _ret;
				function createStandardXHR(){ try { return new window.XMLHttpRequest(); } catch(e){} }
				function createActiveXHR(){ try { return new window.ActiveXObject("Microsoft.XMLHTTP"); } catch(e){} }
				function createJSONP(){
					function randomer(){
						var s=[],itoh = '0123456789ABCDEF',i;

						for(i = 0; i < 16; i++){
							s[i] = i==12?4:Math.floor(Math.random()*0x10);
							if(i==16) s[i] = (s[i]&0x3)|0x8;
							s[i] = itoh[s[i]];
						}
						return s.join('');
					}

					var ret = {
						_options: {
							key: '',
							url: '',
							script: null,
							mime: 'json'
						},
						readyState: 0,
						onreadystatechange: null,
						response: null,
						responseText: null,
						responseXML: null,
						responseType: '',

						status: null,
						statusText: '',
						timeout: 0,

						upload: null
					};

					ret.abort = function(){
						if(ret.readyState != 3) return;
						ret._options.script.parentNode.removeChild(ret._options.script);
						$ar.api[ret._options.key] = function(){
							delete $ar.api[ret._options.key];
						};

						ret.readyState = 1;
						if(typeof ret.onreadystatechange == 'function')
							ret.onreadystatechange();
					};
					ret.getAllResponseHeaders = function(){};
					ret.getResponseHeader = function(header){};
					ret.open = function(method,url,async,user,pass){
						//method is always get, async is always true, and user/pass do nothing
						//they're still there to provide a consistant interface
						ret._options.url = url;
						ret._options.script = document.createElement('script');
						ret._options.script.type = 'text/javascript';
						ret.readyState = 1;
						if(typeof ret.onreadystatechange == 'function')
							ret.onreadystatechange();

						document.body.appendChild(ret._options.script);
					};
					//this does nothing
					ret.overrideMimeType = function(mime){};
					ret.send = function(data){
						ret._options.key = 'jsonp_'.randomer();

						var _data = postString(data),
							url = ret._options.url+'?callback=$ar.api.'+ret._options.key+(_data.length?'&'+_data:'');

						$ar.api[ret._options.key] = function(data){
							ret.responseText = data;

							if(ret.responseType === '' || ret.responseType == 'text'){
								ret.response = data;
							}
							if(ret.responseType == 'arraybuffer'){
								if(!base64DecToArr) throw new Error('arraybuffer not supported in jsonp mode');
								ret.response = base64DecToArr(data).buffer;
							}
							if(ret.responseType == 'blob'){
								if(!Blob) throw new Error('blob not supported in jsonp mode');
								ret.response = new Blob([data],{ 'type': 'text/plain' });
							}
							if(ret.responseType == 'document'){
								throw new Error('document not supported in jsonp mode');
							}
							if(ret.responseType == 'json'){
								ret.response = JSON.parse(data);
							}

							ret.readyState = 4;
							if(typeof ret.onreadystatechange == 'function')
								ret.onreadystatechange();
							ret._options.script.parentNode.removeChild(ret._options.script);

							delete $ar.api[ret._options.key];
						};

						ret.readyState = 3;
						if(typeof ret.onreadystatechange == 'function')
							ret.onreadystatechange();

						ret._options.script.src = url;
					};
					//this does nothing
					ret.setRequestHeader = function(header, value){};

					return ret;
				}

				try {
					origin = location.href;
				} catch(e){
					origin = document.createElement( "a" );
					origin.href = "";
					origin = origin.href;
				}

				origin = /^([\w.+-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/.exec(origin.toLowerCase());
				options.url = (( options.url ) + "").replace(/#.*$/, "").replace(/^\/\//, origin[1] + "//");
				parts  = /^([\w.+-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/.exec(options.url.toLowerCase());
				origin[3] = origin[3]||(origin[1]=='http:'?'80':'443');
				parts[3] = parts[3]||(parts[1]=='http:'?'80':'443');

				crossDomain = !!(parts &&
					( parts[1] !== origin[1] ||
						parts[2] !== origin[2] ||
						parts[3] != origin[3]
					)
				);

				_ret = window.ActiveXObject ?
					function() {
						return !/^(?:about|app|app-storage|.+-extension|file|res|widget):$/.test(origin[1]) && createStandardXHR() || createActiveXHR();
					} : createStandardXHR;
				_ret = _ret();

				if(!_ret || (crossDomain && !_ret.hasOwnProperty('withCredentials')))
					_ret = createJSONP();

				return _ret;
			}

			function ajax(params){
				params = $ar.extend({
					url: '',
					method: 'GET',
					type: 'json',
					async: 'true',
					jsonp: 'callback',	//currently unused
					timeout: 0,
					data: null,

					succes: null,
					error: null
				},params);

				var _xhr = xhr(params);
				if(params.method == 'GET')
					params.url += '?' + postString(params.data);
				_xhr.open(params.method,params.url,params.async);
				_xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				_xhr.responseType = params.type;
				_xhr.onreadystatechange = function(){
					if(_xhr.readyState != 4) return;
					if(_xhr.status != 200 && typeof params.error == 'function')
						params.error(_xhr.response);
					if(_xhr.status == 200 && typeof params.success == 'function')
						params.success(_xhr.response);
				};
				console.log(postString(params.data));
				_xhr.send(params.method=='POST'?postString(params.data):null);
			}

			return function(url,data,callback,method){
				ajax({
					method: method||'GET',
					url: url,
					data: data,
					success: function(result){
						if(typeof callback == 'function')
							callback(result);
					}
				});
			};
		})();
		self.download = function(url,data,callback){
			window.open(url +'?'+postString(data),'_blank');
			if(typeof callback == 'function')
				callback();
		};
		self.request = function(url,params,callback,method){
			self.raw(self.config.url+url, $ar.extend(params||{},{
				token: self.config.token||'NEW'
			}), function(result){
				try {
					result = JSON.parse(result);
				} catch(e){
					console.log('you have and error on the server');
					return;
				}
				if(result.token && result.token != self.config.token){
					$ar.cache.write(self.config.cache_key,result.token);
					self.config.token = result.token;
				}

				if(typeof callback == 'function')
					callback(result);
			},method||'GET');
		};
		// if you're going to be using pre and post filters on the data
		// make sure you return true to continue the chain, or return
		// false to cancel it
		self.route = function(name,url,pre,post,method){
			name = name.trim();
			pre = pre||function(data){ return true; };
			post = post||function(data){ return true; };

			if(/^(route|raw|request|config)$/.test(name))
				throw new Error('invalid name sent to $ar.api.route');

			self[name] = function(params,callback){
				if(!pre(params)) return;
				self.request(url,params,function(data){
					if(post(data) && typeof callback == 'function')
						callback(data);
				},method||'GET');
			};
		};

		return self;
	})()
});
