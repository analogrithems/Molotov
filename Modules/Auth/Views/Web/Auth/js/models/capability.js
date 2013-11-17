$ar.models.register('capability',function(data){
        var self = $ar.model({
                id: '',
                capability: ''
        });

        self.serialize(data);
        return self;
});