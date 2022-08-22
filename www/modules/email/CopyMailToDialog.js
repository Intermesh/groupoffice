GO.email.CopyMailToDialog = Ext.extend(GO.Window, {
	
	_selectedEmailMessages : [],
	
	title : t("Select a folder to copy the email to", "email"),
	
	width: 300,
	
	height: 400,
	
	layout: 'fit',

	move: false,
		
	initComponent : function(){	
		
		this.foldersTree = new Ext.tree.TreePanel({
			animate : true,
			border : false,
			autoScroll : true,
			layout:'fit',
			height : 400,
//			autoHeight:true,
			loader : new GO.base.tree.TreeLoader({
				dataUrl : GO.url("email/account/tree"),
				baseParams: {
					permissionLevel: GO.permissionLevels.create
				},
				preloadChildren : true,
				listeners : {
					beforeload : function() {
						this.body.mask(t("Loading..."));
					},
					load : function() {
						this.body.unmask();
					},
					scope : this
				}
			})
		});
		
		// set the root node
		this.rootNode = new Ext.tree.AsyncTreeNode({
			text : t("Root", "email"),
			draggable : false,
			id : 'root',
			expanded : true
		});
		
		this.foldersTree.setRootNode(this.rootNode);

		this.rootNode.on('load', function() {
			this.rootNode.select();

		}, this);
		
//		this.foldersTree.on('checkchange', function(node, checked) {
//		
//			var route = checked ? 'email/portlet/enablePortletFolder' : 'email/portlet/disablePortletFolder';
//
//			GO.request({
//				maskEl:this.body,
//				url : route,
//				params : {
//					account_id : node.attributes.account_id,
//					mailbox : node.attributes.mailbox
//				},
//				fail: function(response, options, result) {
//						Ext.Msg.alert(t("Error"), result.feedback);
//					this.foldersTree.getRootNode().reload();
//				},
//				scope : this
//			});
//
//		}, this);
		
//		this.foldersTree.on('dblclick',function(node,event){
//			this._copyMail(node.attributes.account_id,node.attributes.mailbox);
//		},this);

		this.buttons = [new Ext.Button({
			text : t("Ok"),
			handler : function() {
				var node = this.foldersTree.getSelectionModel().getSelectedNode();
				if (!GO.util.empty(node))
				this._copyMail(node.attributes.account_id,node.attributes.mailbox);
				this.hide();
			},
			scope : this
		}),new Ext.Button({
			text : t("Cancel"),
			handler : function() {
				this.hide();
			},
			scope : this
		}),new Ext.Button({
			text : t("Refresh"),
			handler : function() {
				this.rootNode.reload();
			},
			scope : this
		})];

		GO.email.CopyMailToDialog.superclass.initComponent.call(this);
		
		this.add(this.foldersTree);
		
		this.addEvents({
			'copy_email' : true
		});
		
	},
	
	show : function(selectedEmailMessages) {
		this._selectedEmailMessages = selectedEmailMessages;
		GO.email.CopyMailToDialog.superclass.show.call(this);
		
	},
	
	_copyMail : function(targetAccountId,targetMailboxPath) {
		
		if (GO.util.empty(targetAccountId) || GO.util.empty(targetMailboxPath)) {
			Ext.MessageBox.alert(t("Error"),t("Please select a valid email folder to copy to.", "email"));
		} else {
//			if (this._selectedEmailMessages.length>1){
//				var messageStr = t("Are you sure you want to copy the selected email messages to this mailbox folder?", "email");
//			}else{
//				var messageStr = t("Are you sure you want to copy the selected email message to this mailbox folder?", "email");
//			}

//			Ext.Msg.show({
//				title: t("Copy email to...", "email"),
//				msg: messageStr,
//				buttons: Ext.Msg.YESNO,
//				fn: function(btn) {
//					if (btn=='yes') {
				var srcMessages = [];
				for (var i=0; i<this._selectedEmailMessages.length;i++) {				
					srcMessages.push({
						accountId : this._selectedEmailMessages[i].data.account_id,
						mailboxPath : this._selectedEmailMessages[i].data.mailbox,
						mailUid : this._selectedEmailMessages[i].data.uid,
						seen : this._selectedEmailMessages[i].data.seen
					});
				}
				GO.request({
					url : "email/account/copyMailTo",
					params : {
						move: this.move ? 1 : 0,
						'srcMessages' : Ext.encode(srcMessages),
						'targetAccountId' : targetAccountId,
						'targetMailboxPath' : targetMailboxPath
					},
					success:function(options, response, result){
						this.fireEvent('copy_email');
						Ext.Msg.hide();
						this.hide();
					},
					scope:this
				});
			}
				
//				},
//				scope : this
//			});
	}
	
});
