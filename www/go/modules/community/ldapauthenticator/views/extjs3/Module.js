Ext.ns('go.modules.community.ldapauthenticator');

go.Modules.register("community", 'ldapauthenticator', {	
	entities: ["LdapAuthServer"]	
});

GO.mainLayout.on("authenticated", function() {
	go.Db.store("LdapAuthServer").on('changes', function (store, added, changed, destroyed) {

		GO.SystemSettingsDomainCombo.reloadDomains();

	});
});