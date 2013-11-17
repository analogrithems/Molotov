(function(){
	var self = $ar.model({
		widgets: []	
	}).watch();
	
	//view model state
	self.show = ko.observable(false);
	self.loading = ko.observable(false);
	self.error = ko.observable('');	

	$ar.models.register('widget',function(data){
		var self = $ar.model({
			title: '',
			link: '',
			icon: ''
		});
		
		self.serialize(data);
		return self;
	});

	self.addWidget = function(widget){
		self.widgets.push(widget);
	};
	
	$ar.sub('loggedin',function(resp){
		console.log("Catching login state");
		self.show(true);
		$('.ui.dropdown').dropdown();//why do I have to do this twice?
	});
	
	$ar.sub('loggedout',function(resp){
		self.show(false);
	});
	
	$ar.init(function(){
		ko.applyBindings(self,$ar.dom('#dashboard')[0]);
	});
	
	Auth.Dashboard = self;
})();
