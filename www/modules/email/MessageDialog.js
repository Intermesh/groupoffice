GO.email.MessageDialog = function(config){	
	
	if(!config)
	{
		config={};
	}

	this.messagePanel = new GO.email.MessagePanel({
		autoScroll:true,
		attachmentContextMenu: new GO.email.AttachmentContextMenu()
	});

	this.toolbar =[
	this.replyButton=new Ext.Button({
		disabled:false,
		iconCls: 'btn-reply',
		text: GO.email.lang.reply,
		cls: 'x-btn-text-icon',
		handler: function(){

			GO.email.showComposer({
				uid: this.messagePanel.uid,
				task: 'reply',
				mailbox: this.messagePanel.mailbox,
				account_id: this.messagePanel.account_id
			});
		},
		scope: this
	}),this.replyAllButton=new Ext.Button({
		disabled:false,
		iconCls: 'btn-reply-all',
		text: GO.email.lang.replyAll,
		cls: 'x-btn-text-icon',
		handler: function(){
			GO.email.showComposer({
				uid: this.messagePanel.uid,
				task: 'reply_all',
				mailbox: this.messagePanel.mailbox,
				account_id: this.messagePanel.account_id

			});
		},
		scope: this
	}),this.forwardButton=new Ext.Button({
		disabled:false,
		iconCls: 'btn-forward',
		text: GO.email.lang.forward,
		cls: 'x-btn-text-icon',
		handler: function(){
			GO.email.showComposer({
				uid: this.messagePanel.uid,
				task: 'forward',
				mailbox: this.messagePanel.mailbox,
				account_id: this.messagePanel.account_id
			});
		},
		scope: this
	}),

	this.printButton = new Ext.Button({
		disabled: false,
		iconCls: 'btn-print',
		text: GO.lang.cmdPrint,
		cls: 'x-btn-text-icon',
		handler: function(){
			this.messagePanel.body.print();
		},
		scope: this
	})];
	
	config.layout='fit';
	config.title=GO.email.lang.message;
	config.stateId='email-message-dialog';
	config.maximizable=true;
	config.collapsible=true;
	config.modal=false;
	config.width=600;
	config.height=500;
	config.resizable=true;
	config.minizable=true;
//	config.closeAction='hide';	
	config.items=this.messagePanel;
	config.tbar=this.toolbar;
	config.buttons=[{	
		text: GO.lang['cmdClose'],
		handler: function()
		{
			this.hide();
		},
		scope:this
	}];
	
	GO.email.MessageDialog.superclass.constructor.call(this, config);
}

Ext.extend(GO.email.MessageDialog, Ext.Window,{
	closeAction:'hide',	
	
	showData : function(data){
		GO.email.MessageDialog.superclass.show.call(this);
		
		this.messagePanel.setData(data);
	},
		
	show : function(uid, mailbox, account_id, no_max_body_size)
	{
		if(!this.rendered)
			this.render(Ext.getBody());

		this.messagePanel.loadMessage(uid, mailbox, account_id, false, no_max_body_size);
				
		GO.email.MessageDialog.superclass.show.call(this);
	}
});

GO.email.showMessageDialog = function(uid,mailbox,account_id,no_max_body_size) {
	
	// no_max_body must be boolean. If true, the email body is not truncated when
	// it exceeds the maximum size.
	
	if (!GO.email.messageDialog){
		GO.email.messageDialog = new GO.email.MessageDialog();
		GO.email.messageDialog.messagePanel.on('attachmentClicked', GO.email.openAttachment, this);
	}

	GO.email.messageDialog.show(uid,mailbox,account_id,no_max_body_size);
	
}