go.User = new (Ext.extend(Ext.util.Observable, {
	loaded : false,
	accessToken: localStorage.getItem('accessToken') || sessionStorage.getItem('accessToken'),
	authenticate: function(cb, scope) {
		if(!this.accessToken) {
			return;
		}
		go.Jmap.get(function(data, options, success, response){
			if(data) {
				document.cookie = "accessToken=" + this.accessToken;
				this.loadSession(data);
			}
			cb.call(scope, data, options, success, response);
		}, this);		
	},
	
	clearAccessToken : function() {
		this.accessToken = null;
		localStorage.removeItem('accessToken');
		sessionStorage.removeItem('accessToken');
	},
  
  loadSession : function(session) {
    this.apiUrl = session.apiUrl;
    this.downloadUrl = session.downloadUrl;
    this.uploadUrl = session.uploadUrl;
		this.eventSourceUrl = session.eventSourceUrl;		
		this.loaded = true;

		Ext.apply(this, session.user);
		this.firstWeekDay = parseInt(session.user.firstWeekday);
		
    Ext.apply(GO.settings, session.oldSettings);
		
		this.fireEvent("load", this);
  },
  
	isLoggedIn: function() {
		return !Ext.isEmpty(this.username);
	}
}));
