go.Modules.register("community", 'apikeys', {
	entities: [{
		name: "Key",
		relations:{
			user: {store: "Principal", fk: "userId"},
		}
	}],
	systemSettingsPanels: ["go.modules.community.apikeys.SystemSettingsPanel"],
	initModule: function () {	

	}
});



