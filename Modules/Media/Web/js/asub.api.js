var Asub = {
	server: ko.observable()
};

Asub.API = {
	baseArgs: function(){
		var ajaxArgs = {
			type: 'GET',
			dataType: 'jsonp', 
			crossDomain: true, 
			data: {
				f: 'jsonp',
				c: 'asubnonymous',
				u: Asub.Login.username(),
				p: Asub.Login.password(),
				v: '1.8.0'
			},
			error: function(jqXHR,serverMsg,e){
				toastr.error(serverMsg,'Error');
			}
		};
		
		return ajaxArgs;
	},
	ajaxCall: function(ajaxArgs){
		$.ajax(ajaxArgs);
	},
	/**
	 * Asub.API.serialize simple recursive function to take a list of args and serialize them into a URL encoded list format
	 * 
 	 * @param {Object} obj to serialize
 	 * @param {Object} prefix since this can handle multidemensional arrays this is required
	 */
	serialize: function(obj, prefix) {
	    var str = [];
	    for(var p in obj) {
	        var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
	        str.push(typeof v == "object" ? 
	            Asub.API.serialize(v, k) :
	            encodeURIComponent(k) + "=" + encodeURIComponent(v));
	    }
	    return str.join("&");
	},	
	getRootFolders: function(id,callback){
		var ajaxArgs = Asub.API.baseArgs();
		ajaxArgs.url = Asub.server() + "/api/Media/Repo/" + id + "/Folders";
		ajaxArgs.success = function(r){
        	if(r['status'] && 'ok' == r['status']) return callback(r['folders']);
        	else Asub.error('Failed to get folders');
	    };
		Asub.API.ajaxCall(ajaxArgs);

	},
	getRepos: function(id,callback){
		var ajaxArgs = Asub.API.baseArgs();
		ajaxArgs.url = Asub.server() + "/api/Media/Repos";
		ajaxArgs.data.musicFolderId = id;
		ajaxArgs.success = function(r){
        	if(r['status'] && 'ok' == r['status']) return callback(r['repos']);
        	else Asub.error('Failed to get repos');
    	};
		Asub.API.ajaxCall(ajaxArgs);		
		
	},
	getMusicDirectory: function(id,callback){
		var ajaxArgs = Asub.API.baseArgs();
		ajaxArgs.url = Asub.server() + "/rest/getMusicDirectory.view";
		ajaxArgs.data.id = id;
		ajaxArgs.success = function(r){
        	if(r['subsonic-response']) return callback(r['subsonic-response']);
        	else Asub.error('Failed to get Folder Contents');
    	};
		Asub.API.ajaxCall(ajaxArgs);		

	},	
	ping: function(callback){
		var ajaxArgs = Asub.API.baseArgs();
		ajaxArgs.url = Asub.server() + "/rest/ping.view";
		ajaxArgs.success = function(r){
        	if(r['subsonic-response']) return callback(r['subsonic-response']);
        	else return callback(false);
    	};
		Asub.API.ajaxCall(ajaxArgs);		

	},
	download: function(args){
		var data = $.extend(Asub.API.baseArgs().data, args);

		var Content = Asub.server() + '/rest/download.view?'+Asub.API.serialize(data);
		return Content;
	},	
	stream: function(args){
		var data = $.extend(Asub.API.baseArgs().data, args);

		var Content = Asub.server() + '/rest/stream.view?'+Asub.API.serialize(data);
		return Content;
	},
	hls: function(args){
		var data = $.extend(Asub.API.baseArgs().data, args);

		var Content = Asub.server() + '/rest/hls.m3u8?'+Asub.API.serialize(data);
		return Content;
	},
	getCoverArt: function(args){
		var data = Asub.API.baseArgs().data;
		if(args.coverArt){
			data.id = args.coverArt;
		}else if(args.id){
			data.id = args.id;	
		}
		
		if(args.size){
			data.size = args.size;
		}
		return Asub.server() + "/rest/getCoverArt.view?" + Asub.API.serialize(data);	
	},
	getAlbumList: function(args,callback){
		var ajaxArgs = Asub.API.baseArgs();
		ajaxArgs.url = Asub.server() + "/rest/getAlbumList.view";
		ajaxArgs.success = function(r){
        	if(r['subsonic-response']) return callback(r['subsonic-response']);
        	else Asub.error('Failed to get Folder Contents');
    	};
		if(args.listType) 	ajaxArgs.data.type = args.listType;
		if(args.size)		ajaxArgs.data.size = args.size;
		if(args.offset) 	ajaxArgs.data.offset = args.offset;
		Asub.API.ajaxCall(ajaxArgs);
	},
	search: function(args,callback){
		var ajaxArgs = Asub.API.baseArgs();
		ajaxArgs.url = Asub.server() + "/rest/search.view";
		if(args.artist) 		ajaxArgs.data.artist = args.artist;
		if(args.album) 			ajaxArgs.data.artistOffset = args.artistOffset;
		if(args.albumOffset) 	ajaxArgs.data.album = args.album;
		if(args.title) 			ajaxArgs.data.title = args.title;
		if(args.any) 			ajaxArgs.data.any = args.any;
		if(args.count) 			ajaxArgs.data.count = args.count;
		if(args.offset) 		ajaxArgs.data.offset = args.offset;
		if(args.newerThan) 		ajaxArgs.data.newerThan = args.newerThan;

		ajaxArgs.success = function(r){
        	if(r['subsonic-response']) return callback(r['subsonic-response']);
        	else Asub.error('Failed to search');
    	};
    	Asub.API.ajaxCall(ajaxArgs);
	},	
	search2: function(args,callback){
		var ajaxArgs = Asub.API.baseArgs();
		ajaxArgs.url = Asub.server() + "/rest/search2.view";
		if(args.query) 			ajaxArgs.data.query = args.query;
		if(args.artistOffset) 	ajaxArgs.data.artistOffset = args.artistOffset;
		if(args.albumOffset) 	ajaxArgs.data.albumOffset = args.albumOffset;
		if(args.songOffset) 	ajaxArgs.data.songOffset = args.songOffset;
		//Count
		if(args.artistCount) 	ajaxArgs.data.artistCount = args.artistCount;
		if(args.albumCount) 	ajaxArgs.data.albumCount = args.albumCount;
		if(args.songCount) 		ajaxArgs.data.songCount = args.songCount;
		ajaxArgs.success = function(r){
        	if(r['subsonic-response']) return callback(r['subsonic-response']);
        	else Asub.error('Failed to search');
    	};		
		Asub.API.ajaxCall(ajaxArgs);
	},	

	getBreadCrumb: function(id){
		var ajaxArgs = Asub.API.baseArgs();
		ajaxArgs.url = Asub.server() + "/rest/getMusicDirectory.view";
		ajaxArgs.data.id = id;
		ajaxArgs.success = function(r){
        	if(r['subsonic-response'] && 'ok' == r['subsonic-response']['status'] ){
        		var d = r['subsonic-response']['directory'];
				Asub.Navigate.breadCrumb.unshift({id:d.id,name:d.name});
				//if I've got a parent go recursive
				if(d.parent){
					Asub.API.getBreadCrumb(d.parent);
				}
        	}
        	else{
	        	Asub.error('Error building Breadcrums');
        	}
    	};
    	Asub.API.ajaxCall(ajaxArgs);
	},
	getMedia: function( id, callback ){
		var ajaxArgs = Asub.API.baseArgs();
		ajaxArgs.url = Asub.server() + "/rest/getSong.view";
		ajaxArgs.data.id = id;
		ajaxArgs.success = function(r){
        	if(r['subsonic-response']) return callback(r['subsonic-response']);
        	else Asub.error('Failed to send message');
    	};
    	Asub.API.ajaxCall(ajaxArgs);		
	}
};
