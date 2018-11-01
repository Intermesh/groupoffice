GO.calendar.TaskContextMenu = function(config){

	if(!config)
	{
		config = {};
	}

	config.items=[
	this.actionInfo = new Ext.menu.Item({
		iconCls: 'btn-properties',
		text:t("Details", "calendar"),
		cls: 'x-btn-text-icon',
		scope:this,
		handler: function()
		{
			this.showTaskInfoDialog();
		}
	})
	]

	GO.calendar.TaskContextMenu.superclass.constructor.call(this,config);

}

Ext.extend(GO.calendar.TaskContextMenu, Ext.menu.Menu, {

	task:null,
	view_id: 0,
	
	setTask : function(task)
	{
		this.task = task;
	},
	
	showTaskInfoDialog : function()
	{		
		go.Router.goto("#task/" + this.task.task_id);
	}
//	,
//	menuHandler : function()
//	{
//		if(!this.menuRecurrenceDialog)
//		{
//			this.menuRecurrenceDialog = new GO.calendar.RecurrenceDialog();
//
//			this.menuRecurrenceDialog.on('single', function()
//			{
//				this.showSelectDateDialog(false, false);
//				this.menuRecurrenceDialog.hide();
//			},this)
//
//			this.menuRecurrenceDialog.on('entire', function()
//			{
//				this.showSelectDateDialog(false, true);
//				this.menuRecurrenceDialog.hide();
//			},this)
//
//			this.menuRecurrenceDialog.on('cancel', function()
//			{
//				this.menuRecurrenceDialog.hide();
//			},this)
//		}
//		this.menuRecurrenceDialog.show();
//	}
});
