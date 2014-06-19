(function(){
	var self = $ar.model({
		display_name: '',
		email: '',
		password: ''
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
	
	$ar.api.route('signup','/api/Auth/AddUser');
	
	$ar.route('#signup',function(){
		self.show(true);
	},function(){
		self.show(false);
	});
	
	self.signup = function(){
		if(!self.validate()){
			self.error('errors: ' + self.errors.join(', '));
			return;
		}
		self.loading(true);

		$ar.api.signup(self.serialize(),function(resp){
			self.password('');
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
		ko.applyBindings(self,$ar.dom('#signup')[0]);
	});
})();
