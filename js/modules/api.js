/* {"requires":["ar.js","modules/utils.js"]} */
// $ar.api:
//		Here we normalize how we talk to the backend
//		You can change options for the api calls by manipulating
//		$ar.api.config
$ar.mixin({
	api : (function(config){
		var self = {
				config: $ar.extend({
					crossDomain: false,
					url: 'https://vault.asynonymous.net',
					cache_key: 'API_CACHE',
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

						document.head.appendChild(ret._options.script);
					};
					//this does nothing
					ret.overrideMimeType = function(mime){};
					ret.send = function(data){
						ret._options.key = 'jsonp_'+randomer();

						var _data = postString(data),
							url = ret._options.url;
						url += (url.indexOf('?') == -1?'?':'&');
						url += 'callback=$ar.api.'+ret._options.key;

						if(_data.length)
							url += '&'+_data;

						$ar.api[ret._options.key] = function(data){
							if(!$ar.type(data,'string'))
								data = JSON.stringify(data);
							ret.responseText = data;
							ret.response = data;
							ret.readyState = 4;
							ret.status = 200;
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
			self.raw(self.config.url+url, $ar.extend(params||{}), function(result){
				if(result){
					try {
						if(typeof result == 'string') result = JSON.parse(result);
					} catch(e){
						console.log('you have an error on the server:',result);
						return;
					}
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