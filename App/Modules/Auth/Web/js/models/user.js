/* {"requires":["web/js/knockout.js","web/js/drdelambre/src/dd.js", "web/js/drdelambre/src/modules/model.js"]} */
var User = function(data){
	var self = $dd.model({
		id:'',
		email: '',
		display_name: '',
		password:'',
		enabled:'',
		created:'',
		groups:[]
	}).type({
		groups: Group
	}).validate({
		email: function(val){
			if(!$dd.type(val,'string') || !val.length)
				return ['email required'];
		},
		display_name: function(val){
			if(!$dd.type(val,'string') || !val.length)
				return ['display name required'];
		},
	});
	
    self.fill(data);
    return self;	
};