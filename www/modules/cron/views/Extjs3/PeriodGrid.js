/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PeriodGrid.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.cron.PeriodGrid = Ext.extend(GO.grid.GridPanel,{
	changed : false,
	
	initComponent : function(){
		
		Ext.apply(this,{
			standardTbar:false,
			store: GO.cron.periodStore,
			editDialogClass:GO.cron.CronDialog,
			border: false,
			tbar:[{
					iconCls: 'btn-refresh',
					text: GO.lang['cmdRefresh'],
					cls: 'x-btn-text-icon',
					handler: function(){
						this.store.load();
					},
					scope: this
				}],
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
					header: GO.cron.lang.name,
					dataIndex: 'name',
					sortable: true,
					width:100
				},
				{
					header: GO.cron.lang.job,
					dataIndex: 'job',
					sortable: true,
					width:180
				},
				{
					header: GO.cron.lang.nextrun,
					dataIndex: 'nextrun',
					sortable: true,
					width:100
				},
				{
					header: GO.cron.lang.lastrun,
					dataIndex: 'lastrun',
					sortable: true,
					width:100
				},
				{
					header: GO.cron.lang.minutes,
					dataIndex: 'minutes',
					sortable: true,
					width:100,
					hidden:true
				},
				{
					header: GO.cron.lang.hours,
					dataIndex: 'hours',
					sortable: true,
					width:100,
					hidden:true
				},
				{
					header: GO.cron.lang.monthdays,
					dataIndex: 'monthdays',
					sortable: true,
					width:100,
					hidden:true
				},
				{
					header: GO.cron.lang.months,
					dataIndex: 'months',
					sortable: true,
					width:100,
					hidden:true
				},
				{
					header: GO.cron.lang.weekdays,
					dataIndex: 'weekdays',
					sortable: true,
					width:100,
					hidden:true
				},
				{
					header: GO.cron.lang.years,
					dataIndex: 'years',
					sortable: true,
					width:100,
					hidden:true
				},
				{
					header: GO.cron.lang.active,
					dataIndex: 'active',
					sortable: true,
					renderer: GO.grid.ColumnRenderers.coloredYesNo,
					width:50,
					hidden:true
				}
				]
			})
		});
		GO.cron.PeriodGrid.superclass.initComponent.call(this);
		
		GO.cron.periodStore.load();
	}	
});