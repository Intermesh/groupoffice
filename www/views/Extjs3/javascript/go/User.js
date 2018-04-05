go.User = {
	accessToken: localStorage.getItem('accessToken') || sessionStorage.getItem('accessToken'),
	authenticate: function(cb) {
		if(!this.accessToken) {
			return;
		}
		var me = this;
		go.Jmap.get(function(data, response){
			
			me.username = data.username;
			me.apiUrl = data.apiUrl;
			me.downloadUrl = data.downloadUrl;
			me.uploadUrl = data.uploadUrl;
			me.displayName = data.user.displayName;
			me.id = data.user.id;
			Ext.applyIf(GO.settings, data.user);
			cb(data, response);
		});
		
	},
	isLoggedIn: function() {
		return !Ext.isEmpty(this.username);
	}
};
