go.User = {
	accessToken: localStorage.getItem('accessToken') || sessionStorage.getItem('accessToken'),
	authenticate: function(cb) {
		if(!this.accessToken) {
			return;
		}
		go.Jmap.get(function(data, response){
			if(data) {
				this.loadSession(data);
			}
			cb(data, response);
		}, this);		
	},
  
  loadSession : function(session) {
    this.username = session.username;
    this.apiUrl = session.apiUrl;
    this.downloadUrl = session.downloadUrl;
    this.uploadUrl = session.uploadUrl;
    this.displayName = session.user.displayName;
    this.id = session.user.id;
    this.avatarId = session.user.avatarId;

    Ext.apply(GO.settings, session.oldSettings);
  },
  
	isLoggedIn: function() {
		return !Ext.isEmpty(this.username);
	}
};
