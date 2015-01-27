/* {"requires":["web/js/knockout.js","web/js/drdelambre/src/dd.js", "web/js/drdelambre/src/modules/model.js"]} */
var Capability = function(data){
        var self = $dd.model({
                id: '',
                capability: ''
        });

        self.fill(data);
        return self;
};