(function(){
	var self = {
		show: ko.observable(false),
		editing: ko.observable(false),
		user: UserModel(),
		selectedCompany: ko.observable(null)
	};

	$ar.route('account/',function(){
		self.show(true);
	},function(){
		self.show(false);
	});
	$ar.sub('loggedin',function(resp){
		self.user.serialize(resp);
	});
	$ar.sub('loggedout',function(){
		self.user.serialize(null);
	});
	$ar.init(function(){
		ko.applyBindings(self,$ar.dom('#account')[0]);
	});
})();