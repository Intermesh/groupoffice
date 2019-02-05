/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CronGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.cron.CronGrid = Ext.extend(GO.grid.GridPanel,{
	changed : false,
	
	initComponent : function(){
		
		Ext.apply(this,{
			standardTbar:false,
			editDialogClass:GO.cron.CronDialog,
			tbar : new Ext.Toolbar({
				cls:'go-head-tb',
				items: [{
					xtype:'htmlcomponent',
					html:t("System task scheduler", "cron"),
					cls:'go-module-title-tbar'
				},{
					itemId:'add',
					iconCls: 'btn-add',
					text: t("Add"),
					cls: 'x-btn-text-icon',
					disabled:this.standardTbarDisabled,
					handler: function(){
						this.btnAdd();
					},
					scope: this
				},{
					itemId:'delete',
					iconCls: 'btn-delete',
					text: t("Delete"),
					cls: 'x-btn-text-icon',
					disabled:this.standardTbarDisabled,
					handler: function(){
						this.deleteSelected();
					},
					scope: this
				},
				'-',
				{
					iconCls: 'btn-refresh',
					text: t("Refresh"),
					cls: 'x-btn-text-icon',
					handler: function(){
						this.store.load();
					},
					scope: this
				}]
			//				'-',
			//				{
			//					itemId:'settings',
			//					iconCls: 'btn-settings',
			//					text: t("Settings"),
			//					cls: 'x-btn-text-icon',
			//					disabled:this.standardTbarDisabled,
			//					handler: function(){
			//						this.showSettingsDialog();
			//					},
			//					scope: this
			//				}]
			}),
			store: GO.cron.cronStore,
			border: false,
			paging:true,
			view:new Ext.grid.GridView({
				emptyText: t("No items to display")
			}),
			cm:new Ext.grid.ColumnModel({
				defaults:{
					sortable:true
				},
				columns:[
				{
					header: t("Enabled", "cron"),
					dataIndex: 'active',
					sortable: true,
					renderer: GO.grid.ColumnRenderers.coloredYesNo,
					width:70
				},
				{
					header: t("System task scheduler", "cron"),
					dataIndex: 'name',
					sortable: true,
					width:250
				},
				{
					header: t("Minutes", "cron"),
					dataIndex: 'minutes',
					sortable: true,
					width:100
				},
				{
					header: t("Hours", "cron"),
					dataIndex: 'hours',
					sortable: true,
					width:100
				},
				{
					header: t("Month days", "cron"),
					dataIndex: 'monthdays',
					sortable: true,
					width:100
				},
				{
					header: t("Months", "cron"),
					dataIndex: 'months',
					sortable: true,
					width:100
				},
				{
					header: t("Week days", "cron"),
					dataIndex: 'weekdays',
					sortable: true,
					width:100
				},
				//				{
				//					header: t("Years", "cron"),
				//					dataIndex: 'years',
				//					sortable: true,
				//					width:100
				//				},
				{
					header: t("Job", "cron"),
					dataIndex: 'job',
					sortable: true,
					width:250
				},
				{
					header: t("Next run", "cron"),
					dataIndex: 'nextrun',
					sortable: true,
					width: dp(140)
				},
				{
					header: t("Last run", "cron"),
					dataIndex: 'lastrun',
					sortable: true,
					width: dp(140)
				},
				{
					header: t("Completed at", "cron"),
					dataIndex: 'completedat',
					sortable: true,
					width: dp(140)
				},{
					header: t("Error", "cron"),
					dataIndex: 'error',
					maxLength:20,
					renderer: GO.grid.ColumnRenderers.Text
				}
				]
			})
		});
		GO.cron.CronGrid.superclass.initComponent.call(this);
		
		this.on('render', function(){
			GO.cron.cronStore.load();
		},this);
	},
	showSettingsDialog : function(){
		if(!this.settingsDialog){
			this.settingsDialog = new GO.cron.SettingsDialog();

			this.settingsDialog.on('save', function(){   
				this.store.load();
				this.changed=true;	    			    			
			}, this);	
		}
		this.settingsDialog.show();	  
	},
	deleteSelected : function(){
		GO.cron.CronGrid.superclass.deleteSelected.call(this);
		this.changed=true;
	}	
});
