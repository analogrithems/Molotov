(function(){
	var self = $ar.model({
		email: '',
	}).validate({
		email: function(val){
			if(!$ar.type(val,'string') || !val.length)
				return ['email required'];
		}
	}).watch();

	self.show = ko.observable(false);
	self.loading = ko.observable(false);
	self.error = ko.observable('');
	self.resp = ko.observable('');
	
	$ar.api.route('PasswordResetRequest','/api/Auth/PasswordResetRequest');

	$ar.route('#passwordResetRequest',function(){
		console.log("Password Reset....");
		self.show(true);
	},function(){
		self.show(false);
	});
	
	self.PasswordResetRequest = function(){
		if(!self.validate()){
			self.error('errors: ' + self.errors.join(', '));
			return;
		}
		self.loading(true);

		$ar.api.PasswordResetRequest(self.serialize(),function(resp){
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
		ko.applyBindings(self,$ar.dom('#passwordResetRequest')[0]);
	});
})();
