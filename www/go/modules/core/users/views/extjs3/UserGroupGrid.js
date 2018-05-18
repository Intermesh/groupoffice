
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 19225 2015-06-22 15:07:34Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

go.modules.core.users.UserGroupGrid = Ext.extend(go.grid.GridPanel, {
	initComponent: function () {
	
		this.title = t("Groups");
		
		
		var checkColumn = new GO.grid.CheckColumn({
			dataIndex: 'selected'
		});
		
		

		this.store = new go.data.Store({
			fields: [
				'id', 
				'name'				
			],
			entityStore: go.Stores.get("User")
		});

		Ext.apply(this, {		
			plugins: [checkColumn],
			tbar: [ '->', 
				{
					xtype: 'tbsearch'
				},{					
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (e, toolEl) {
						var dlg = new go.modules.core.users.CreateUserDialog();
						dlg.show();
					}
				}
				
			],
			columns: [
				{
					id: 'username',
					header: t('Username'),
					width: dp(200),
					sortable: true,
					dataIndex: 'username'
				},{
					id: 'displayName',
					header: t('Display name'),
					width: dp(200),
					sortable: true,
					dataIndex: 'displayName'
				},
				{
					xtype:"datecolumn",
					id: 'createdAt',
					header: t('Created at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'createdAt',
					hidden: false
				},	
				actions
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>',
				forceFit: true,
				autoFill: true
			},
			// config options for stateful behavior
			stateful: true,
			stateId: 'users-grid'
		});

		go.modules.community.apikeys.KeyGrid.superclass.initComponent.call(this);
		
		this.on('render', function() {
			this.store.load();
		}, this);
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
					iconCls: 'ic-more-vert'
				}]
		});

		actions.on({
			action: function (grid, record, action, row, col, e, target) {
				this.showMoreMenu(record, e);
			},
			scope: this
		});

		return actions;

	},
	
	showMoreMenu : function(record, e) {
		if(!this.moreMenu) {
			this.moreMenu = new Ext.menu.Menu({
				items: [
					{
						itemId: "view",
						iconCls: 'ic-edit',
						text: t("Edit"),
						handler: function() {
							var dlg = new go.usersettings.UserSettingsDialog();
							dlg.show(this.moreMenu.record.id);
						},
						scope: this						
					},{
						itemId:"delete",
						iconCls: 'ic-share',
						text: t("Delete"),
						handler: function() {
							this.getSelectionModel().selectRecords([this.moreMenu.record]);
							this.deleteSelected();
						},
						scope: this						
					},
				]
			})
		}
		
		this.moreMenu.record = record;
		
		this.moreMenu.showAt(e.getXY());
	},
	
	load : function() {
		console.log("TODO this funciton should be renamed");
	}
});


