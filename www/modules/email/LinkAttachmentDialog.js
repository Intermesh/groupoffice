GO.email.LinkAttachmentDialog = Ext.extend(go.links.CreateLinkWindow,{

	attachmentItem : null, // If this is set to null, then it saves all attachments of the message.
	messagePanel : null,
	attachmentHandle : null,
	attachmentsView : null,
	
	constructor : function(config){
		
		config = config || {};
		
		Ext.apply(config, {
			title:t("Save the attachment to these items", "email"),
			singleSelect:true,
			filesupport:true
		});

		GO.email.LinkAttachmentDialog.superclass.constructor.call(this,config);
	},
	setAttachmentHandle : function(handler) {
		this.attachmentHandle = handler;
	},
	setAttachmentsView : function(attachmentsView) {
		this.attachmentsView = attachmentsView;
	},
	link : function()	{
		var record = this.grid.getSelectionModel().getSelected();
		
		this.getEl().mask();

		if(record.data.entity == "Folder") {
			if(GO.util.empty(this.attachmentItem)){
				this.saveAllToItem(record, record.data.entityId);
			} else {
				this.saveToItem(record, record.data.entityId);
			}

			return;
		}

		GO.request({
			url:'files/folder/checkModelFolder',
			params:{								
				mustExist:true,
				model:record.data.entity,
				id:record.data.entityId
			},
			success:function(response, options, result){
				
				if(GO.util.empty(this.attachmentItem)){
					if(this.attachmentHandle) {
						this.attachmentHandle(result);
						this.hide();
					} else {
						this.saveAllToItem(record, result.files_folder_id);
					}
					
				} else {
					this.saveToItem(record, result.files_folder_id);
				}
			},
			scope:this
		});
	},
	
	show : function(attachmentItem,messagePanel){
		this.attachmentItem = attachmentItem;
		this.messagePanel = messagePanel;
		GO.email.LinkAttachmentDialog.superclass.show.call(this);
	},

	saveToItem : function(record,files_folder_id){
		if(!GO.files.saveAsDialog){
			GO.files.saveAsDialog = new GO.files.SaveAsDialog();
		}
		
		this.getEl().unmask();

		GO.files.saveAsDialog.show({
			folder_id : files_folder_id,
			filename: this.attachmentItem.name,
			handler:function(dialog, folder_id, filename){

				GO.request({
					maskEl:dialog.el,
					url: 'email/message/saveAttachment',
					params:{
						uid: this.messagePanel.uid,
						mailbox: this.messagePanel.mailbox,
						number: this.attachmentItem.number,
						encoding: this.attachmentItem.encoding,
						type: this.attachmentItem.type,
						subtype: this.attachmentItem.subtype,
						account_id: this.messagePanel.account_id,
						uuencoded_partnumber: this.attachmentItem.uuencoded_partnumber,
						folder_id: folder_id,
						filename: filename,
						charset:this.attachmentItem.charset,
						sender:this.messagePanel.data.sender,
						tmp_file: this.attachmentItem.tmp_file ? this.attachmentItem.tmp_file : 0,
						filepath:this.messagePanel.data.path//smime message are cached on disk
					},
					success: function(options, response, result)
					{
						
						dialog.hide();
						this.close();
					},
					scope:this
				});
			},
			scope:this
		});
	},
	
	saveAllToItem: function(record,files_folder_id){

		GO.request({
			url: 'email/message/saveAllAttachments',
			params:{
				uid: this.messagePanel.uid,
				mailbox: this.messagePanel.mailbox,
				account_id: this.messagePanel.account_id,
				folder_id: files_folder_id,
				filepath: this.messagePanel.data.path
			},
			success: function(options, response, result){
				// Successfully saved all attachments
				this.getEl().unmask();
				this.close();
			},
			scope:this
		});
	}

});
