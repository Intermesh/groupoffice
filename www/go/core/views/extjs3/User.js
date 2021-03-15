go.User = new (Ext.extend(Ext.util.Observable, {
	loaded : false,
	accessToken: go.util.Cookies.get('accessToken'),
	authenticate: function(cb, scope) {
		if(!this.accessToken) {
			return;
		}
		return this.load();
	},

	load : function() {
		return go.Jmap.get().then((data) => {
			return this.onLoad(data);
		});
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

	onLoad : function(session) {
		console.warn(session);

		go.Jmap.capabilities = session.capabilities;

		this.session = session;

    this.apiUrl = session.apiUrl;
    this.downloadUrl = session.downloadUrl;
    this.uploadUrl = session.uploadUrl;
		this.pageUrl = session.pageUrl;
		this.eventSourceUrl = session.eventSourceUrl;		
		this.loaded = true;
		this.apiVersion = session.version + "-" + session.cacheClearedAt;

		GO.settings.state = session.state;

		// Ext.apply(this, session.user);
		return go.Db.store("User").single(session.userId).then((user) => {
			Ext.apply(this, user);
			// me.firstWeekDay = parseInt(user.firstWeekday);
			this.legacySettings(user);

			go.ActivityWatcher.activity();
			go.ActivityWatcher.init(GO.settings.config.logoutWhenInactive);

			this.fireEvent("load", this);

			return this;
		});
		
	},
	
	legacySettings : function (user) {

		Ext.apply(GO.settings, {
			'user_id' : user.id
			,'avatarId' : user.avatarId
			,'has_admin_permission' : user.isAdmin
			,'username' : user.username
			,'displayName' : user.displayName
			,'email' : user.email
			,'thousands_separator' : user.thousandsSeparator
			,'decimal_separator' : user.decimalSeparator
			,'date_format' : user.dateFormat
			,'time_format' : user.timeFormat
			,'currency' : user.currency
			,'lastlogin' : user.lastLogin
			,'max_rows_list' : user.max_rows_list
			,'timezone' : user.timezone
			,'start_module' : user.start_module
			,'theme' : user.theme
			,'mute_sound' : user.mute_sound
			,'mute_reminder_sound' : user.mute_reminder_sound
			,'mute_new_mail_sound' : user.mute_new_mail_sound
			,'popup_reminders' : user.popup_reminders
			,'popup_emails' : user.popup_emails
			,'show_smilies' : user.show_smilies
			,'auto_punctuation' : user.auto_punctuation
			,'first_weekday' : user.firstWeekday
			,'sort_name' : user.sort_name
			,'list_separator' : user.listSeparator
			,'text_separatoe.r' : user.textSeparator
			,'modules' : []
		});

		/*
		 "core": {
                "id": 1,
                "name": "core",
                "package": "core",
                "version": 148,
                "sort_order": 1,
                "admin_menu": false,
                "aclId": 5,
                "enabled": true,
                "modifiedAt": null,
                "modSeq": null,
                "deletedAt": null,
                "url": "/api/modules/core/",
                "full_url": "https://office.group-office.com/modules/core/",
                "permission_level": 50,
                "read_permission": true,
                "write_permission": true
						},*/
						

	},

	loadLegacyModules : function() {
			GO.settings.modules = {};
			var modules = go.Modules.getAll();
			for(var id in modules) {
				var m = modules[id];

				if(!m.enabled) {
					continue;
				}
				
				GO.settings.modules[m.name] = m;
				// m.url = 
				GO.settings.modules[m.name].permission_level = m.permissionLevel;
				GO.settings.modules[m.name].read_permission = !!m.permissionLevel;
				GO.settings.modules[m.name].write_permission = m.permissionLevel >= go.permissionLevels.write;
			}

	},
  
	isLoggedIn: function() {
		return !Ext.isEmpty(this.username);
	}
}));

// Update go.User when it's edited
Ext.onReady(function(){
	go.Db.store("User").on("changes", function(store, added, changed, deleted){
		if(changed[go.User.id]) {
			Ext.apply(go.User, changed[go.User.id]);
		}
	});
})
