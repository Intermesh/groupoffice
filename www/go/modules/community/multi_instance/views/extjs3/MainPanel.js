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

go.modules.community.multi_instance.MainPanel = Ext.extend(go.grid.GridPanel, {

	initComponent: function () {
		
		var actions = this.initRowActions();

		this.store = new go.data.Store({
			fields: [
				'id', 
				'hostname', 
				'userCount',
				{name: 'createdAt', type: 'date'}, 
				{name: 'lastLogin', type: 'date'},
				'adminDisplayName',
				'adminEmail',
				'enabled',
				'loginCount'
			],
			entityStore: go.Stores.get("Instance")
		});

		Ext.apply(this, {		
			plugins: [actions],
			tbar: [ '->', {					
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (e, toolEl) {
						var dlg = new go.modules.community.multi_instance.InstanceDialog();
						dlg.show();
					}
				}, {
					iconCls: 'ic-more-vert',
					menu: [{
							iconCls: 'ic-email', 
							text: t("E-mail selected"),
							handler: function() {
								var records = this.getSelectionModel().getSelections();
								
								console.log(records);
								
								var str = "";
								Ext.each(records, function(r) {
									if(r.data.adminEmail && str.indexOf(r.data.adminEmail) == -1) {
										str +=  '"' + r.data.adminDisplayName.replace(/"/g, '\\"') + '" &lt;' + r.data.adminEmail + '&gt;, ';
									}
								});
								
								console.log(str);
								
								Ext.MessageBox.alert("E-mail addresses", str);
							},
							scope: this
					}]
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
					id: 'hostname',
					header: t('Hostname'),
					width: 200,
					sortable: true,
					dataIndex: 'hostname',
					renderer: function(value, metaData, record, rowIndex, colIndex, store) {
						if(!record.data.enabled) {
							metaData.css = "deactivated";
						}
						
						return value;
					}
				},				
				{
					xtype:"datecolumn",
					id: 'createdAt',
					header: t('Created at'),
					width: 160,
					sortable: true,
					dataIndex: 'createdAt',
					hidden: false
				},
				{
					xtype:"datecolumn",
					id: 'lastLogin',
					header: t('Last login'),
					width: 160,
					sortable: true,
					dataIndex: 'lastLogin',
					hidden: false
				},{
					id: 'userCount',
					header: t('User count'),
					width: 160,
					sortable: true,
					dataIndex: 'userCount',
					hidden: false
				},{
					id: 'loginCount',
					header: t('Login count'),
					width: 160,
					sortable: true,
					dataIndex: 'loginCount',
					hidden: false
				},{
					header: t('Admin name'),
					width: 160,
					sortable: true,
					dataIndex: 'adminDisplayName'
				},{
					header: t('Admin E-mail'),
					width: 160,
					sortable: true,
					dataIndex: 'adminEmail'
				},
				actions
				
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>',
				forceFit: true,
				autoFill: true
			},
			autoExpandColumn: 'hostname',
			// config options for stateful behavior
			stateful: true,
			stateId: 'multi_instance-grid'
		});

		go.modules.community.multi_instance.MainPanel.superclass.initComponent.call(this);
		
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
						itemId:"login",
						iconCls: 'ic-lock-open',
						text: t("Login as administrator"),
						handler: function() {
							
							var win = window.open("about:blank", "groupoffice_instance");
							go.Jmap.request({
								method: "community/multi_instance/Instance/login",
								params: {
									id: this.moreMenu.record.get('id')
								},
								callback: function(options, success, result) {
									
									//POST access token to popup for enhanced security
									var f = document.createElement("form");
									f.setAttribute('method',"post");
									f.setAttribute('target',"groupoffice_instance");
									f.setAttribute('action',document.location.protocol + "//" + this.moreMenu.record.get('hostname'));
									
									var i = document.createElement("input"); //input element, text
									i.setAttribute('type',"hidden");
									i.setAttribute('name',"accessToken");
									i.setAttribute('value',result.accessToken);									
									f.appendChild(i);
									
									var body = document.getElementsByTagName('body')[0];
									body.appendChild(f);									
									f.submit();																	
									body.removeChild(f);									
									
								},
								scope: this
							});
						},
						scope: this						
					}, '-',
					{
						itemId:"deactivate",
						iconCls: 'ic-block',
						text: t("Deactivate instance"),
						handler: function() {
							
							var update = {};
							update[this.moreMenu.record.id] = {enabled: !this.moreMenu.record.data.enabled};
						
							go.Stores.get("Instance").set({
								update: update
							});
							
						},
						scope: this
					},{
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete instance"),
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
		this.moreMenu.items.item("deactivate").setText(record.data.enabled ? t("Deactivate instance") : t("Activate instance"));
		this.moreMenu.showAt(e.getXY());
	}
});

