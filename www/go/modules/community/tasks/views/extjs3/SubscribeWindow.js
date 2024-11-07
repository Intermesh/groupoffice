go.modules.community.tasks.SubscribeWindow = Ext.extend(Ext.Window, {
	layout:'fit',
	width: 400,
	initComponent() {
		this.title = t('Subscribe to Tasklist');
		this.height = 800;
		const store = new go.data.Store({
			filters:{subscribed: {isSubscribed: false}, "role": {role: this.support ? "support" : "list"}},
			fields: ['id', 'name', 'isSubscribed'],
			sort:[{property:'name',isAscending:true}],
			entityStore: this.support ? 'SupportList' : 'TaskList',
		});

		this.on('render', () => {
			store.load();
		} )

		this.items = [


			this.grid = new go.grid.GridPanel({
				tbar: [
				 '->', {
						xtype: "tbsearch"
					}
					],
			style:{width:'100%'},
			hideHeaders: true,
			store,
			autoExpandColumn: 'name',
			columns: [
				{id:'name',dataIndex:'name'},
				{xtype: 'actioncolumn',
					dataIndex:'id',
					width: 100,
					items: [{
						//iconCls : 'ic-check',
						text : t("Subscribe"),
						handler: (me,row) => {
							let record = me.store.getAt(row);
							me.store.entityStore.save({isSubscribed: true}, record.data.id);
						}
					}]}

			]
		})];

		this.supr().initComponent.call(this);
	}
})