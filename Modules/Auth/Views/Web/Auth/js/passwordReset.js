(function(){
	var self = $ar.model({
		email: '',
		key: '',
		password: '',
		password_confirmation: ''
	}).validate({
		email: function(val){
			if(!$ar.type(val,'string') || !val.length)
				return ['email required'];
		},
		key: function(val){
			if(!$ar.type(val,'string') || !val.length)
				return ['key required'];
		},
		password: function(val){
			if(val != self.password_confirmation()) return ['Passwords do not match'];
		}
	}).watch();

	self.show = ko.observable(false);
	self.loading = ko.observable(false);
	self.resp = ko.observable('');
	self.error = ko.observable('');
		
	$ar.api.route('PasswordReset','/api/Auth/PasswordReset');
		
	$ar.route('#passwordReset/:key/:email',function(key,email){
		self.email(email);
		self.activation_key(key);
		
		self.show(true);
	},function(key,email){
		self.show(false);
	});
	
	self.PasswordReset = function(){
		if(!self.validate()){
			self.error('errors: ' + self.errors.join(', '));
			return;
		}
		self.loading(true);

		$ar.api.PasswordReset({email: self.email(), key: self.key(), password: self.password()},function(resp){
			if( 'error' == resp.status ){
				self.loading(false);
				self.error(resp.msg);
				self.resp('');
				return;
			}else{
				self.resp(resp.msg);
				self.error('');
			}
			self.loading(false);
		});
	};

	$ar.init(function(){
		ko.applyBindings(self,$ar.dom('#passwordReset')[0]);
	});
})();
