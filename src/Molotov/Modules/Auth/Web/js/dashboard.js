/* {"requires":["web/js/knockout.js", "web/js/drdelambre/src/modules/api.js", "web/js/drdelambre/src/modules/model.js", "web/js/drdelambre/src/modules/route.js", "web/js/drdelambre/src/modules/dom.js", "web/js/drdelambre/src/modules/pubsub.js", "web/js/drdelambre/src/modules/ioc.js"]} */
(function(){
	var Widget = function(data){
		var self = $dd.model({
			title: '',
			link: '',
			icon: ''
		}).fill(data);
		return self;
	};
	
	var self = $dd.model({
		widgets: []	
	}).type({
		widgets: Widget
	});
	
	//view model state
	self.show = ko.observable(false);
	self.loading = ko.observable(false);
	self.error = ko.observable('');	


	self.addWidget = function(widget){
		self.widgets.push(widget);
	};
	
	$dd.sub('loggedin',function(resp){
		console.log("Catching login state");
		self.show(true);
		//$('.ui.dropdown').dropdown();//why do I have to do this twice?
	});
	
	$dd.sub('loggedout',function(resp){
		self.show(false);
	});
	
	$dd.ioc.register('dashboard',function(){
		return self;
	});
	
	$dd.init(function(){
		ko.applyBindings(self,$dd.dom('#wrapper')[0]);
	});
	
})();
