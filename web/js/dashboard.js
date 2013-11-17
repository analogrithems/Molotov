$ar.api.route('activity','/activity',function(){
	return false;
});

(function(){
	var self = {
		show: ko.observable(false),
		events: ko.observableArray([]),

		page: 0,
		count: 25,
		pause: true,
		loading: false
	};

	$ar.init(function(){
		var elem = $ar.dom('#dashboard');
		ko.applyBindings(self,elem[0]);
		elem.on('scroll',function(e){
		});
	});

	$ar.sub('loggedin',function(){ window.location.href = '#/home/'; });
	$ar.route('home/',function(){
		self.show(true);
	}, function(){
		self.show(false);
	});
})();
