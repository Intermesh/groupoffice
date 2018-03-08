GO.tasks.ScheduleCallMenuItem = Ext.extend(Ext.menu.Item,{
	linkConfig : { name : '', link: {model_id:0, model_name:0}},
	
	initComponent : function(){
		this.iconCls= 'tasks-call';
		this.text= GO.tasks.lang.scheduleCall;
		this.cls='x-btn-text-icon';
		this.disabled=true;
		this.handler= function()
		{
			if(!GO.tasks.scheduleCallDialog)
			{
				GO.tasks.scheduleCallDialog = new GO.tasks.ScheduleCallDialog();
			}
			GO.tasks.scheduleCallDialog.show(0,{link_config : this.linkConfig});			
		};
		
		GO.tasks.ScheduleCallMenuItem.superclass.initComponent.call(this);
	},
	
	setLinkConfig : function(config){
		
		this.linkConfig = config;
		this.setDisabled(false);
	}
});