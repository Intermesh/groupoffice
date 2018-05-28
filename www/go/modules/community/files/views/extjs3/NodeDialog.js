go.modules.community.files.NodeDialog = Ext.extend(go.form.Dialog, {
	stateId: 'files-nodeDialog',
	title: t("Rename"),
	entityStore: go.Stores.get("Node"),
	width: 450,
	height: 150,
	
	initFormItems: function () {
		var items = [
			{
				xtype: 'fieldset',
				autoHeight: true,
				items: [
					this.txtParentId = new Ext.form.Hidden({
						name: 'parentId',
						disabled:true
					}),
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					}
				]
			}
		];
		return items;
	},
	
	show: function(parentId) {
		
		debugger;
		
		if(parentId) {
			this.txtParentId.setValue(parentId);
			this.txtParentId.setDisabled(false);
		} else {
			this.txtParentId.setDisabled(true);
		}

		go.modules.community.files.NodeDialog.superclass.show.call(this);
	}
});