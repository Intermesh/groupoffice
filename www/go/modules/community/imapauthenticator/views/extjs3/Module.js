Ext.ns('go.modules.community.imapauthenticator');

go.Modules.register("community", 'imapauthenticator', {	
	entities: ["ImapAuthServer"]	
});

GO.mainLayout.on("authenticated", function() {
	go.Db.store("ImapAuthServer").on('changes', function (store, added, changed, destroyed) {

		GO.SystemSettingsDomainCombo.reloadDomains();

	});
});