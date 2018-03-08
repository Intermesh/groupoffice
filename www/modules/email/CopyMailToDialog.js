GO.email.CopyMailToDialog = Ext.extend(GO.Window, {
	
	_selectedEmailMessages : [],
	
	title : GO.email.lang['copyMailToTxt'],
	
	width: 300,
	
	height: 400,
	
	layout: 'fit',
		
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
					permissionLevel: GO.permissionLevels.write
				},
				preloadChildren : true,
				listeners : {
					beforeload : function() {
						this.body.mask(GO.lang.waitMsgLoad);
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
			text : GO.email.lang.root,
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
//						Ext.Msg.alert(GO.lang.strError, result.feedback);
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
			text : GO.lang['cmdOk'],
			handler : function() {
				var node = this.foldersTree.getSelectionModel().getSelectedNode();
				if (!GO.util.empty(node))
				this._copyMail(node.attributes.account_id,node.attributes.mailbox);
				this.hide();
			},
			scope : this
		}),new Ext.Button({
			text : GO.lang['cmdCancel'],
			handler : function() {
				this.hide();
			},
			scope : this
		}),new Ext.Button({
			text : GO.lang['cmdRefresh'],
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
			Ext.MessageBox.alert(GO.lang['strError'],GO.email.lang['selectValidMailFolder']);
		} else {
//			if (this._selectedEmailMessages.length>1){
//				var messageStr = GO.email.lang['copyMailsToRUSure'];
//			}else{
//				var messageStr = GO.email.lang['copyMailToRUSure'];
//			}

//			Ext.Msg.show({
//				title: GO.email.lang['copyMailTo'],
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