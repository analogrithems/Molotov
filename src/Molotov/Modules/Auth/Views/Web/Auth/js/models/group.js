$ar.models.register('group',function(data){
        var self = $ar.model({
                id: '',
                name: '',
                users: [],
                role: {}
        }).type({
	        role: $ar.role,
	        user: $ar.user
        });
        
        self.loadUsers = function(){
                var url = '/api/Auth/Group/' + self.id() + '/Members';
                        handle = 'groupMembers_' + self.id();
                $ar.api.route(handle,url);
                $ar.api[handle]({},function(resp){
                        if( 'ok' == resp.status ){
                                self.users(resp.members);		
                        }
                });
        };
        
        self.inviteUser = function( email ){
                var url = '/api/Auth/Group/' + self.id() + '/InviteMember';
                        handle = 'inviteGroupMembers_' + self.id();
                $ar.api.route(handle,url);
                $ar.api[handle]({},function(resp){
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
        
        self.serialize(data);
        return self;
});