(function(){
	var self = $ar.model({
		id: '',
		email: '',
		password: '',
		display_name: ''
	}).validate({
		email: function(val){
			if(!$ar.type(val,'string') || !val.length)
				return ['set your email'];
		},
		password: function(val){
			if(!$ar.type(val,'string') || !val.length)
				return ['password required'];
		}
	}).watch();

	self.show = ko.observable(false);
	self.loading = ko.observable(false);
	self.error = ko.observable('');
	
	$ar.api.route('login','/api/Auth/Login');
	$ar.api.route('logout','/api/Auth/Logout');
	$ar.api.route('sessionCheck','/api/Auth/SessionCheck');
	
	$ar.route('#login',function(){
		self.show(true);
	},function(){
		self.show(false);
	});

	$ar.route('#logout',function(){
		console.log("Logging Out User");
		self.logout();
	});
	
	self.login = function(){
		if(!self.validate()){
			self.error('errors: ' + self.errors.join(', '));
			return;
		}
		self.loading(true);

		$ar.api.login(self.serialize(),function(resp){
			self.password('');
			if( 'error' == resp.status ){
				self.loading(false);
				self.error(resp.msg);
				return;
			}else if('ok' == resp.status){
				self.error('');
				$ar.pub('loggedin',resp);
			}			
			self.loading(false);
		});
	};
	
	self.logout = function(){
		$ar.api.logout({},function(resp){
			self.id('');
			self.email('');
			self.display_name('');
			self.password('');
			if( 'error' == resp.status ){
				self.loading(false);
				self.error(resp.msg);
				return;
			}else{
				self.error('');
			}

			$ar.pub('loggedout',resp);
			self.loading(false);
		});
	};

	$ar.sub('loggedin',function(resp){
		self.id(resp.user.id);
		self.email(resp.user.email);
		self.display_name(resp.user.display_name);
		self.show(false);
	});
	$ar.sub('loggedout',function(resp){
		window.location.hash = 'login';
	});


	$ar.init(function(){
		//before we go to far, lets check our session is already login
		$ar.api.sessionCheck({},function(resp){
			self.password('');
			if( 'ok' == resp.status ){
				console.log(resp);
				$ar.pub('loggedin',resp);
				self.loading(false);
				self.error('');
			}else{
				if(window.location.hash != '#passwordReset' && window.location.hash != '#passwordResetRequest' ){
					window.location.hash = 'login';
					console.log("Not Logged In:",window.location.hash);
				}else{
					window.location.hash = window.location.hash;
				}
			}
		});
		ko.applyBindings(self,$ar.dom('#login')[0]);
	});
	
	Auth = {};
	Auth.Login = self;
})();
