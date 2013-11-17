var ContractModel = function(data){
	var self = $ar.model({
		id: 0,
		version: 0,
		title: '',
		text: '',
		status: 'active',
		single_user: 0
	}).validate({
		title: function(val){
			if(!$ar.type(val,'string') || !val.length)
				return ['invalid title'];
		},
		text: function(val){
			if(!$ar.type(val,'string') || !val.length)
				return ['text required'];
		}
	}).sync({
		api: $ar.api.save_contract,
		channel: 'contract/update',
		key: 'id'
	}).serialize(data);

	$ar.sub('contract/update',function(c){
		var c_id = ko.utils.unwrapObservable(c.id),
			s_id = ko.utils.unwrapObservable(self.id);
		if(c_id === 0 || c_id != s_id)
			return;
		self.serialize(c.serialize());
	});

	return self;
};
var UserModel = function(data){
	var self = $ar.model({
		id: 0,
		name: 'guest',
		company: 0,
		email: '',
		password: ''
	}).post(function(obj){
		if(!obj.password)
			delete obj.password;
	}).sync({
		api: $ar.api.save_user,
		channel: 'user/update',
		key: 'id'
	}).serialize(data).watch();

	self.companies = (function(){
		var _com;
		return function(){
			if(arguments.length){
				_com = arguments[0];
				return;
			}
			if(_com)
				return _com;
			$ar.api.u_companies(null,function(resp){
				_com = resp;
			});
		};
	})();
	return self;
};
var PagedModel = function(data){
	var self = $ar.model({
		api: null,

		items: [],

		page: 0,
		count: 25,
		loading: false,
		total: 0,

		update: null
	}).watch({
		loading: true,
		items: true,
		page: true,
		total: true
	}).serialize(data);

	self.more = function(){
		self.page(self.page()+1);
	};
	self.left = ko.computed(function(){
		var num = self.total()-self.items().length;
		return num>0?num:0;
	});

	self.page.subscribe(function(){
		self.grab();
	});

	self.generateParams = function(){
		return {
			page: self.page(),
			count: self.count
		};
	};
	self.grab = function(){
		self.loading(true);
		self.api(self.generateParams(), function(resp){
			self.loading(false);
			self.serialize({ items: self.items().concat(resp.items) });
			if(!$ar.type(self.update,'function')) return;
			self.update(self);
		});
	};

	return self;
};