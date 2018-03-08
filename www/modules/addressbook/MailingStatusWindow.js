GO.addressbook.MailingStatusWindow = function(config){

	config = config || {};

	config.title=GO.addressbook.lang.sentMailings;
	config.id='ml-sent-mailings';

	config.width=770;
	config.height=500;

	config.layout='fit';
	
	config.listeners={
		scope:this,
		show:function(){
			Ext.TaskMgr.start(this.refreshTask);
		},
		hide:function(){
			Ext.TaskMgr.stop(this.refreshTask);
		}
	};
	
	this.refreshTask = {
			run: function(){
				this.sentMailingsGrid.store.load({
					params: {
						start : this.sentMailingsGrid.bottomToolbar.cursor
					}
				})
			},
			scope:this,
			interval:5000
		};
	

	config.items=this.sentMailingsGrid = new GO.addressbook.SentMailingsGrid();

	
	

	GO.addressbook.MailingStatusWindow.superclass.constructor.call(this, config);
}

Ext.extend(GO.addressbook.MailingStatusWindow, GO.Window,{
	refreshTask : false
});