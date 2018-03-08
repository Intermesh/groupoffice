/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CronGrid.js 17292 2014-04-08 11:19:49Z mschering $
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
					html:GO.cron.lang.name,
					cls:'go-module-title-tbar'
				},{
					itemId:'add',
					iconCls: 'btn-add',
					text: GO.lang['cmdAdd'],
					cls: 'x-btn-text-icon',
					disabled:this.standardTbarDisabled,
					handler: function(){
						this.btnAdd();
					},
					scope: this
				},{
					itemId:'delete',
					iconCls: 'btn-delete',
					text: GO.lang['cmdDelete'],
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
					text: GO.lang['cmdRefresh'],
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
			//					text: GO.lang['cmdSettings'],
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
				emptyText: GO.lang['strNoItems']
			}),
			cm:new Ext.grid.ColumnModel({
				defaults:{
					sortable:true
				},
				columns:[
				{
					header: GO.cron.lang.active,
					dataIndex: 'active',
					sortable: true,
					renderer: GO.grid.ColumnRenderers.coloredYesNo,
					width:70
				},
				{
					header: GO.cron.lang.name,
					dataIndex: 'name',
					sortable: true,
					width:250
				},
				{
					header: GO.cron.lang.minutes,
					dataIndex: 'minutes',
					sortable: true,
					width:100
				},
				{
					header: GO.cron.lang.hours,
					dataIndex: 'hours',
					sortable: true,
					width:100
				},
				{
					header: GO.cron.lang.monthdays,
					dataIndex: 'monthdays',
					sortable: true,
					width:100
				},
				{
					header: GO.cron.lang.months,
					dataIndex: 'months',
					sortable: true,
					width:100
				},
				{
					header: GO.cron.lang.weekdays,
					dataIndex: 'weekdays',
					sortable: true,
					width:100
				},
				//				{
				//					header: GO.cron.lang.years,
				//					dataIndex: 'years',
				//					sortable: true,
				//					width:100
				//				},
				{
					header: GO.cron.lang.job,
					dataIndex: 'job',
					sortable: true,
					width:250
				},
				{
					header: GO.cron.lang.nextrun,
					dataIndex: 'nextrun',
					sortable: true,
					width:110
				},
				{
					header: GO.cron.lang.lastrun,
					dataIndex: 'lastrun',
					sortable: true,
					width:110
				},
				{
					header: GO.cron.lang.completedat,
					dataIndex: 'completedat',
					sortable: true,
					width:110
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