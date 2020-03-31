go.links.EditLinkDialog = Ext.extend(go.form.Dialog, {
	stateId: "go-edit-link-windows",
	title: t("Edit link", "links"),
	entityStore: "Link",
	maximizable: false,
	collapsible: false,
	modal: true,
	width: 800,
	autoHeight: true,
	defaults: {
		labelWidth: dp(140)
	},

	initFormItems: function () {
		return [
			{
			xtype: 'fieldset',
			items: [{
					xtype: 'textfield',
					name: 'description',
					fieldLabel: t("Description"),
					maxLength: 190,
					anchor: '100%',
					allowBlank: true
				}]
		}]
	},
	onLoad: function(entityValues) {
		this.supr().onLoad.call(this,entityValues);
	}
});