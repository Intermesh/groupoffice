go.User = new (Ext.extend(Ext.util.Observable, {
	loaded : false,
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
		this.accessToken = accessToken;
		
		if(!Ext.Ajax.defaultHeaders) {
			Ext.Ajax.defaultHeaders = {};
		}
		
		Ext.Ajax.defaultHeaders.Authorization = 'Bearer ' + accessToken;
		
	},
  
  loadSession : function(session) {
    this.apiUrl = session.apiUrl;
    this.downloadUrl = session.downloadUrl;
    this.uploadUrl = session.uploadUrl;
		this.eventSourceUrl = session.eventSourceUrl;		
		this.loaded = true;
		this.apiVersion = session.version;

		Ext.apply(this, session.user);
		this.firstWeekDay = parseInt(session.user.firstWeekday);
		
    Ext.apply(GO.settings, session.oldSettings);
		
		this.fireEvent("load", this);
  },
  
	isLoggedIn: function() {
		return !Ext.isEmpty(this.username);
	}
}));
