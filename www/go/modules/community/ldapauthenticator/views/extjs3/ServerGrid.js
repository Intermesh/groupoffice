
go.modules.community.ldapauthenticator.ServerGrid = Ext.extend(go.grid.GridPanel, {
	viewConfig: {
		forceFit: true,
		autoFill: true
	},

	initComponent: function () {

		var actions = this.initRowActions();

		Ext.apply(this, {	
			tbar: [
				{
					xtype:'tbtitle',
					text: t("LDAP Authenticator", "ldapauthenticator")
				},
				'->',
				{
					iconCls: 'ic-add',
					handler: function() {
						var form = new go.modules.community.ldapauthenticator.ServerForm();
						form.show();
					}
			}],
			
			store: new go.data.Store({
				fields: ['id', 'hostname'],
				entityStore: "LdapAuthServer"				
			}),
			autoHeight: true,
			plugins: [actions],
			columns: [
				{
					id: 'hostname',
					header: t('Hostname', 'ldapauthenticator'),
					sortable: false,
					dataIndex: 'hostname',
					hideable: false,
					draggable: false,
					menuDisabled: true
				},
				actions
			],
			listeners : {
				render: function() {
					this.store.load();
				},
				scope: this
			}
		});

		go.modules.community.ldapauthenticator.ServerGrid.superclass.initComponent.call(this);
	},

	initRowActions: function () {

		var actions = new Ext.ux.grid.RowActions({
			menuDisabled: true,
			hideable: false,
			draggable: false,
			fixed: true,
			header: '',
			hideMode: 'display',
			keepSelection: true,

			actions: [{
					iconCls: 'ic-edit'
				}]
		});

		actions.on({
			action: function (grid, record, action, row, col, e, target) {
				console.log(action);
//				switch (action.iconCls) {
//					case 'ic-edit':
						var form = new go.modules.community.ldapauthenticator.ServerForm();
						form.load(record.data.id).show();
//						break;
//				}
			},
			scope: this
		});

		return actions;

	}
});
