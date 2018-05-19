
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

go.modules.core.users.SystemSettingsUserGrid = Ext.extend(go.grid.GridPanel, {
	iconCls: 'ic-account-box',
	initComponent: function () {
		
		var actions = this.initRowActions();
		
		this.title = t("Users");

		this.store = new go.data.Store({
			fields: [
				'id', 
				'username', 
				'displayName',
				'avatarId',
				'loginCount',
				{name: 'createdAt', type: 'date'},
				{name: 'lastLogin', type: 'date'}	
			],
			entityStore: go.Stores.get("User")
		});

		Ext.apply(this, {		
			plugins: [actions],
			tbar: [ '->', 
				{
					xtype: 'tbsearch'
				},{					
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (e, toolEl) {
						var dlg = new go.modules.core.users.CreateUserWizard();
						dlg.show();
					}
				}
				
			],
			columns: [
				{
					id: 'name',
					header: t('Name'),
					width: dp(200),
					sortable: true,
					dataIndex: 'displayName',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						var style = record.get('avatarId') ?  'background-image: url(' + go.Jmap.downloadUrl(record.get("avatarId")) + ')"' : "";
						
						return '<div class="user"><div class="avatar" style="'+style+'"></div>' +
							'<div class="wrap">'+
								'<div class="displayName">' + record.get('displayName') + '</div>' +
								'<small class="username">' + record.get('username') + '</small>' +
							'</div>'+
							'</div>';
					}
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
				{
					xtype:"datecolumn",
					id: 'lastLogin',
					header: t('Last login'),
					width: dp(160),
					sortable: true,
					dataIndex: 'lastLogin',
					hidden: false
				},{
					id: 'loginCount',
					align: "right",
					header: t('Logins'),
					width: dp(100),
					sortable: true,
					dataIndex: 'loginCount',
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

		go.modules.core.users.SystemSettingsUserGrid.superclass.initComponent.call(this);
		
		this.on('render', function() {
			this.store.load();
		}, this);
		
		this.on('rowdblclick', function(grid, rowIndex, e) {
			this.edit(this.store.getAt(rowIndex).id);
		}); 
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
						handler: function() {this.edit(this.moreMenu.record.id);},
						scope: this						
					},{
						itemId:"loginAs",
						iconCls: 'ic-swap-horiz',
						text: t("Login as this user"),
						handler: function() {
							alert('TODO');
						},
						scope: this						
					},
					"-"
					,{
						itemId:"delete",
						iconCls: 'ic-delete',
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
	},
	
	edit : function(id) {

		var dlg = new go.usersettings.UserSettingsDialog();
		dlg.show(id);
						
	}
	
});

