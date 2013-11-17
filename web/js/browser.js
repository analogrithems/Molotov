(function(){
	var self = {
		show: ko.observable(false)
	};
	$ar.sub('loggedin',function(resp){
		if(!resp.id)
			return;
		self.show(true);
	});
	$ar.sub('loggedout',function(){ self.show(false); });

	$ar.init(function(){
		ko.applyBindings(self,$ar.dom('#browser')[0]);
	});
})();
