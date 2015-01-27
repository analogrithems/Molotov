/* {"requires":["web/js/knockout.js","web/js/drdelambre/src/dd.js", "web/js/drdelambre/src/modules/api.js", "web/js/drdelambre/src/modules/model.js", "web/js/drdelambre/src/modules/route.js", "web/js/drdelambre/src/modules/dom.js"]} */
(function(){
	var self = $dd.model({
		email: '',
	}).validate({
		email: function(val){
			if(!$dd.type(val,'string') || !val.length)
				return ['email required'];
		}
	});

	self.show = ko.observable(false);
	self.loading = ko.observable(false);
	self.error = ko.observable('');
	self.resp = ko.observable('');
	
	$dd.api.route('PasswordResetRequest','/Molotov/api/Auth/PasswordResetRequest');

	$dd.route('#passwordResetRequest',function(){
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

		$dd.api.PasswordResetRequest(self.out(),function(resp){
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
		ko.applyBindings(self,$dd.dom('#passwordResetRequest')[0]);
	});
})();
