$ar.models.register('role',function(data){
        var self = $ar.model({
                id: '',
                name: '',
                capabilities: []
        }).type({
	        capabilities: $ar.capability
        });

        self.serialize(data);
        return self;
});