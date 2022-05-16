go.permissions.AclOverviewGrid = Ext.extend(go.grid.GridPanel, {

	initComponent: function() {

		Ext.apply(this,{
			tbar: [
				{xtype:'component',html: t('Filter')},{xtype:'groupcomboreset', listeners: {
					'select': (_,r) => {this.store.setFilter('group', {groupId:r.id}).load() },
					'change': (_,v) => {this.store.setFilter('group', {groupId:v}).load() }
				}},
				{xtype:'component',html:' &nbsp; '+ t('Module')}, this.typeFilter({listeners: {
					'select': (_,r) => {this.store.setFilter('module', {moduleId:r.data.id}).load() },
					'change': (_,v) => {this.store.setFilter('module', {moduleId:v}).load() },
				}}),
				{
					xtype:'buttongroup',
					defaults:{
						toggleHandler: (btn, state) => state && this.store.setFilter('type', {type:btn.itemId}).load(),
						toggleGroup: 'permissionOwnerType'
					},
					items: [
						{text:t('All'), itemId: 'both', pressed:true},
						{text:t('Users'), itemId: 'users'},
						{text:t('Groups'), itemId: 'groups'},
					]
				}
			],
			store: new go.data.Store({
				fields: [
					'entityId',
					'level',
					'type',
					'userGroup',
					'module',
					'username',
					'name'
				],
				method: "Acl/overview"
			}),
			loadMask:true,
			viewConfig: {totalDisplay: false},
			columns: [{
				id: 'user',
				header: t('User')+' / '+t('Group'),
				dataIndex: 'username',
				width:160,
				renderer: (v,m,r) => '<i class="icon">'+(r.data.userGroup==0 ? 'group ':'person')+'</i> '+v
			},{
				header: t('Module'),
				width: dp(160),
				dataIndex: 'module',
				renderer: v => t('name', v)
			},{
				header: t('Type'),
				width: dp(160),
				dataIndex: 'type'
			},{
				header: t('Item ID'),
				width: dp(80),
				dataIndex: 'entityId',
				align: "right"
			},{
				header: t('Name'),
				dataIndex: 'name',
				width:240,
				renderer: v => v || '<sub>+2000 rows, use filters to show name</sub>'
			},{
				header: t('Level'),
				dataIndex: "level",
				width: dp(120),
				renderer: v => ({
						10: t("Read only"),
						20: t("Read and Create only"),
						30: t("Write"),
						40: t("Write and delete"),
						50: t("Manage")
					}[v] || v)
			// },{
			// 	xtype: 'actioncolumn',
			// 	width: 90,
			// 	items: [
			// 		{
			// 			//isDisabled: (v,meta,rec) => rec.get('closed') || rec.get('approved') ? true : false,
			// 			iconCls : 'ic-open-in-window',
			// 			text : t("Show"),
			// 			handler: function (me,row) {
			// 				let record = me.store.getAt(row);
			// 				var win = new go.links.LinkDetailWindow({
			// 					entity: record.data.type
			// 				});
			//
			// 				win.load(record.data.entityId);
			// 			}
			// 		}]
			}]
		});

		this.supr().initComponent.call(this);

	//	this.on('afterrender', () => { this.loadMask.show(); this.store.load() });
	},


	typeFilter: function(cfg) {

		// var data = []
		// go.Entities.getAll().forEach(function (m) {
		// 	console.log(m);
		// 	if (m.isAclOwner || m.name == "Folder") {
		// 		data.push([m.id, m.name]);
		// 	}
		// });

		return new go.form.ComboBoxReset(Ext.apply(cfg,{
			emptyText: t("Please select..."),
			hiddenName: 'typeFilter',
			mode:'remote',
			valueField: 'id',
			triggerAction: 'all',
			displayField: 'name',
			editable: false,
			tpl: '<tpl for="."><div class="x-combo-list-item">{[t("name", values.name, values.package)]} - {name}</div></tpl>',
			selectOnFocus: true,
			forceSelection: true,
			store: {
				xtype:'gostore',
				fields: ['id', 'name', 'package'],
				entityStore: 'Module'
			}
		}));

	}
});