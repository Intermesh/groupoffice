GO.email.MailboxContextMenu = Ext.extend(Ext.menu.Menu,{
	
	hasAcl : function(node){
		
		var inboxNode = this.treePanel.findInboxNode(node);
		if(!inboxNode)
			return false;
		else
			return inboxNode.attributes.acl_supported;
	},
	
	setNode : function(node){
		this.addFolderButton.setDisabled(node.attributes.noinferiors);
		
		this.shareBtn.setVisible(this.hasAcl(node));

	//		if (GO.settings.modules.email.write_permission) {
	//			var node_id_type = node.attributes.id.substring(0,6);
	//			this.items.get(5).setDisabled(node_id_type!='folder');
	//		}
	},
	initComponent : function(){
		
		this.items= [
		this.addFolderButton = new Ext.menu.Item({
			iconCls: 'btn-add',
			text: t("Add folder", "email"),
			handler: function(){
				Ext.MessageBox.prompt(t("Name"), t("Enter the folder name:", "email"), function(button, text){
					if(button=='ok')
					{
						var sm = this.treePanel.getSelectionModel();
						var node = sm.getSelectedNode();
				
						GO.request({
							url: "email/folder/create",
							maskEl: GO.mainLayout.getModulePanel("email").getEl(),
							params: {
								parent: node.attributes.mailbox,
								account_id: node.attributes.account_id,
								name: text
							},
							success: function(options, response, result)
							{								
								this.treePanel.refresh(node);
							},
							fail : function(response, options, result){
								Ext.Msg.alert(t("Error"), result.feedback);
								this.treePanel.refresh();
							},
							scope: this
						});
					}
				}, this);
			},
			scope:this
		}),
		this.renameFolderButton = new Ext.menu.Item({
			iconCls: 'btn-edit',
			text: t("Rename folder", "email"),
			handler: function()
			{
				var sm = this.treePanel.getSelectionModel();
				var node = sm.getSelectedNode();

				if(!node || !node.attributes.mailbox)
				{
					Ext.MessageBox.alert(t("Error"), t("Select a folder to rename please", "email"));
				}else if(node.attributes.mailbox=='INBOX')
				{
					Ext.MessageBox.alert(t("Error"), t("You can't rename the INBOX folder", "email"));
				}else
				{
					Ext.MessageBox.prompt(t("Name"), t("Enter the folder name:", "email"), function(button, text){
						if(button=='ok')
						{
							var sm = this.treePanel.getSelectionModel();
							var node = sm.getSelectedNode();

							GO.request({								
								maskEl: Ext.getBody(),
								url: "email/folder/rename",
								params: {
									account_id: node.attributes.account_id,
									mailbox: node.attributes.mailbox,
									name: text
								},
								success: function(options, response, result)
								{
									this.treePanel.refresh(node.parentNode);
									
										//remove preloaded children otherwise it won't request the server
//										delete node.parentNode.attributes.children;
//
//										var updateFolderName = function(){
//											var node = this.treePanel.getNodeById('folder_'+this.folder_id);
//											if(node){
//												if(this.folder_id==node.attributes.folder_id){
//													this.mailbox = node.attributes.mailbox;
//													this.treePanel.getSelectionModel().select(node);
//												}
//											}
//											this.el.unmask();
//										}
//										node.parentNode.reload(updateFolderName.createDelegate(this));
										
									
								},
								fail : function(response, options, result){
									Ext.Msg.alert(t("Error"), result.feedback);
									this.treePanel.refresh();
								},
								scope: this
							});
						}
					}, this, false, node.attributes.name);
				}
			},
			scope:this
		}),'-',	new Ext.menu.Item({
//			iconCls: 'btn-delete',
			text:t("Mark as read", "email"),
			cls: 'x-btn-text-icon',
			scope:this,
			handler: function()
			{
				var sm = this.treePanel.getSelectionModel();
				var node = sm.getSelectedNode();

				var tpl = new Ext.Template(t("Are you sure you want to mark all messages in folder '{name}' as read?", "email"));

				Ext.MessageBox.confirm(t("Confirm"), tpl.applyTemplate({name:node.attributes.name}), function(btn){
					if(btn=='yes')
					{
						GO.request({
							maskEl:GO.mainLayout.getModulePanel("email").getEl(),
							url: "email/folder/markAsRead",
							params:{
								account_id: node.attributes.account_id,
								mailbox: node.attributes.mailbox
							},
							success:function(){
								if(node.attributes.mailbox==GO.mainLayout.getModulePanel("email").messagesGrid.store.baseParams.mailbox)
								{
									GO.mainLayout.getModulePanel("email").messagesGrid.store.load();
								}
							},
							scope: this
						});
					}
				}, this);
			}
		}),	new Ext.menu.Item({
			iconCls: 'btn-cut',
			text:t("Move old mails", "email"),
			cls: 'x-btn-text-icon',
			scope:this,
			handler: function()
			{
				if (typeof(this.moveOldMailDialog)=='undefined') {
					this.moveOldMailDialog = new GO.email.MoveOldMailDialog();
				}
				this.moveOldMailDialog.setNode(this.treePanel.getSelectionModel().getSelectedNode());
				this.moveOldMailDialog.show();
			}
		}),this.emptyFolderButton = new Ext.menu.Item({
			iconCls: 'btn-delete',
			text: t("Empty folder", "email"),
			handler: function(){

				var sm = this.treePanel.getSelectionModel();
				var node = sm.getSelectedNode();

				var template = new Ext.Template(t("Are you sure you want to EMPTY '{name}'?", "email"));

				Ext.MessageBox.confirm(t("Confirm"), template.applyTemplate({name:node.attributes.name}), function(btn){
					if(btn=='yes')
					{
						GO.request({
							maskEl:GO.mainLayout.getModulePanel("email").getEl(),
							url: "email/folder/truncate",
							params:{
								account_id: node.attributes.account_id,
								mailbox: node.attributes.mailbox
							},
							success:function(){
								if(node.attributes.mailbox==GO.mainLayout.getModulePanel("email").messagesGrid.store.baseParams.mailbox)
								{
									GO.mainLayout.getModulePanel("email").messagesGrid.store.removeAll();
									GO.mainLayout.getModulePanel("email").messagePanel.reset();
								}
								GO.mainLayout.getModulePanel("email").updateFolderStatus(node.attributes.mailbox, 0);
								this.treePanel.mainPanel.refresh(true);
//								GO.mainLayout.getModulePanel("email").updateNotificationEl();
							},
							scope: this
						});
					}
				}, this);
			},
			scope:this
		}),this.deleteFolderButton = new Ext.menu.Item({
			iconCls: 'btn-delete',
			text: t("Delete"),
			cls: 'x-btn-text-icon',
			scope: this,
			handler: function(){
				var sm = this.treePanel.getSelectionModel();
				var node = sm.getSelectedNode();
				var protected_mbs = ["INBOX", "Trash", "Sent"];
				if(!node|| node.attributes.folder_id<1) {
					Ext.MessageBox.alert(t("Error"), t("Select a folder to delete please", "email"));
				} else if(protected_mbs.indexOf(node.attributes.mailbox) > -1) {
					Ext.MessageBox.alert(t("Error"), t("You can't delete the trash, sent items or drafts folder", "email"));
				} else {
					var trashNode = this.treePanel.findMailboxByName(node, 'Trash'), trashable = true, alreadyTrashed = false;
					if(trashNode && trashNode.attributes.noinferiors === true) {
						trashable = false;
					} else if(node.attributes.mailbox.indexOf("Trash/") === 0 ) { // .startsWith does not work with IE. Fools.
						alreadyTrashed = true;
					}
					
					GO.deleteItems({
						maskEl: GO.mainLayout.getModulePanel("email").getEl(),
						url: GO.url("email/folder/delete"),
						params: {					
							account_id:node.attributes.account_id,
							mailbox: node.attributes.mailbox,
							trashable: (trashable ? 1 : 0)
						},
						noConfirmation: (trashable && !alreadyTrashed),
						callback: function(responseParams)
						{
							
							if(responseParams.success)
							{
								node.remove();

								if(node.attributes.mailbox==GO.mainLayout.getModulePanel("email").messagesGrid.store.baseParams.mailbox){
									GO.mainLayout.getModulePanel("email").messagesGrid.store.removeAll();
								}

								if(go.Modules.isAvailable("legacy", "emailportlet")){
									GO.emailportlet.foldersStore.load();
								}
								this.treePanel.mainPanel.refresh(true);
							}
						},
						count: 1,
						scope: this
					});
				}
			}
		}),'-',this.shareBtn = new Ext.menu.Item({
			iconCls:'ic-share',
			text: t("Share", "email"),
			handler:function(){
				if(!this.imapAclDialog)
					this.imapAclDialog = new GO.email.ImapAclDialog();

				var sm = this.treePanel.getSelectionModel();
				var node = sm.getSelectedNode();

				this.imapAclDialog.setParams(node.attributes.account_id,node.attributes.mailbox, node.text);
				this.imapAclDialog.show();
			},
			scope:this

		}),{
			iconCls : 'btn-settings',
			text : t("Subscribe to folders", "email"),
			scope : this,
			handler : function() {
				if (!this.foldersDialog) {
					this.foldersDialog = new GO.email.FoldersDialog();
				}
				var sm = this.treePanel.getSelectionModel();
				var node = sm.getSelectedNode();
				this.foldersDialog.show(node.attributes.account_id);
			}
		},'-', this.propertiesBtn = new Ext.menu.Item({
			iconCls: 'btn-edit',
			text: t("Properties"),
			handler:function(a,b){
				var sm = this.treePanel.getSelectionModel();
				var node = sm.getSelectedNode();
				
				if (!GO.email.folderDialog)
					GO.email.folderDialog = new GO.email.FolderDialog();
				GO.email.folderDialog.show(node.attributes.account_id,{mailboxPath:node.attributes.mailbox});
			},
			scope:this
		})];

	
		for(var i=0;i<GO.email.extraTreeContextMenuItems.length;i++)
		{
			this.items.push(GO.email.extraTreeContextMenuItems[i]);
		}
		
		GO.email.MailboxContextMenu.superclass.initComponent.call(this);
		

	}
}
);
