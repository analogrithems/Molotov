/* {"requires":["web/js/knockout.js","web/js/drdelambre/src/dd.js", "web/js/drdelambre/src/modules/api.js", "web/js/drdelambre/src/modules/model.js", "web/js/drdelambre/src/modules/route.js", "web/js/drdelambre/src/modules/dom.js", "web/js/drdelambre/src/modules/ioc.js"]} */
(function(){
	var self = $dd.model({
		groups: []
	}).type({
		groups: $dd.group
	});


	
	self.show = ko.observable(false);
	self.loading = ko.observable(false);
	self.error = ko.observable('');
	
    $dd.api.route('myGroups','/Molotov/api/Auth/myGroups');
    $dd.api.route('addGroup','/Molotov/api/Auth/Group');
	
	$dd.sub('loggedout',function(resp){
		self.show(false);
	});
	
	$dd.route('#groups',function(){
		console.log("Showing Groups");
		self.show(true);
	},function(){
		self.show(false);
	});

	$dd.ioc.register('group',function(){
		return self;
	});

	$dd.init(function(){
		console.log("Init Groups");
		Auth.Dashboard.addWidget({title: "Groups", link: "#groups", icon: "users"});
		ko.applyBindings(self,$dd.dom('#groupManager')[0]);
	});
	
})();
