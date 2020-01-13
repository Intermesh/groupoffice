Ext.onReady(function () {
  Ext.override(go.systemsettings.AuthenticationPanel, {

    initComponent: go.systemsettings.AuthenticationPanel.prototype.initComponent.createSequence(function () {
      this.add({
				xtype: "fieldset",
				title: t("IMAP Authenticator"),
        items: [
          new go.modules.community.imapauthenticator.ServerGrid({
            border: true
          })
        ]
      })
    })
  });
});
