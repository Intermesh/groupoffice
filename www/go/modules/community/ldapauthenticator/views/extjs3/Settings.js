
Ext.onReady(function() {
	Ext.override(go.systemsettings.AuthenticationPanel, {

		initComponent : go.systemsettings.AuthenticationPanel.prototype.initComponent.createSequence(function() {
			this.add(new go.modules.community.ldapauthenticator.ServerGrid())
		})
	});
});
