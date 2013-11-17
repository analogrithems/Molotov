(function(){
	var self = $ar.model({
		groups: []
	}).type({
		groups: $ar.group
	}).watch();


	
	self.show = ko.observable(false);
	self.loading = ko.observable(false);
	self.error = ko.observable('');
	
    $ar.api.route('myGroups','/api/Auth/myGroups');
    $ar.api.route('addGroup','/api/Auth/Group');
	
	$ar.sub('loggedout',function(resp){
		self.show(false);
	});
	
	$ar.route('#groups',function(){
		console.log("Showing Groups");
		self.show(true);
	},function(){
		self.show(false);
	});

	$ar.init(function(){
		console.log("Init Groups");
                Auth.Dashboard.addWidget({title: "Groups", link: "#groups", icon: "users"});
		ko.applyBindings(self,$ar.dom('#groupManager')[0]);
	});
	
	Auth.Groups = self;
})();
