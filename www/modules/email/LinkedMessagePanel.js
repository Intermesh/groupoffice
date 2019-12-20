GO.email.LinkedMessagePanel = Ext.extend(GO.email.MessagePanel,{
	initComponent : function(){
		this.tbar=[{
					iconCls: 'btn-print',
					text: t("Print"),
					cls: 'x-btn-text-icon',
					handler: function(){
						this.body.print();
					},
					scope: this
				},
				'-',
				{
					iconCls: 'btn-reply',
					text: t("Reply", "email"),
					cls: 'x-btn-text-icon',
					handler: function(){
						var comp = GO.email.showComposer({
							task:'reply',
							loadParams : {
								is_tmp_file:this.data.is_tmp_file,
								path:this.data.path
							}
						});

						this.data.links.forEach(function(link) {
							comp.createLinkButton.addLink(link.entity, link.entityId);
						});
					},
					scope: this
				},
				{
					iconCls: 'btn-reply-all',
					text: t("Reply all", "email"),
					cls: 'x-btn-text-icon',
					handler: function(){
						var comp = GO.email.showComposer({
							task:'reply_all',
							loadParams : {
								is_tmp_file:this.data.is_tmp_file,
								path:this.data.path
							}
						});

						this.data.links.forEach(function(link) {
							comp.createLinkButton.addLink(link.entity, link.entityId);
						});
					},
					scope: this
				},
				{
					iconCls: 'btn-forward',
					text: t("Forward", "email"),
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
//					text: t("Edit"),
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
				this.addButton = this.newMenuButton = new go.detail.addButton({			
					detailView: this,
					noFiles: true
				})
		];

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
	entity: "LinkedEmail",
	load : function(id, config){

	 config = config || {};
	 
	 this.lastConfig=config;
	 this.lastId=id;
		
		if(!this.remoteMessage)
			this.remoteMessage={};

		this.messageId=this.currentId=id;		
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
					this.addButton.show();
				else
					this.addButton.hide();
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
			if(attachment.mime=='message/rfc822' || attachment.mime=='application/eml')
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

