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

go.modules.community.apikeys.KeyGrid = Ext.extend(go.grid.GridPanel, {

	initComponent: function () {
		
		var actions = this.initRowActions();
	
		this.store = new go.data.Store({
			fields: [
				'id', 
				'name', 
				'accessToken',
				{name: 'createdAt', type: 'date'}			
			],
			entityStore: "Key"
		});

		Ext.apply(this, {		
			plugins: [actions],
			tbar: [  {					
					text: t('Add key'),
					handler: function (e, toolEl) {
						var dlg = new go.modules.community.apikeys.KeyDialog();
						dlg.show();
					}
				}				
			],
			columns: [
				{
					id: 'id',
					hidden: true,
					header: 'ID',
					width: dp(40),
					sortable: true,
					dataIndex: 'id'
				},
				{
					id: 'name',
					header: t('Name'),
					width: dp(200),
					sortable: true,
					dataIndex: 'name'
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
			stateId: 'apikeys-grid'
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
						iconCls: 'ic-search',
						text: t("View access token"),
						handler: function() {
							alert(this.moreMenu.record.get('accessToken'));
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
	}
});

