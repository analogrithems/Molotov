// $ar.model is teh bones of the application
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
				na = model.def[ni].external?model.def[ni].external:ni;
				if(!data.hasOwnProperty(na)){
					if(!data.hasOwnProperty(ni))
						continue;
					na = ni;
				}

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
					if(!$ar.type(fun,'function'))
						return;
					_pre.push(fun);
					return self;
				};
				self.save = function(){
					var ni, fine = true;
					for(ni = 0; ni < _pre.length; ni++)
						fine =  fine && _pre[ni]();
					if(!fine) return;

					if(!opt.api && !opt.channel)
						return;
					if(!opt.api){
						$ar.pub(opt.channel,self);
						return;
					}
					self.loading(true);
					opt.api(self.serialize(),function(resp){
						self.loading(false);

						var ni;
						for(ni = 0; ni < _post.length; ni++)
							_post[ni](resp);

						$ar.pub(opt.channel,self);
					});
				};
				self.post_save = function(fun){
					if(!$ar.type(fun,'function'))
						return;
					_post.push(fun);
					return self;
				};

				return self;
			};

			//initialization
			return self.extend(def);
		};
	})()
});