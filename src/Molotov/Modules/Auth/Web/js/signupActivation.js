/* {"requires":["web/js/knockout.js","web/js/drdelambre/src/dd.js", "web/js/drdelambre/src/modules/api.js", "web/js/drdelambre/src/modules/model.js", "web/js/drdelambre/src/modules/route.js", "web/js/drdelambre/src/modules/dom.js"]} */
(function(){
	var self = $dd.model({
		email: '',
		activation_key: ''
	}).validate({
		email: function(val){
			if(!$dd.type(val,'string') || !val.length)
				return ['email required'];
		},
		activation_key: function(val){
			if(!$dd.type(val,'string') || !val.length)
				return ['key required'];
		}
	});

	self.show = ko.observable(false);
	self.loading = ko.observable(false);
	self.error = ko.observable('');
	
	$dd.api.route('SignupActivation','/Molotov/api/Auth/ActivateUser');
	
	$dd.route('#SignupActivation/:key/:email',function(key,email){
		self.email = email;
		self.activation_key = key;
		
		self.loading(true);
		self.SignupActivation();
	},function(key,email){
		self.show(false);
	});
	
	self.SignupActivation = function(){
		if(!self.validate()){
			self.error('errors: ' + self.errors.join(', '));
			return;
		}
		self.loading(true);

		$dd.api.SignupActivation(self.out(),function(resp){
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

	$dd.init(function(){
		ko.applyBindings(self,$dd.dom('#SignupActivation')[0]);
	});
})();
