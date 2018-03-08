GO.email.LinkedMessagePanel = Ext.extend(GO.email.MessagePanel,{
	initComponent : function(){
		this.tbar=[{
					iconCls: 'btn-print',
					text: GO.lang.cmdPrint,
					cls: 'x-btn-text-icon',
					handler: function(){
						this.body.print();
					},
					scope: this
				},
				'-',
				{
					iconCls: 'btn-reply',
					text: GO.email.lang.reply,
					cls: 'x-btn-text-icon',
					handler: function(){
						GO.email.showComposer({
							task:'reply',
							loadParams : {
								is_tmp_file:this.data.is_tmp_file,
								path:this.data.path
							}
						});
					},
					scope: this
				},
				{
					iconCls: 'btn-reply-all',
					text: GO.email.lang.replyAll,
					cls: 'x-btn-text-icon',
					handler: function(){
						GO.email.showComposer({
							task:'reply_all',
							loadParams : {
								is_tmp_file:this.data.is_tmp_file,
								path:this.data.path
							}
						});
					},
					scope: this
				},
				{
					iconCls: 'btn-forward',
					text: GO.email.lang.forward,
					cls: 'x-btn-text-icon',
					handler: function(){						
						GO.email.showComposer({
							task:'forward',
							loadParams : {
								is_tmp_file:this.data.is_tmp_file,
								path:this.data.path
							}
						});
					},
					scope: this
				},
//				{
//					iconCls: 'btn-edit',
//					text: GO.lang.cmdEdit,
//					handler: function(){
//						var composer = GO.email.showComposer({
//							task:'opendraft',
//							loadParams : {
//								is_tmp_file:this.data.is_tmp_file,
//								path:this.data.path
//							},
//							saveToPath:this.data.path
//						});
//						
//						composer.on('hide', this.reload, this, {single:true});
//					},
//					scope: this
//				},
				this.linkButton = new Ext.Button({
					iconCls: 'btn-link',
					text: GO.lang.cmdLink,
					hidden:true,
					handler: function(){
						if(!this.linksDialog)
						{
							this.linksDialog = new GO.dialog.LinksDialog();							
						}

						this.linksDialog.setSingleLink(this.data.id, "GO\\Savemailas\\Model\\LinkedEmail");
						this.linksDialog.show();
								},
					scope: this
				})];

		GO.email.LinkedMessagePanel.superclass.initComponent.call(this);
	},
	border:false,
	autoScroll:true,
	editHandler : function(){
		//needed because it needs to be compatible with javascript/DisplayPanel.js
	},
	loadUrl: '',
	reload : function (){
		this.load(this.lastId, this.lastConfig);	
	},
	load : function(id, config){

	 config = config || {};
	 
	 this.lastConfig=config;
	 this.lastId=id;
		
		if(!this.remoteMessage)
			this.remoteMessage={};

		this.messageId=id;		
		this.remoteMessage.id=this.messageId;

		this.loadUrl = '';
		switch(config.action){
			
			case 'path':
				this.loadUrl=("savemailas/linkedEmail/loadPath");
			break;
			
			case 'attachment':
				this.loadUrl = ("email/message/messageAttachment");
				break;
				
			case 'file':
				this.loadUrl=("savemailas/linkedEmail/loadFile");
				break;
				
			default:
				this.loadUrl=("savemailas/linkedEmail/loadLink");
				
				break;
			
		}

		GO.request({
			maskEl:this.el,
			url: this.loadUrl,
			params: this.remoteMessage,
			scope: this,
			success: function(options, response, data)
			{				
				this.setMessage(data);
				
				
				if(this.data.is_linked_message)
					this.linkButton.show();
				else
					this.linkButton.hide();
			}
		});
	},
	listeners:{
		scope:this,
		linkClicked: function(href){
			var win = window.open(href);
			win.focus();
		},
		attachmentClicked: function(attachment, panel){
			if(attachment.mime=='message/rfc822')
			{
				GO.email.showMessageAttachment(0, {
					action:'path',
					path:attachment.tmp_file,
					isTempFile:true
				});
//			} else if(attachment.extension == 'vcf') {
//			// Not possible at the moment
//				GO.url('/addressbook/contact/handleAttachedVCard')
//				GO.email.readVCard(attachment.url+'&importVCard=1');
			} else {
				window.open(attachment.url);
			}
		}
	}

});

