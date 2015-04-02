/* {"requires":["web/js/knockout.js", "web/js/drdelambre/src/modules/api.js", "web/js/drdelambre/src/modules/model.js", "web/js/drdelambre/src/modules/route.js", "web/js/drdelambre/src/modules/dom.js", "web/js/drdelambre/src/modules/pubsub.js", "web/js/drdelambre/src/modules/ioc.js"]} */
(function(){
	var self = $dd.model({
		id: '',
		email: '',
		password: '',
		display_name: ''
	}).validate({
		email: function(val){
			if(!$dd.type(val,'string') || !val.length)
				return ['set your email'];
		},
		password: function(val){
			if(!$dd.type(val,'string') || !val.length)
				return ['password required'];
		}
	});

	self.show = ko.observable(false);
	self.loading = ko.observable(false);
	self.error = ko.observable('');
	
	$dd.api.route('login','/Molotov/api/Auth/Login');
	$dd.api.route('logout','/Molotov/api/Auth/Logout');
	$dd.api.route('sessionCheck','/Molotov/api/Auth/SessionCheck');
	
	$dd.route('#login',function(){
		self.show(true);
	},function(){
		self.show(false);
	});

	$dd.route('#logout',function(){
		console.log("Logging Out User");
		self.logout();
	});
	
	self.login = function(){
		if(!self.validate()){
			self.error('errors: ' + self.errors.join(', '));
			return;
		}
		self.loading(true);

		$dd.api.login(self.out(),function(resp){
			self.password = '';
			if( 'error' == resp.status ){
				self.loading(false);
				self.error(resp.msg);
				return;
			}else if('ok' == resp.status){
				self.error('');
				$dd.pub('loggedin',resp);
			}			
			self.loading(false);
		});
	};
	
	self.logout = function(){
		$dd.api.logout({},function(resp){
			self.id = '';
			self.email = '';
			self.display_name = '';
			self.password = '';
			if( 'error' == resp.status ){
				self.loading(false);
				self.error(resp.msg);
				return;
			}else{
				self.error('');
			}

			$dd.pub('loggedout',resp);
			self.loading(false);
		});
	};

	$dd.sub('loggedin',function(resp){
		self.id = resp.user.id;
		self.email = resp.user.email;
		self.display_name = resp.user.display_name;
		self.show(false);
	});
	$dd.sub('loggedout',function(resp){
		window.location.hash = 'login';
	});
	
	
	$dd.ioc.register('login',function(){
		return self;
	});


	$dd.init(function(){
		//before we go to far, lets check our session is already login
		$dd.api.sessionCheck({},function(resp){
			self.password = '';
			if( 'ok' == resp.status ){
				console.log(resp);
				$dd.pub('loggedin',resp);
				self.loading(false);
				self.error('');
			}else{
				if(window.location.hash == ''){
					window.location.hash = 'login';
					console.log("Not Logged In:",window.location.hash);
				}else{
					$dd.pub('loggedout',resp);
					window.location.hash = window.location.hash;
				}
			}
		});
		ko.applyBindings(self,$dd.dom('#login')[0]);
	});
	
})();
