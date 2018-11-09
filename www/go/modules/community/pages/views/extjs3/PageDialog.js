go.modules.community.pages.PageDialog = Ext.extend(go.form.Dialog, {
	title: t("Page"),
	entityStore: go.Stores.get("Page"),
	width: 600,
	height: 600,
	//maximized: true,
	maximizable: true,
	siteId: '_',
	sortOrder: 0,
	initFormItems: function () {
		var items = [{
				xtype: 'fieldset',
				autoHeight: true,
				items: [
					{
						xtype: 'textfield',
						name: 'pageName',
						fieldLabel: t("Page name"),
						anchor: '100%',
						allowBlank: false
					},
					{
						xtype: 'xhtmleditor',
						name: 'content',
						fieldLabel: "",
						hideLabel: true,
						anchor: '100%',
						allowBlank: true
					},
					{
						xtype: 'hidden',
						name: 'siteId',
						value: this.siteId
					},{
						xtype: 'hidden',
						name: 'sortOrder',
						value: this.sortOrder
					}]
			}
		]
		return items;
	},
	
	submit: function(){
	    //check if form has dirty fields.
	    go.modules.community.pages.PageDialog.superclass.submit.call(this);
	}
});
