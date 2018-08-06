go.modules.community.addressbook.MainPanel = Ext.extend(Ext.Panel, {
	
	layout: "responsive",
	
	addAddressBookId : 1,

	initComponent: function () {

		this.grid = new go.modules.community.addressbook.ContactGrid({
			region: 'center',
			tbar: [
				{
					cls: 'go-narrow',
					iconCls: "ic-menu",
					handler: function () {
//						this.westPanel.getLayout().setActiveItem(this.noteBookGrid);
//						this.noteBookGrid.show();
					},
					scope: this
				},
				'->',
				{
					xtype: 'tbsearch'
				},
				this.addButton = new Ext.Button({
					//disabled: true,
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (btn) {
						var dlg = new go.modules.community.addressbook.ContactDialog({
							formValues: {
								addressBookId: this.addAddressBookId
							}
						});
						dlg.show();
					},
					scope: this
				})

			],
			listeners: {
				viewready: function (grid) {
					//load note books and select the first
					this.grid.store.load();
				},

				rowdblclick: function (grid, rowIndex, e) {

					var record = grid.getStore().getAt(rowIndex);
					if (record.get('permissionLevel') < GO.permissionLevels.write) {
						return;
					}

					var dlg = new go.modules.community.addressbook.ContactDialog();
					dlg.load(record.id).show();
				},

				scope: this
			}
		});
		
		this.items = [this.grid];

		go.modules.community.addressbook.MainPanel.superclass.initComponent.call(this);
	}
});
