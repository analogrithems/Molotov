(function(){
	var id = 0;

	$ar.sub('loggedin',function(resp){
		id = resp.id || resp.uid;
		if(id)
			window.location.href = '#/home/';
	});
	$ar.sub('loggedout',function(){
		window.location.href = '#';
	});

	$ar.route('*', function(){
		if(id === 0)
			window.location.href = '#';
	});
	$ar.route('',function(){
		if(id !== 0)
			window.location.href = '#/home/';
	});
	$ar.route('logout',function(){
		if(!id) return;
		$ar.api.logout();
	});
})();
