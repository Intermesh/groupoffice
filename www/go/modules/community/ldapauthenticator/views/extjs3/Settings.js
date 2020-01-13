
Ext.onReady(function() {
	Ext.override(go.systemsettings.AuthenticationPanel, {

		initComponent : go.systemsettings.AuthenticationPanel.prototype.initComponent.createSequence(function() {
			this.add({
				xtype: "fieldset",
				title: t("LDAP Authenticator"),
				items: [
					new go.modules.community.ldapauthenticator.ServerGrid({
						border: true
					})
				]
			})
		})
	});
});
