/* {"requires":["web/js/knockout.js","web/js/drdelambre/src/dd.js", "web/js/drdelambre/src/modules/model.js"]} */
var Group = function(data){
        var self = $dd.model({
                id: '',
                name: '',
                users: [],
                role: {}
        }).type({
	        role: Role,
	        user: User
        });
        
        self.loadUsers = function(){
                var url = '/Molotov/api/Auth/Group/' + self.id() + '/Members';
                        handle = 'groupMembers_' + self.id();
                $dd.api.route(handle,url);
                $dd.api[handle]({},function(resp){
                        if( 'ok' == resp.status ){
                                self.users(resp.members);		
                        }
                });
        };
        
        self.inviteUser = function( email ){
                var url = '/Molotov/api/Auth/Group/' + self.id() + '/InviteMember';
                        handle = 'inviteGroupMembers_' + self.id();
                $dd.api.route(handle,url);
                $dd.api[handle]({},function(resp){
                        if( 'ok' == resp.status ){
                                console.log(resp);
                        }
                });
        };
        
        self.hasCapability = function( cap ){
            if( self.role.capabilities &&  self.role.capabilities.length > 0 ){
                for (var i = 0; i < self.role.capabilities.length; i++ ) {
                    if ( cap == self.role.capabilities[i].name ) {
                        return true;
                    }
                }
            }
            return false;
        };
        
        self.fill(data);
        return self;
};