/* {"requires":["web/js/knockout.js","web/js/drdelambre/src/dd.js", "web/js/drdelambre/src/modules/api.js", "web/js/drdelambre/src/modules/model.js", "web/js/drdelambre/src/modules/route.js", "web/js/drdelambre/src/modules/dom.js"]} */
(function(){
	var self = $dd.model({
		email: '',
		key: '',
		password: '',
		password_confirmation: ''
	}).validate({
		email: function(val){
			if(!$dd.type(val,'string') || !val.length)
				return ['email required'];
		},
		key: function(val){
			if(!$dd.type(val,'string') || !val.length)
				return ['key required'];
		},
		password: function(val){
			if(val != self.password_confirmation()) return ['Passwords do not match'];
		}
	});

	self.show = ko.observable(false);
	self.loading = ko.observable(false);
	self.resp = ko.observable('');
	self.error = ko.observable('');
		
	$dd.api.route('PasswordReset','/Molotov/api/Auth/PasswordReset');
		
	$dd.route('#passwordReset/:key/:email',function(key,email){
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

		$dd.api.PasswordReset({email: self.email(), key: self.key(), password: self.password()},function(resp){
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

	$dd.init(function(){
		ko.applyBindings(self,$dd.dom('#passwordReset')[0]);
	});
})();
