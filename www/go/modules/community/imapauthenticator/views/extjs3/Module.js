Ext.ns('go.modules.community.imapauthenticator');

go.Modules.register("community", 'imapauthenticator', {	
	entities: ["ImapAuthServer"]	
});

go.Db.store("ImapAuthServer").on('changes', function(store, added, changed, destroyed) {

	GO.SystemSettingsDomainCombo.reloadDomains();

});