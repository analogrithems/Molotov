(function(){
	var self = $ar.model({
		username: '',
		password: '',
		email: '',
		company: ''
	}).validate({
		username: function(val){
			if(!$ar.type(val,'string') || !val.length)
				return ['username required'];
		},
		password: function(val){
			if(!$ar.type(val,'string') || !val.length)
				return ['password required'];
		},
		email: function(val){
			if(!self.signup())
				return;
			if(!$ar.type(val,'string') || !val.length)
				return ['set your email'];
		},
		company: function(val){
			if(!self.signup())
				return;
			if(!$ar.type(val,'string') || !val.length)
				return ['set your company name'];
		}
	}).watch();

	self.show = ko.observable(true);
	self.loading = ko.observable(false);
	self.error = ko.observable('');
	self.signup = ko.observable(false);

	self.toggle_sign = function(){
		self.signup(!!!self.signup());
	};
	self.login = function(){
		if(!self.validate()){
			self.error('errors: ' + self.errors.join(', '));
			return;
		}
		self.loading(true);
		$ar.api.login(self.serialize(),function(resp){
			self.password('');
			if(resp.error){
				self.loading(false);
				self.error(resp.error);
				return;
			}

			$ar.pub('loggedin',resp);
			self.loading(false);
		});
	};

	$ar.sub('loggedin',function(resp){
		if(resp.id == 0)
			return;
		self.show(false);
	});
	$ar.sub('loggedout',function(resp){ self.show(true); });


	$ar.init(function(){
		ko.applyBindings(self,$ar.dom('#login')[0]);
		ko.applyBindings(self,$ar.dom('#default')[0]);

		if(!$ar.api.config.token) return;

		self.loading(true);
		$ar.api.user(null,function(resp){
			if(resp && !resp.error)
				$ar.pub('loggedin',resp);
			//flash suppresor
			setTimeout(function(){ self.loading(false); },200);
		});
	});
})();
