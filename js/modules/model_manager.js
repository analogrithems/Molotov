(function(){
	function registry(heading){
		var repo = {},
			self = {};
		self.register = function(name,constructor){
			repo[name] = constructor;
		};
		self.create = function(name,data){
			if(!repo.hasOwnProperty(name))
				throw new Error('$ar.' + heading + '.create: ' + name + ' not declared');
			return new repo[name](data);
		};
		self.reference = function(name){
			if(!repo.hasOwnProperty(name))
				throw new Error('$ar.' + heading + '.reference: ' + name + ' not declared');
			return repo[name];
		};

		return self;
	}
	$ar.mixin({
		models: registry('models'),
		views: registry('views')
	});
})();