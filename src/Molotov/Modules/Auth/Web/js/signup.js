/* {"requires":["web/js/knockout.js","web/js/drdelambre/src/dd.js", "web/js/drdelambre/src/modules/api.js", "web/js/drdelambre/src/modules/model.js", "web/js/drdelambre/src/modules/route.js", "web/js/drdelambre/src/modules/dom.js"]} */
(function(){
	var self = $dd.model({
		display_name: '',
		email: '',
		password: ''
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
	self.complete = ko.observable(false);
	self.loading = ko.observable(false);
	self.error = ko.observable('');
	
	$dd.api.route('signup','/Molotov/api/Auth/AddUser');
	
	$dd.route('#signup',function(){
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

		$dd.api.signup(self.out(),function(resp){
			self.password = '';;
			if( 'error' == resp.status ){
				self.loading(false);
				self.error(resp.msg);
				return;
			}else{
				self.error('');
				self.complete(true);
			}

			self.loading(false);
		});
	};

	$dd.init(function(){
		//if('' == window.location.hash) window.location.hash = 'signup';
		ko.applyBindings(self,$dd.dom('#signup')[0]);
	});
})();
