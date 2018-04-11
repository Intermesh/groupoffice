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
			me.displayName = data.clientSettings.displayName;
			me.id = data.clientSettings.user_id;
			
			Ext.apply(GO.settings, data.clientSettings);
			cb(data, response);
		});
		
	},
	isLoggedIn: function() {
		return !Ext.isEmpty(this.username);
	}
};
