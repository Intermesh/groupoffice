go.emailfavorites.FavoritefolderDialog = Ext.extend(go.form.Dialog, {
	stateId: "go-emailfavorites-favoritefolder-dialog",
	title: t("E-mail favorite"),
	entityStore: "Favoritefolder",
	width: dp(265),
	height: dp(200),
	resizable: true,
	maximizable: false,
	collapsible: false,
	modal: true,
	initFormItems: function () {
		return [{
			xtype: "fieldset",
			items: [{
				xtype: 'textfield',
				name: 'name',
				fieldLabel: t("Name"),
				allowBlank: false
			}]
		}]
	}
});