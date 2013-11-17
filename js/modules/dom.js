/* {"requires":["ar.js"]} */
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
					if(!curr.parentNode){
						curr = cap;
						break;
					}
					curr = curr.parentNode;
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
			self.find = function(selector){ return _find(self,selector); };
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
				for(ni = 0; ni < self._len; ni++){
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
