go.User = {
	accessToken: go.util.Cookies.get('accessToken'),
	authenticate: function(cb, scope) {
		if(!this.accessToken) {
			return;
		}
		go.Jmap.get(function(data, options, success, response){
			if(data) {
				this.loadSession(data);
			}
			cb.call(scope, data, options, success, response);
		}, this);		
	},
	
	clearAccessToken : function() {
		this.accessToken = null;
		go.util.Cookies.unset('accessToken');
	},
	
	setAccessToken : function(accessToken, remember) {
		var expires = null;
		
		if(remember) {
			expires = new Date();
			expires.setFullYear(expires.getFullYear() + 1);
		}
		
		go.util.Cookies.set('accessToken', accessToken, expires);
		this.accessToken = accessToken;
		
		if(!Ext.Ajax.defaultHeaders) {
			Ext.Ajax.defaultHeaders = {};
		}
		
		Ext.Ajax.defaultHeaders['Authorization'] = 'Bearer ' + accessToken;
		
	},
  
  loadSession : function(session) {
    //this.username = session.username;
    this.apiUrl = session.apiUrl;
    this.downloadUrl = session.downloadUrl;
    this.uploadUrl = session.uploadUrl;
		this.eventSourceUrl = session.eventSourceUrl;
		
		Ext.apply(this, session.user);
	//    this.displayName = session.user.displayName;
	//    this.id = session.user.id;
	//    this.avatarId = session.user.avatarId;
	//		this.isAdmin = session.user.isAdmin;
	//		this.dateFormat = session.user.dateFormat;
	//		this.timeFormat = session.user.timeFormat;
			this.firstWeekDay = parseInt(session.user.firstWeekday);

    Ext.apply(GO.settings, session.oldSettings);
  },
  
	isLoggedIn: function() {
		return !Ext.isEmpty(this.username);
	}
};
