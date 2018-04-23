go.modules.community.files.NodeGrid = Ext.extend(go.grid.GridPanel, {
	paging: true,
	initComponent: function () {

		this.store = new go.data.Store({
			fields: ['id', 'name', 'byteSize', {name: 'createdAt', type: 'date'}, {name: 'modifiedAt', type: 'date'}, 'permissionLevel'],
			baseParams: {filter:{parentId:0}},
			entityStore: go.Stores.get("Node")
		});

		Ext.apply(this, {
			tbar: [
				{
					cls: 'go-narrow',
					iconCls: "ic-menu",
					handler: function () {
						this.sideNav.show();
					},
					scope: this
				},
				'->',
				this.addButton = new Ext.Button({
					disabled: true,
					iconCls: 'ic-add',
					tooltip: t('Add'),
					menu: new Ext.menu.Menu({
						items: [
							{
								iconCls: 'ic-folder',
								text: t("Folder"),
								handler: this.newFolder,
								scope: this
							}
							//this.uploadItem,
							//this.jUploadItem
						]
					}),
					scope: this
				}),{
					tooltip: t("Thumbnails", "files"),
					iconCls: 'ic-view-comfy',
					enableToggle: true,
					toggleHandler: function(item, pressed){
						this.cardPanel.getLayout().setActiveItem(pressed?1:0);

						var thumbs = this.gridStore.reader.jsonData.thumbs=='1';
						if(thumbs!=pressed)
							alert('switch');
					},
					scope:this
				},{
					xtype: 'tbsearch'
				}
			],
			columns: [
				{
					id: 'id',
					hidden: true,
					header: 'ID',
					width: 40,
					sortable: true,
					dataIndex: 'id'
				},
				{
					id: 'name',
					header: t('Name'),
					width: 75,
					sortable: true,
					dataIndex: 'name'
				},
				{
					xtype:"datecolumn",
					id: 'createdAt',
					header: t('Created at'),
					width: 160,
					sortable: true,
					dataIndex: 'createdAt',
					hidden: true
				},
				{					
					xtype:"datecolumn",
					hidden: false,
					id: 'modifiedAt',
					header: t('Modified at'),
					width: 160,
					sortable: true,
					dataIndex: 'modifiedAt'
				}
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>',
			},
			autoExpandColumn: 'name',
			// config options for stateful behavior
			stateful: true,
			stateId: 'files-grid'
		});

		go.modules.community.files.NodeGrid.superclass.initComponent.call(this);
	}
});

