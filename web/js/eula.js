(function(){
	var ContractEditView = function(data){
		var self = ContractModel(data).pre_save(function(){
			self.active(false);
			if(!self.validate()){
				//throw some errors
				return false;
			}
			return true;
		}).post_save(function(){
			self._cache = self.serialize();
		}).watch();

		self._cache = self.serialize();
		self.active = ko.observable(false);

		self.active.subscribe(function(nval){
			if(!!!nval) return;
			self._cache = self.serialize();
		});
		self.activator = function(){
			self.status(self.status()=='active'?'inactive':'active');
			self.save();
		};
		self.publishor = function(){
			self.loading(true);
			self.active(false);
			$ar.api.publish_contract(self.serialize(),function(resp){
				self.serialize(resp);
				self._cache = self.serialize();
				$ar.pub('contract/update',self);
				self.loading(false);
			});
		};
		self.cancel = function(){
			self.active(false);
			self.serialize(self._cache);
		};

		return self;
	};

	var UserContractPagedModel = function(data){
		data.api = $ar.api.contracts;
		var self = PagedModel();
		self.def['items']['type'] = ContractEditView;
		self.serialize(data);
		self.generateParams = function(){
			return {
				page: self.page(),
				count: self.count,
				single_user: 1,
				status: data.active?'active':'inactive'
			};
		};
		return self;
	};

	var EulaView = function(){
		var self = {
			show: ko.observable(false),
			editing: null,

			contracts: UserContractPagedModel({ active: true }),
			dead_contracts: UserContractPagedModel({ active: false })
		};

		self.edit = function(which){
			if(self.editing){
				if(which == self.editing && which.active()){
					which.active(false);
					return;
				}
				if(which != self.editing){
					self.editing.active(false);
				}
			}
			which.active(true);
			self.editing = which;
		};
		self.add = function(){
			var to_add = ContractEditView({ single_user: true }),
				sub;
			self.contracts.items.unshift(to_add);
			self.edit(to_add);
			sub = to_add.active.subscribe(function(){
				if(!to_add.title() && !to_add.text()){
					self.contracts.items.remove(to_add);
				}
				sub.dispose();
			});
		};

		$ar.sub('loggedin',function(resp){
			if(!resp.id) return;
			self.contracts.grab();
			self.dead_contracts.grab();
		});
		$ar.sub('contract/update',function(obj){
			console.log('oh beans');
			var con = self.contracts.items(),
				dcon = self.dead_contracts.items(),
				ni, no;
			if(obj.status() == 'active'){
				for(ni = 0; ni < con.length; ni++){
					if(obj.id() == con[ni].id())
						return;
				}
				for(ni = 0; ni < dcon.length; ni++){
					if(obj.id() != dcon[ni].id())
						continue;
					self.contracts.items.unshift(dcon[ni]);
					self.dead_contracts.items.remove(dcon[ni]);
				}
				return;
			}
			for(ni = 0; ni < dcon.length; ni++){
				if(obj.id() == dcon[ni].id())
					return;
			}
			for(ni = 0; ni < con.length; ni++){
				if(obj.id() != con[ni].id())
					continue;
				self.dead_contracts.items.unshift(con[ni]);
				self.contracts.items.remove(con[ni]);
			}
		});
		$ar.init(function(){
			ko.applyBindings(self,$ar.dom('#eula')[0]);
		});
		$ar.route('eula/',function(){
			self.show(true);
		}, function(){
			self.show(false);
		});
	};

	EulaView();
})();