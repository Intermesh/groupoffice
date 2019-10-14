/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PeriodGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

go.cron.PeriodGrid = Ext.extend(go.grid.GridPanel,{
	changed : false,
	
	initComponent : function(){
		
		Ext.apply(this,{
			standardTbar:false,
			store: go.cron.periodStore,
			editDialogClass:go.cron.CronDialog,
			border: false,
			tbar:[{
				iconCls: 'ic-refresh',
				text: t("Refresh"),
				handler: function(){
					this.store.load();
				},
				scope: this
			}],
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
					header: t("System task scheduler", "cron"),
					dataIndex: 'name',
					sortable: true,
					width:100
				},
				{
					header: t("Job", "cron"),
					dataIndex: 'job',
					sortable: true,
					width:180
				},
				{
					header: t("Next run", "cron"),
					dataIndex: 'nextrun',
					sortable: true,
					width:100
				},
				{
					header: t("Last run", "cron"),
					dataIndex: 'lastrun',
					sortable: true,
					width:100
				},
				{
					header: t("Minutes", "cron"),
					dataIndex: 'minutes',
					sortable: true,
					width:100,
					hidden:true
				},
				{
					header: t("Hours", "cron"),
					dataIndex: 'hours',
					sortable: true,
					width:100,
					hidden:true
				},
				{
					header: t("Month days", "cron"),
					dataIndex: 'monthdays',
					sortable: true,
					width:100,
					hidden:true
				},
				{
					header: t("Months", "cron"),
					dataIndex: 'months',
					sortable: true,
					width:100,
					hidden:true
				},
				{
					header: t("Week days", "cron"),
					dataIndex: 'weekdays',
					sortable: true,
					width:100,
					hidden:true
				},
				{
					header: t("Years", "cron"),
					dataIndex: 'years',
					sortable: true,
					width:100,
					hidden:true
				},
				{
					header: t("Enabled", "cron"),
					dataIndex: 'active',
					sortable: true,
					renderer: GO.grid.ColumnRenderers.coloredYesNo,
					width:50,
					hidden:true
				}
				]
			})
		});
		go.cron.PeriodGrid.superclass.initComponent.call(this);
		
		go.cron.periodStore.load();
	}	
});
