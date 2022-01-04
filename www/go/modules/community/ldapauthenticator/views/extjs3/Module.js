Ext.ns('go.modules.community.ldapauthenticator');

go.Modules.register("community", 'ldapauthenticator', {	
	entities: ["LdapAuthServer"]	
});

go.Db.store("LdapAuthServer").on('changes', function(store, added, changed, destroyed) {

	GO.SystemSettingsDomainCombo.reloadDomains();

});