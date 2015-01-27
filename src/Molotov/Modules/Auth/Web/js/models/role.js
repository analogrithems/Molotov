/* {"requires":["web/js/knockout.js","web/js/drdelambre/src/dd.js", "web/js/drdelambre/src/modules/model.js"]} */
var Role = function(data){
        var self = $dd.model({
                id: '',
                name: '',
                capabilities: []
        }).type({
	        capabilities: Capability
        });

        self.serialize(data);
        return self;
});