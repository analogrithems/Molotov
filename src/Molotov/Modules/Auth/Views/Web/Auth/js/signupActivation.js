(function(){
	var self = $ar.model({
		email: '',
		activation_key: ''
	}).validate({
		email: function(val){
			if(!$ar.type(val,'string') || !val.length)
				return ['email required'];
		},
		activation_key: function(val){
			if(!$ar.type(val,'string') || !val.length)
				return ['key required'];
		}
	}).watch();

	self.show = ko.observable(false);
	self.loading = ko.observable(false);
	self.error = ko.observable('');
	
	$ar.api.route('SignupActivation','/api/Auth/ActivateUser');
	
	$ar.route('#SignupActivation',function(key,email){
		self.email(email);
		self.activation_key(key);
		
		self.show(true);
	},function(key,email){
		self.show(false);
	});
	
	self.SignupActivation = function(){
		if(!self.validate()){
			self.error('errors: ' + self.errors.join(', '));
			return;
		}
		self.loading(true);

		$ar.api.SignupActivation(self.serialize(),function(resp){
			if( 'error' == resp.status ){
				self.loading(false);
				self.error(resp.msg);
				return;
			}else{
				self.error('');
			}
			self.loading(false);
		});
	};

	$ar.init(function(){
		ko.applyBindings(self,$ar.dom('#SignupActivation')[0]);
	});
})();
