$ar.models.register('group',function(data){
	var self = $ar.model({
		id:'',
		email: '',
		display_name: '',
		password:'',
		enabled:'',
		created:'',
		groups:[]
	}).type({
		groups: $ar.group
	}).validate({
		email: function(val){
			if(!$ar.type(val,'string') || !val.length)
				return ['email required'];
		},
		display_name: function(val){
			if(!$ar.type(val,'string') || !val.length)
				return ['display name required'];
		},
	}).watch(['email','display_name']);
	
    self.serialize(data);
    return self;	
};