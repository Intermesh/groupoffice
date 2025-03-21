/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * @version $Id: EmailClient.js 22437 2018-03-01 07:55:17Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */


Ext.namespace("GO.email");

//placeholder overriden in calendar
GO.email.handleITIP = function(dom, msg) {};

GO.email.EmailClient = Ext.extend(Ext.Panel, {

	initComponent : function() {

		this.messagesStore = new GO.data.GroupingStore({
			url: GO.url("email/message/store"),
			root: 'results',
			totalProperty: 'total',
			remoteSort: true,
			suppressError: true,
			reader: new Ext.data.JsonReader({
				root: 'results',
				totalProperty: 'total',
				fields:['uid','icon','deleted','flagged','labels','has_attachments','seen','subject','from','to','sender','size','udate','internal_udate', 'x_priority','answered','forwarded','account_id','mailbox','mailboxname'],
				id: 'uid'
			}),
			sortInfo: {
				field: 'internal_udate',
				direction: 'DESC'
			},
			groupField: 'internal_udate'
		});

		this.messagesStore.on('load', function(){

			this.isManager = this.messagesGrid.store.reader.jsonData.permission_level == GO.permissionLevels.manage;

			this.readOnly = this.messagesGrid.store.reader.jsonData.permission_level < GO.permissionLevels.create || this.messagesGrid.store.reader.multipleFolders;
			this._permissionDelegated = this.messagesGrid.store.reader.jsonData.permission_level == GO.email.permissionLevels.delegated;

			this.permissionLevel = this.messagesGrid.store.reader.jsonData.permission_level;
			
			this.messagesGrid.deleteButton.setDisabled(this.readOnly);
		}, this);
		this.messagesStore.on('exception',
			function( store, type, action, options, response){
				if(response.isTimeout || response.status == 0){
					console.warn("Connection timeout", response, options);
					if(document.visibilityState === "visible") {
						GO.errorDialog.show(t("The connection to the server timed out. Please check your internet connection."), t("Request error"));
					}
				} else if(!options.reader.jsonData || GO.jsonAuthHandler(options.reader.jsonData, this.load, this)) {
					let msg;

					if (!GO.errorDialog.isVisible()) {
						if(options.reader.jsonData && options.reader.jsonData.feedback) {
							const feedback = options.reader.jsonData.feedback;

							if(feedback.toLowerCase().indexOf("oauth2") > -1) {
								Ext.MessageBox.alert(t("Refresh token"),
									t("Your token has possibly expired. A new window will be opened in which you can renew your token.", "email"),
									() => {
										window.open(window.location.pathname + 'go/modules/community/oauth2client/gauth.php/authenticate/' + this.account_id, 'do_da_auth_thingy');
									}
									,this);
								// we done!
								return;
							}
							msg = feedback;
							GO.errorDialog.show(msg);
						} else if (!response.isAbort) {
							msg = t("An error occurred on the webserver. Contact your system administrator and supply the detailed error.");
							msg += '<br /><br />JsonStore load exception occurred';
							GO.errorDialog.show(msg);
						}
					}
				} else {
					console.error(response);

					GO.errorDialog.show(t("Failed to send the request to the server. Please check your internet connection."));
				}
			}
			, this
		);

	var messagesAtTop = Ext.state.Manager.get('em-msgs-top');
	if(messagesAtTop) {
		messagesAtTop = Ext.decode(messagesAtTop);
	} else {
		messagesAtTop = screen.width < 1024;
	}

	var deleteConfig = {
		callback:function(){
			if(this.messagePanel.uid && !this.messagesGrid.store.getById(this.messagePanel.uid))
			{
				this.messagePanel.reset();
			}
		},
		scope: this
	};

	this.leftMessagesGrid = new GO.email.MessagesGrid({
		id:'em-pnl-west',
		emailClient:this,
		store:this.messagesStore,
		region:'center',
		hidden:messagesAtTop,
		deleteConfig : deleteConfig,
		header:false,
		split:true,
		minWidth: dp(300)
	});
	this.addGridHandlers(this.leftMessagesGrid);

	this.messagesGrid=this.leftMessagesGrid;

	//for global access by composers
	GO.email.messagesGrid = this.messagesGrid;

	this.messagesGrid.store.on('load',function(){

		var header = this.messagesGrid.store.reader.jsonData.sent || this.messagesGrid.store.reader.jsonData.drafts ? t("To", "email") : t("From", "email");
		var header2 = this.messagesGrid.store.reader.jsonData.sent || this.messagesGrid.store.reader.jsonData.drafts ? t("From", "email") : t("To", "email");

		var unseen = this.messagesGrid.store.reader.jsonData.unseen;
		for(var mailbox in unseen) {
			this.updateFolderStatus(mailbox, unseen[mailbox]);
		}
		//don't confirm delete to trashfolder
		this.messagesGrid.deleteConfig.noConfirmation=!this.messagesGrid.store.reader.jsonData.deleteConfirm;
	}, this);
	
	this.messagesGrid.store.on("beforeload", function(store) {	
		this.getView().holdPosition = true;
		if(store.baseParams['search'] != undefined) {
			GO.email.search_query = store.baseParams['search'];
			this.searchDialog.hasSearch = false;
			delete(store.baseParams['search']);
		} else if(this.searchDialog.hasSearch) {
			this.resetSearch();
			this.searchField.triggerField.setValue('[' + t("Advanced", "email") + ']');
		}
		if(GO.email.search_query) {
			this.searchDialog.hasSearch = false;
			var search_type = (GO.email.search_type)
			? GO.email.search_type : GO.email.search_type_default;

			var query;

			if(search_type == 'any' || search_type == 'fts'){
				// if the server does not support FTS it will be converted into a search for subject, from, to, cc
				query = 'TEXT "' + GO.email.search_query + '"';
			} else {
				query = search_type.toUpperCase() + ' "' + GO.email.search_query + '"';
			}
			store.baseParams['query'] = query;

			if(GO.email.search_in) {
				store.baseParams.searchIn = GO.email.search_in;
			}
			
		} else if(!this.searchDialog.hasSearch && store.baseParams['query']) {
			this.resetSearch();
			delete(store.baseParams['query']);
			delete(store.baseParams['searchIn']);
		}

	}, this.messagesGrid);

	GO.email.saveAsItems = GO.email.saveAsItems || [];

	for(var i = 0; i < GO.email.saveAsItems.length; i++) {
		GO.email.saveAsItems[i].scope=this;
	}


	this.gridContextMenu = new GO.email.MessageContextMenu({
		main: this,
		grid: this.messagesGrid
	});

	this.gridReadOnlyContextMenu = new GO.menu.RecordsContextMenu({
		shadow: "frame",
		minWidth: 180,
		items: [
		  new Ext.menu.Item ({
		  text: t("View source", "email"),
		  handler: function(){

			  var record = this.messagesGrid.selModel.getSelected();
			  if(record) {
				  var win = window.open(GO.url("email/message/source",{account_id:this.account_id,mailbox:record.data.mailbox,uid:record.data.uid}));
				  win.focus();
			  }

		  },
		  scope: this
	  }),
		  '-',
		 new Ext.menu.Item ({
		  iconCls: 'ic-content-copy',
		  text: t("Copy email to...", "email"),
		  handler: function(a,b,c){
			  var selectedEmails = this.messagesGrid.getSelectionModel().getSelections();
			  this.showCopyMailToDialog(selectedEmails);
		  },
		  scope: this,
		  multiple:true
	  })
		]
	});

	GO.email.treePanel = this.treePanel = new GO.email.AccountsTree({
		id:'email-tree-panel',
		region:'west',
		cls: 'go-sidenav',
		mainPanel:this
	});

	this.treePanel.getRootNode().on('beforeload', () => {
		this.messagesGrid.btnRefresh.setDisabled(true);
	}, this);
	//select the first inbox to be displayed in the messages grid
	this.treePanel.getRootNode().on('load', function(rootNode) {

		this.body.unmask();
		//restore already selected account
		let accountNode;
		if(this.account_id) {
			const accountNodeId = btoa('account_' + this.account_id);
			accountNode = this.treePanel.getNodeById(accountNodeId);
		}
		if(!accountNode) {
			// fallback on first
			for(var i=0;i<rootNode.childNodes.length;i++){
				let child = rootNode.childNodes[i];
				if(child.expanded) {
					accountNode = child;
					break;
				}
			}
		}

		if(!accountNode) {
			this.messagesStore.removeAll();
			this.messagesGrid.btnRefresh.setDisabled(false);
			return; //no accounts
		}

		accountNode.on("load", function() {

			let mailboxNode;
			//restore already selected mailbox
			if(this.mailbox) {
				const mailboxNodeId = btoa('f_' + this.account_id + "_" + this.mailbox);
				mailboxNode = this.treePanel.getNodeById(mailboxNodeId);
			}

			if(!mailboxNode) {
				mailboxNode = accountNode.childNodes[0];
			}

			if(mailboxNode) {
				// mailboxNode.on('load', function(){
				setTimeout(() => {
					//don't know why but it doesn't work without a setTimeout
					this.treePanel.getSelectionModel().select(mailboxNode);

					if(this.treeScrollTop) {
						//restore scroll position after refresh, dirty hack with 100ms delay to allow sub nodes to expand
						setTimeout(() => {
							this.treePanel.body.dom.scrollTop = this.treeScrollTop;
							delete this.treeScrollTop;
						}, 100);
					}
				}, 0)

				// },this, {single: true, defer: 10});
			} else {
				this.messagesStore.removeAll();
			}

			this.messagesGrid.btnRefresh.setDisabled(false);

		}, this, {single: true});

	}, this);


	this.treePanel.getSelectionModel().on('selectionchange', function(sm, node)	{
		if(!node){
			return;
		}
		let usage = '';

		let inboxNode = this.treePanel.findInboxNode(node);
		if(inboxNode) {
			usage=inboxNode.attributes.usage;
		}
		let mbname = node.attributes.mailbox;
		if(Ext.isEmpty(mbname)) {
			mbname = 'INBOX';
		}
		this.setAccount(
			node.attributes.account_id,
			mbname,
			usage
		);
	}, this);

	this.treePanel.on('click',function(node){
		var selectedNode = this.treePanel.getSelectionModel().getSelectedNode();

		if(selectedNode && node.id==selectedNode.id) {
			var inboxNode =this.treePanel.findInboxNode(node),
				usage = inboxNode ? inboxNode.attributes.usage : '';
			this.setAccount(
				node.attributes.account_id,
				node.attributes.mailbox,
				usage
			);
		}
		this.messagesGrid.show();
	}, this);

	this.layout='responsive';
	this.layoutConfig = {
			triggerWidth: 1000
		};

	this.messageTbar = new Ext.Toolbar({
		enableOverflow: true,
		items:[{
			cls: 'go-narrow',
			iconCls: "ic-arrow-back",
			handler: function () {
				this.westPanel.show();
			},
			scope: this
		},
			this.editButton=new Ext.Button({
				hidden:true,
				iconCls: 'ic-edit',
				text: GO.util.isMobileOrTablet() ? "" : t("Edit", "email"),
				handler: function(){
					GO.email.showComposer({
						uid: this.messagePanel.uid,
						task: 'opendraft',
						template_id: 0,
						mailbox: this.messagePanel.mailbox,
						account_id: this.account_id
					});
				},
				scope: this
			}),
			this.replyButton=new Ext.Button({
				disabled:true,
				iconCls: 'ic-reply',
				text: GO.util.isMobileOrTablet() ? "" : t("Reply", "email"),
				handler: function(){
					var comp = null;
					if (!this._permissionDelegated) {
						comp =GO.email.showComposer({
							uid: this.messagePanel.uid,
							task: 'reply',
							mailbox: this.messagePanel.mailbox,
							account_id: this.account_id
						});
					} else {
						comp = GO.email.showComposer({
							uid: this.messagePanel.uid,
							task: 'reply',
							mailbox: this.messagePanel.mailbox,
							account_id: this.account_id,
							delegated_cc_enabled: true
						});
					}

					this.messagePanel.data.links.forEach(function(link) {
						comp.createLinkButton.addLink(link.entity, link.entityId);
					});
				},
				scope: this
			}),this.replyAllButton=new Ext.Button({
				disabled:true,
				iconCls: 'ic-reply-all',
				text: GO.util.isMobileOrTablet() ? "" : t("Reply all", "email"),
				handler: function(){
					var comp = GO.email.showComposer({
						uid: this.messagePanel.uid,
						task: 'reply_all',
						mailbox: this.messagePanel.mailbox,
						account_id: this.account_id
					});

					this.messagePanel.data.links.forEach(function(link) {
						comp.createLinkButton.addLink(link.entity, link.entityId);
					});
				},
				scope: this
			}),

			this.forwardButton=new Ext.Button({
				disabled:'true',
				iconCls: 'ic-forward',
				text: GO.util.isMobileOrTablet() ? "" : t("Forward", "email"),
				handler: function(){
					var comp;
					if (!this._permissionDelegated) {
						comp = GO.email.showComposer({
							uid: this.messagePanel.uid,
							task: 'forward',
							mailbox: this.messagePanel.mailbox,
							account_id: this.account_id
						});
					} else {
						comp = GO.email.showComposer({
							uid: this.messagePanel.uid,
							task: 'forward',
							mailbox: this.messagePanel.mailbox,
							account_id: this.account_id,
							delegated_cc_enabled: true
						});
					}
					this.messagePanel.data.links.forEach(function(link) {
						comp.createLinkButton.addLink(link.entity, link.entityId);
					});
				},
				scope: this
			}),'->',
			// this.printButton = new Ext.Button({
			// 	disabled: true,
			// 	iconCls: 'ic-print',
			// 	tooltip: t("Print"),
			// 	overflowText: t("Print"),
			// 	handler: function(){
			// 		this.messagePanel.print();
			// 	},
			// 	scope: this
			// }),
			{
				iconCls: 'ic-more-vert',
				menu: this.gridContextMenu
			}
		]});

	if(!GO.util.isMobileOrTablet()) {
		this.messageTbar.insert(-2,{
			hidden: !GO.email.saveAsItems || !GO.email.saveAsItems.length,
			iconCls: 'ic-save',
			text:t("Save as"),
			menu:this.gridContextMenu.saveAsMenu
		});
	}

		this.messageTbar.insert(-1, {
			xtype:'button',
			iconCls:'ic-delete',
			overflowText:t('Delete'),
			handler: (btn) => {
				this.messagesGrid.deleteSelected();
				this.westPanel.show();
			}
		});

	this.messagePanel = new GO.email.MessagePanel({
		id:'email-message-panel',
		tbar: this.messageTbar,
		region:'center',
		autoScroll:true,
		titlebar: false,
		attachmentContextMenu: new GO.email.AttachmentContextMenu()
	});
	
	this.westPanel = new Ext.Panel({
		region:"west",
		layout:'responsive',

		split: true,
		narrowWidth: dp(400), //this will only work for panels inside another panel with layout=responsive. Not ideal but at the moment the only way I could make it work
		width: dp(700),
		minWidth: dp(600),
		narrowMinWidth: dp(300),
		stateId: 'go-email-west',
		items: [			
			this.leftMessagesGrid,
			this.treePanel
		]
	})

	this.items=[
		this.westPanel,
		this.messagePanel
	];
	

	this.messagePanel.on('load', function(options, success, response, data, password){
		if(!success)
		{
			this.messagePanel.uid=0;
		}else
		{
			this.messageTbar.setDisabled(false);

			this.messagePanel.do_not_mark_as_read = 0;
			if(!GO.util.empty(data.do_not_mark_as_read)) {
				this.messagePanel.do_not_mark_as_read = data.do_not_mark_as_read;
			}
			this.editButton.setVisible(data.isDraft);

			this.replyAllButton.setVisible(!data.isDraft);
			this.replyButton.setVisible(!data.isDraft);

			this.replyAllButton.setDisabled(this.readOnly && !this._permissionDelegated);
			this.replyButton.setDisabled(this.readOnly && !this._permissionDelegated);

			var record = this.messagesGrid.store.getById(this.messagePanel.uid);

			if(record && !record.data.seen && data.notification) {
				if(GO.email.alwaysRespondToNotifications || confirm(t("The sender of this messages likes a read notification by you at: %s. Do you want to send a read notification now?", "email").replace('%s', data.notification))) {
					GO.request({
						url: "email/message/notification",
						params: {
							account_id: this.messagePanel.account_id,
							message_to:data.to_string,
							notification_to: data.notification,
							subject: data.subject
						}
					});
				}
			}
		}

	}, this);

	this.messagePanel.on('reset', function(){
		this.messageTbar.setDisabled(true);
	}, this);

	this.messagePanel.on('attachmentClicked', GO.email.openAttachment, this);

	/**
     * for email seapching on sender from message panel
    */
	GO.email.searchSender=function(sender) {
		if(this.rendered) {
			GO.email.search_type = 'from';
			this.messagesGrid.showUnreadButton.toggle(false, true);
			this.messagesGrid.store.baseParams['search'] = sender;
			GO.email.messagesGrid.store.baseParams['unread']=0;
			this.messagesGrid.setSearchFields('from', sender);

			this.messagesGrid.searchField.search();

			this.messagesGrid.store.load({
				params:{
					start:0
				}
			});

			if(GO.mainLayout.tabPanel) {
				GO.mainLayout.tabPanel.setActiveTab(this.id);
			}
		} else {
			alert(t("To use this function you must load your e-mail first by pressing the e-mail tab", "email"));
		}
	}
	GO.email.searchSender = GO.email.searchSender.createDelegate(this);

	GO.email.EmailClient.superclass.initComponent.call(this);
	
	GO.email.emailClient = this;
	},

	_permissionDelegated : false,



	print: function() {
		this.messagePanel.print();
	},


	addGridHandlers : function(grid)
	{
		grid.on("rowcontextmenu", function(grid, rowIndex, e) {
			e.stopEvent();
			this.rowClicked=true;
			var sm = grid.getSelectionModel();
			if(sm.isSelected(rowIndex) !== true) {
				sm.clearSelections();
				sm.selectRow(rowIndex);
			}

			var coords = e.getXY();

			var selectedMailboxFolder = this.treePanel.getSelectionModel().getSelectedNode();
			// show the labels context menu when
			this.gridContextMenu.contextMenuLabels.setVisible(selectedMailboxFolder.attributes.permittedFlags || selectedMailboxFolder.attributes.isAccount);

			if(this.messagesGrid.store.reader.jsonData.permission_level <= GO.permissionLevels.read || this.messagesGrid.store.reader.jsonData.multipleFolders)
			  this.gridReadOnlyContextMenu.showAt([coords[0], coords[1]], grid.getSelectionModel().getSelections());
			else
			  this.gridContextMenu.showAt([coords[0], coords[1]]);
		},this);

		grid.on('collapse', function(){
			this.closeMessageButton.setVisible(true);
		}, this);

		grid.on('expand', function(){
			this.closeMessageButton.setVisible(false);
		}, this);

		grid.on("rowdblclick", function(){
			if(this.messagesGrid.store.reader.jsonData.drafts || this.messagesGrid.store.reader.jsonData.sent) {
				GO.email.showComposer({
					uid: this.messagePanel.uid,
					task: 'opendraft',
					template_id: 0,
					mailbox: this.mailbox,
					account_id: this.account_id
				});
			} else {
				this.messagePanel.popup();
			}
		}, this);

		grid.on('navigate', function (sm, rowIndex, r) {
			if(r.data['uid']!=this.messagePanel.uid)
			{
				this.messagePanel.loadMessage(r.data.uid, r.data['mailbox'], this.account_id);
				this.messagePanel.show();
				if(!r.data.seen && this.messagesGrid.store.reader.jsonData.permission_level > GO.permissionLevels.read){
					//set read with 2 sec delay.
					this.markAsRead.defer(2000, this, [r.data.uid, r.data['mailbox'], this.account_id]);
				}
			}
			this.messagePanel.show();
		},this)
	},

	markAsRead : function(uid, mailbox, account_id){
		if(this.messagePanel.uid==uid && this.messagePanel.mailbox==mailbox && this.messagePanel.account_id==account_id && !this.messagePanel.do_not_mark_as_read){
				GO.request({
				url: "email/message/setFlag",
				params: {
					account_id: account_id,
					mailbox: mailbox,
					flag: "Seen",
					clear: 0,
					messages: Ext.encode([uid])
				},
				success: function(options, response,result)
				{
					var record = this.messagesGrid.store.getById(uid);
					if(record){
						record.set("seen", 1);
						record.commit();
					}

					this.updateFolderStatus(this.mailbox, result.unseen);

				},
				scope:this
			});
		}
	},

	afterRender : function(){
		GO.email.EmailClient.superclass.afterRender.call(this);
		this.body.mask(t("Loading..."));
	},

	onShow : function() {
		GO.email.EmailClient.superclass.onShow.call(this);
	},


	showComposer : function(values) {
		GO.email.showComposer(
		{
			account_id: this.account_id,
			values : values
		});
	},

	setAccount : function(account_id,mailbox, usage)
	{
		const reload = account_id==this.account_id && this.mailbox==mailbox;
		if(!reload)
		{
			this.messagePanel.reset();
			this.messagesGrid.getSelectionModel().clearSelections();
			this.messagesGrid.getView( ).scrollToTop();
		}

		this.messagesGrid.expand();
		
		this.account_id = account_id;
		this.mailbox = mailbox;

		this.messagesGrid.account_id = account_id;
		this.messagesGrid.store.baseParams['task']='messages';
		this.messagesGrid.store.baseParams['account_id']=account_id;
		this.messagesGrid.store.baseParams['mailbox']=mailbox;

		this.messagesGrid.store.load({
			params: {
				start: 0
			}
		});

		this.treePanel.setUsage(usage);
	},

	getFolderNodeId : function (account_id, mailbox){
		return GO.util.Base64.encode("f_"+account_id+"_"+mailbox);
	},

	/**
	 * Returns true if the current folder needs to be refreshed in the grid
	 */
	updateFolderStatus : function(mailbox, unseen, account_id)
	{
		if(!account_id) {
			account_id = this.messagesGrid.store.baseParams.account_id;
		}
		var nodeId = this.getFolderNodeId(account_id, mailbox);
		var statusElId = "status_"+nodeId;
		var statusEl = Ext.get(statusElId);


		if(statusEl && statusEl.dom) {
			var node = this.treePanel.getNodeById(nodeId);

			if(node) {
				if (unseen) {
					node.getUI().addClass('ml-folder-unseen');
				} else {
					node.getUI().removeClass('ml-folder-unseen');
				}
			}

			var statusText = statusEl.dom.innerHTML;
			var current = statusText=='' ? 0 : parseInt(statusText);

			if(current != unseen) {
				if(unseen>0) {
					statusEl.dom.innerHTML = unseen;
				} else {
					statusEl.dom.innerHTML = "";
				}
				return true;
			}
		}
		return false;
	},

	incrementFolderStatus : function(mailbox, increment)
	{
		var statusElId = "status_"+this.getFolderNodeId(this.account_id, mailbox);
		var statusEl = Ext.get(statusElId);

		var statusText = statusEl.dom.innerHTML;

		var status = 0;
		if(statusText!='') {
			status = parseInt(statusText.substring(1, statusText.length-1));
		}
		status+=increment;

		this.updateFolderStatus(mailbox, status);
	},

	refresh : function(refresh) {

		//restore scroll position after refresh in the root node load handler above
		this.treeScrollTop = this.treePanel.body.dom.scrollTop;

		if(refresh) {
			this.treePanel.loader.baseParams.refresh = true;
		}
		this.treePanel.root.reload();

		if(refresh) {
			delete this.treePanel.loader.baseParams.refresh;
		}
	},

	showAccountsDialog : function() {
		if(!this.accountsDialog) {
			this.accountsDialog = new GO.email.AccountsDialog();
			this.accountsDialog.accountsGrid.accountDialog.on('save', function(dialog, result){
				if(result.refreshNeeded){
					this.refresh();
				}
			}, this);

			this.accountsDialog.accountsGrid.on('delete', function(){
				this.refresh();
				if(go.Modules.isAvailable("legacy", "emailportlet"))
					GO.emailportlet.foldersStore.load();
			}, this);
		}
		this.accountsDialog.show();
	},

	showCopyMailToDialog : function(selectedEmailMessages, move) {
		if (!this._copyMailToDialog) {
			this._copyMailToDialog = new GO.email.CopyMailToDialog({
				move: move
			});

			this._copyMailToDialog.on('copy_email',function(){
				this.messagesGrid.store.reload();
			},this);
		}

		this._copyMailToDialog.move = move;

		this._copyMailToDialog.show(selectedEmailMessages);
	},

	flagMessages : function (flag, clear){
		var selectedRows = this.messagesGrid.selModel.selections.keys;

		if(selectedRows.length) {
			GO.request({
				url: "email/message/setFlag",
				maskEl:this.getEl(),
				params: {
					account_id: this.account_id,
					mailbox: this.mailbox,
					flag: flag,
					clear: clear ? 1 : 0,
					messages: Ext.encode(selectedRows)
				},
				success: function(options, response,result) {
					var field;
					var value;
					var records = this.messagesGrid.selModel.getSelections();

					switch(flag) {
						case 'Seen':
							field='seen';
							value=!clear;
							break;
						case 'Flagged':
							field='flagged';
							value=!clear;
							break;
					}


					for(var i=0;i<records.length;i++) {
						records[i].set(field, value);
						records[i].commit();
					}

					this.updateFolderStatus(this.mailbox, result.unseen);
				},
				scope:this
			});

		}
	},

	addSendersTo : function(menuItem){
		var records = this.messagesGrid.getSelectionModel().getSelections();

		var emails=[];
		for(var i=0;i<records.length;i++) {
			emails.push('"'+records[i].get('from')+'" <'+records[i].get('sender')+'>');
		}

		var activeComposer=false;
		if(GO.email.composers) {
			for(var i=GO.email.composers.length-1;i>=0;i--) {
				if(GO.email.composers[i].isVisible()) {
					activeComposer=GO.email.composers[i];
					break;
				}
			}
		}

		if(activeComposer) {
			var f = activeComposer.formPanel.form.findField(menuItem.field);
			var v = f.getValue();
			if(v!='') {
				v+=', ';
			}
			v+=emails.join(', ');
			f.setValue(v);
			activeComposer.focus();
		}else {
			var config={
				values:{}
			}
			config.values[menuItem.field]=emails.join(', ');
			GO.email.showComposer(config);
		}
	},

	addSendersToAddresslist : function(data) {
		const records = this.messagesGrid.getSelectionModel().getSelections();

		let emails=[], from =[];
		for(let i=0; i<records.length; i++) {
			from.push(records[i].get('from'));
			emails.push(records[i].get('sender'));
		}
		const dialog = new GO.email.AddressListDialog({
			email: emails[0],
			from: from[0],
			delete: false,
			title: t("Add to address list", "email")
		});
		dialog.show();
	},

	deleteSendersFromAddresslist : function(addresslistId) {
		var records = this.messagesGrid.getSelectionModel().getSelections();

		var emails=[];
		for(var i=0;i<records.length;i++) {
			emails.push(records[i].get('sender'));
		}

		go.Db.store("Contact").query({
			filter: {
				email: emails[0],
				permissionLevel: go.permissionLevels.write
			},
			limit: 1
		}).then(function(result) {
			// no contact found
			if (!result.ids.length) {
				Ext.MessageBox.alert(t("Error"), t("The contact is unknown","email"));
				return;
			} else {
				var dialog = new GO.email.AddressListDialog({
					email:emails[0],
					delete: true,
					title: t("Remove from address list","email")
				});
				dialog.show();
			}
		});
	}
});

GO.mainLayout.onReady(function(){

	let countEmailShown;

	//contextmenu when an e-mail address is clicked
	GO.email.addressContextMenu=new GO.email.AddressContextMenu();
	GO.email.search_type_default = localStorage && localStorage.email_search_type  ? localStorage.email_search_type : 'any';

	//register a new request to the checker. It will poll unseen tickets every two minutes
	go.Notifier.addStatusIcon('email', 'ic-email');
	GO.checker.registerRequest("email/account/checkUnseen",{},function(checker, data){

		if(!data.email_status) {
			return;
		}

		//go.Notifier.toggleIcon('email',data.email_status.total_unseen > 0);
		GO.mainLayout.setNotification('email',data.email_status.total_unseen,'green');

		if(GO.mainLayout.panelIsVisible('email')) {
			var ep = GO.mainLayout.getModulePanel('email', false);

			if (ep) {
				for (var i = 0; i < data.email_status.unseen.length; i++) {
					var s = data.email_status.unseen[i];
					var changed = ep.updateFolderStatus(s.mailbox, s.unseen, s.account_id);
					if (changed && ep.messagesGrid.store.baseParams.mailbox == s.mailbox && ep.messagesGrid.store.baseParams.account_id == s.account_id) {
						ep.messagesGrid.store.reload({
							keepScrollPosition: true
						});
					}
				}
			}
		}

		if((!data.email_status.has_new && countEmailShown)
			|| data.email_status.total_unseen <= 0
			|| (countEmailShown && countEmailShown >= data.email_status.total_unseen)){

			countEmailShown = data.email_status.total_unseen;
			return;
		}
		countEmailShown = data.email_status.total_unseen;
		var title = t("New email"),
			text = t("You have %d unread email(s)").replace('%d', data.email_status.total_unseen);

		go.Notifier.notify({
			title: title,
			description: text,
			iconCls: 'ic-email',
			icon: 'views/Extjs3/themes/Paper/img/notify/email.png',
			tag: "email"
		}).catch((e) => {
			console.warn("Notification failed: " + e);
		});

		go.Notifier.playSound('message-new-email', 'email');

	});

});

GO.email.aliasesStore = new GO.data.JsonStore({
	url: GO.url("email/alias/store"),
	baseParams:{limit:0},
	fields: ['id','account_id', 'from', 'name','email','html_signature', 'plain_signature','template_id','signature_below_reply'],
	remoteSort: true
});

// Save all attachments of the given email panel to a selected GO folder
GO.email.saveAllAttachments = function(panel){
	
	if(!this.selectFolderDialog){
		
		this.selectFolderDialog = new GO.files.SelectFolderDialog({
			handler:function(fs, path, selectedFolderNode){
				GO.request({
					url: 'email/message/saveAllAttachments',
					params:{
						uid: panel.uid,
						mailbox: panel.mailbox,
						account_id: panel.account_id,
						folder_id: selectedFolderNode.attributes.id,
						filepath:panel.data.path//smime message are cached on disk
					},
					success: function(options, response, result){
						// Successfully saved all attachments
					},
					scope:this
				});
			}
		});
	}

	this.selectFolderDialog.show();
};

GO.email.deleteAllAttachments = function(panel) {
	Ext.MessageBox.confirm(
		t('Delete all attachments', 'email', 'legacy'),
		t('Are you sure that you wish to remove all attachments from this email message?', 'email', 'legacy'),
		function(btn) {
			if(btn === 'yes') {
				GO.request({
					url: 'email/message/deleteAllAttachments',
					params:{
						uid: panel.uid,
						mailbox: panel.mailbox,
						account_id: panel.account_id
					},

					success: function (options, response, result) {
						if(result.uid) {
							panel.loadMessage(result.uid, panel.mailbox, panel.account_id);
							GO.email.emailClient.leftMessagesGrid.store.load();
						}
					},
					failure: function(response,options,result) {
						console.log(response);
						console.log(options);
						console.log(result);
					},
					scope: this
				});
			}
		}, this);
};

GO.email.saveAttachment = function(attachment,panel) {
		if(!GO.files.saveAsDialog)
		{
			GO.files.saveAsDialog = new GO.files.SaveAsDialog({
				stateId: 'email-save-as',
				stateful: true
			});
		}
		GO.files.saveAsDialog.show({
			folder_id : 0,
			filename: attachment.name,
			handler:function(dialog, folder_id, filename){

				GO.request({
					maskEl:dialog.el,
					url: 'email/message/saveAttachment',
					params:{
						uid: panel.uid,
						mailbox: panel.mailbox,
						number: attachment.number,
						encoding: attachment.encoding,
						type: attachment.type,
						subtype: attachment.subtype,
						account_id: panel.account_id,
						uuencoded_partnumber: attachment.uuencoded_partnumber,
						folder_id: folder_id,
						filename: filename,
						tmp_file: attachment.tmp_file ? attachment.tmp_file : 0,
						charset:attachment.charset,
						sender:panel.data.sender,
						filepath:panel.data.path//smime message are cached on disk
					},
					success: function(options, response, result)
					{
						dialog.hide();
					},
					scope:this
				});
			},
			scope:this
		});
	}

GO.email.openAttachment = function(attachment, panel, forceDownload) {
		if(!panel || !attachment) {
			return false;
		}

		if(forceDownload) {
			attachment.url += '&inline=0';
			go.util.downloadFile(attachment.url);
			return;
		}

		if(!forceDownload && (attachment.mime==='message/rfc822' || attachment.mime==='application/eml')) {
			GO.email.showMessageAttachment(0, {
				action:'attachment',
				account_id: panel.account_id,
				mailbox: panel.mailbox,
				uid: panel.uid,
				number: attachment.number,
				uuencoded_partnumber: attachment.uuencoded_partnumber,
				encoding: attachment.encoding,
				type: attachment.type,
				subtype: attachment.subtype,
				filename:attachment.name,
				charset:attachment.charset,
				sender:panel.data.sender, //for gnupg and smime,
				filepath:panel.data.path ? panel.data.path : '' //In some cases encrypted messages are temporary stored on disk so the handlers must use that to fetch the data.
			});
		} else {
			switch(attachment.extension) {
				case 'ics':
					go.openIcs && go.openIcs({
						accountId:panel.account_id,
						mailbox: panel.mailbox,
						uid:panel.uid,
						partId: attachment.number,
						encoding:attachment.encoding
					});
					break;
				case 'vcf':
					Ext.MessageBox.confirm(t('Confirm'), t('Are you sure that you would like to import this VCard?'),
						function(btn) {
							if (btn !== "yes") {
								return;
							}
							Ext.getBody().mask(t("Importing..."));
							go.Jmap.request({
								method: "Contact/loadVCF",
								params: {
									account_id: panel.account_id,
									mailbox: panel.mailbox,
									uid: panel.uid,
									number: attachment.number,
									encoding: attachment.encoding
								},
								callback: function (options, success, response) {
									Ext.getBody().unmask();
									if (!success) {
										Ext.MessageBox.alert(t("Error"), response.errors.join("<br />"));
									} else {
										var dlg = new go.modules.community.addressbook.ContactDialog();
										dlg.load(response.contactId).show();
									}
								}
							});
						});
					break;
				case 'bmp':
				case 'png':
				case 'gif':
				case 'jpg':
				case 'jpeg':

					if(GO.files && !forceDownload) {
						if(!this.imageViewer) {
							this.imageViewer = new GO.files.ImageViewer({
								closeAction:'hide'
							});
						}

						var index = 0;
						var images = Array();
						if(panel) {
							for (var i = 0; i < panel.data.attachments.length;  i++) {
								var r = panel.data.attachments[i];
								var ext = GO.util.getFileExtension(r.name);

								if(ext=='jpg' || ext=='png' || ext=='gif' || ext=='bmp' || ext=='jpeg') {
									images.push({
										name: r.name,
										src: r.url+'&inline=1',
										download_path: r.url+'&inline=0'
									});
								}
								if(r.name==attachment.name) {
									index=images.length-1;
								}
							}
							this.imageViewer.show(images, index);
							break;
						}
					}

				default:
					if(go.Modules.isAvailable('legacy', 'files') && attachment.name.toLowerCase() != 'winmail.dat') {
						return GO.files.openEmailAttachment(attachment, panel, false);
					} else {
						go.util.viewFile(attachment.url);
					}

					break;
			}
		}
	};

/**
 * Unlink an email
 * @param linkId
 */
GO.email.unlink = function(linkId) {
	Ext.MessageBox.confirm(t("Delete"), t("Are you sure you want to unlink this item?"), function (btn) {
		if (btn === "yes") {
			go.Db.store("Link").set({
				destroy: [linkId]
			}).then(() => {
				GO.mainLayout.getModulePanel('email').messagePanel.reload();
			});
		}
	}, this);
};

/**
 * Function that will open an email composer. If a composer is already open it will create a new one. Otherwise it will reuse an already created one.
 * 
 * {
 *	values: {to: "merijn@intermesh.nl"}
 * }
 */
GO.email.showComposer = function(config){

	config = config || {};

	GO.email.composers = GO.email.composers || [];

	var availableComposer;

	for(var i=0;i<GO.email.composers.length;i++) {
		if(!GO.email.composers[i].isVisible()) {
			availableComposer=GO.email.composers[i];
			break;
		}
	}


	if(!availableComposer) {
		config.move=30*GO.email.composers.length;

		availableComposer = new GO.email.EmailComposer();
		availableComposer.on('send', function(composer){
			if(composer.sendParams.reply_uid && composer.sendParams.reply_uid>0) {
				var record = GO.email.messagesGrid.store.getById(composer.sendParams.reply_uid);
				if(record) {
					record.set('answered',true);
				}
			}

			if(composer.sendParams.forward_uid && composer.sendParams.forward_uid>0) {
				var record = GO.email.messagesGrid.store.getById(composer.sendParams.forward_uid);
				if(record) {
					record.set('forwarded',true);
				}
			}

			if(GO.email.messagesGrid && GO.email.messagesGrid.store.loaded && (GO.email.messagesGrid.store.reader.jsonData.sent || (GO.email.messagesGrid.store.reader.jsonData.drafts && composer.sendParams.draft_uid && composer.sendParams.draft_uid>0))) {
				GO.email.messagesGrid.store.reload();
			}
		});

		availableComposer.on('save', function(composer){

			if(GO.email.messagesGrid && GO.email.messagesGrid.store.loaded && GO.email.messagesGrid.store.reader.jsonData.drafts) {
				GO.email.messagesGrid.store.reload();
			}
		});


		GO.email.composers.push(availableComposer);
	}

	availableComposer.show(config);

	return availableComposer;
}

GO.email.extraTreeContextMenuItems = [];

go.Modules.register("legacy", 'email', {
	mainPanel: GO.email.EmailClient,
	title: t("E-mail"),
	userSettingsPanels: ["GO.email.SettingsPanel"]
});

(function() {

	function launchAddressContextMenu(e, href){
		var queryString = '';
		var email = '';
		var indexOf = href.indexOf('?');
		if(indexOf>-1) {
			email = href.substr(7, indexOf-7);
			queryString = href.substr(indexOf+1);
		} else {
			email = href.substr(7);
		}

		e.preventDefault();

		var addresses = go.util.parseEmail(email);

		GO.email.addressContextMenu.showAt(e.getXY(), addresses[0].email, addresses[0].name, queryString);
	}

	function checkForMailto(e, target) {

		if(target.tagName=='A' && target.attributes.href) {
			var href=target.attributes.href.value;

			if(href.substr(0,6)=='mailto') {
				launchAddressContextMenu(e, href);
			}
		}
	}

	Ext.getBody().on('click', checkForMailto);
})();

GO.newMenuItems.push({
	itemId : 'email-files',
	text: t("Email files", "email"),
	iconCls: 'ic-email',
	handler:function(item, e){
		var panel = item.parentMenu.panel;

		if (panel.model_name == 'GO\\Files\\Model\\File') {
			GO.request({
				url:'files/file/display',
				maskEl:panel.ownerCt.getEl(),
				params:{
					id: panel.data.id
				},
				success:function(response, options, result){
					GO.email.emailFiles(result.data, this);
				},
				scope: this
			});
		} else {
			GO.request({
				url:'files/folder/checkModelFolder',
				maskEl:panel.ownerCt.getEl(),
				params:{
					mustExist:false,
					model: panel.model_name,
					id: panel.data.id
				},
				success:function(response, options, result){
					GO.email.openFolderTree(result.files_folder_id, 0, panel);
				},
				scope: this
			});
		}
	}
});

// files is array of objects with {name, path, size, type, extension}
GO.email.emailFiles = function(files, detailView) {
	if (!Ext.isArray(files)) {
		files = new Array(files);
	}

	var promise;
	if(detailView && detailView.getEmailComposerConfig) {
		promise = detailView.getEmailComposerConfig();
	} else {
		promise = Promise.resolve({});
	}

	promise.then(config => {
		const c = GO.email.showComposer(config);

		c.on('dialog_ready', function(){
			var items = [];
			for (var i = 0; i < files.length; i++) {
				items.push({
					human_size: Ext.util.Format.fileSize(files[i].size),
					extension: files[i].extension,
					size: files[i].size,
					type: files[i].type,
					name: files[i].name,
					fileName: files[i].name,
					from_file_storage: true,
					tmp_file: files[i].path
				});
			}
			c.emailEditor.attachmentsView.addFiles(items);

			if(detailView) {
				c.createLinkButton.addLink(detailView.entity || detailView.entityStore.entity.name, detailView.data.id);
			}
		},this,{single:true});
	});
}

 GO.email.openFolderTree = function(id, folder_id, detailView) {

	if (!GO.email.treeFileBrowser) {
		GO.email.treeFileBrowser = new GO.Window({
			title: t("File browser", "files"),
			height:500,
			width:400,
			layout:'fit',
			border:false,
			maximizable:true,
			collapsible:true,
			closeAction:'hide',
			items: [
				GO.email.folderTree = new GO.files.TreeFilePanel()
			],
			listeners:{
				show:function(){
					this.btnSelectAll.toggle(false);
				},
				scope:this
			},
			tbar: new Ext.Toolbar({
				region:'north',
				items:[{
					iconCls: 'ic-refresh',
					text: t("Refresh"),
					handler: function() {
						GO.email.folderTree.getRootNode().reload()
						this.btnSelectAll.toggle(false);
					},
					scope: this
				},
				this.btnSelectAll = new Ext.Button({
					iconCls: 'ic-done-all',
					text: t("Select all"),
					enableToggle: true,
					pressed: false,
					toggleHandler: function(btn, state) {
						GO.email.folderTree.getRootNode().cascade(function(n) {
							n.getUI().toggleCheck(state);
						});
					},
					scope: this
				})
				]
			}),
			buttons:[{
				text: t("Ok"),
				handler: function(){
					var selFiles = new Array();
					var selNodes = GO.email.folderTree.getChecked();

					Ext.each(selNodes, function(node) {
						selFiles.push(node.attributes);
					});

					GO.email.emailFiles(selFiles, GO.email.treeFileBrowser.detailView);

					GO.email.treeFileBrowser.hide();
				},
				scope:this
			}]
		});
	}

	GO.email.folderTree.getLoader().baseParams.root_folder_id=id;
	GO.email.folderTree.getLoader().baseParams.expand_folder_id=folder_id;
	GO.email.folderTree.getRootNode().reload({
		callback:function(){
			delete GO.email.folderTree.getLoader().baseParams.expand_folder_id;
		},
		scope:this
	});


	GO.email.treeFileBrowser.detailView = detailView;
	GO.email.treeFileBrowser.show();
}

GO.email.showMessageAttachment = function(id, remoteMessage){

	if(!GO.email.linkedMessagePanel){
		GO.email.linkedMessagePanel = new GO.email.LinkedMessagePanel();

		GO.email.linkedMessageWin = new GO.Window({
			maximizable:true,
			collapsible:true,
			stateId:'em-linked-message-panel',
			title: t("E-mail message", "email"),
			height: 500,
			width: 800,
			closeAction:'hide',
			layout:'fit',
			items: GO.email.linkedMessagePanel
		});
	}

	if(!remoteMessage)
		remoteMessage={};

	GO.email.linkedMessagePanel.remoteMessage=remoteMessage;
	GO.email.linkedMessageWin.show();
	GO.email.linkedMessagePanel.load(id, remoteMessage);
}


GO.email.showAttendanceWindow=function(event_id){
	if(!GO.email.attendanceWindow){
		GO.email.attendanceWindow = new GO.calendar.AttendanceWindow ();
	}
	GO.email.attendanceWindow.show(event_id);
}


GO.email.moveToSpam = function(mailUid,mailboxName,fromAccountId) {
	this.messagesGrid.getView().scrollToTopOnLoad=false;
	Ext.Msg.show({
		title: t("Move to Spam folder?", "email"),
		icon: Ext.MessageBox.QUESTION,
		msg: t("Are you sure you want to classify this message as spam?", "email"),
		buttons: Ext.Msg.YESNO,
		fn: function(btn) {
			if (btn=='yes') {
				var me = this;
				GO.request({
					url: 'email/message/moveToSpam',
					params: {
						account_id: fromAccountId,
						from_mailbox_name: mailboxName,
						mail_uid: JSON.stringify(mailUid)
					},
					success: function() {
						var records = me.messagesGrid.selModel.getSelections();
						var lastItem = records.pop();
						var index = me.messagesGrid.store.indexOfId(lastItem.id);
						me.messagesGrid.selModel.selectRow(index + 1);
						GO.email.emailClient.leftMessagesGrid.store.load();
					},
					failure: function(response,options,result) {
						console.log(response);
						console.log(options);
						console.log(result);
					}
				});
			}
		},
		scope : this
	});
}

GO.email.moveToInbox = function(mailUid,fromAccountId) {
	Ext.Msg.show({
		title: t("Move out of Spam folder?", "email"),
		icon: Ext.MessageBox.QUESTION,
		msg: t("Are you sure you want to remove the spam mark and move this message into your inbox?", "email"),
		buttons: Ext.Msg.YESNO,
		fn: function(btn) {
			if (btn=='yes') {
				GO.request({
					url: 'email/message/moveToInbox',
					params: {
						account_id: fromAccountId,
						mail_uid: mailUid
					},
					success: function() {
						GO.email.emailClient.leftMessagesGrid.store.load();
					},
					failure: function(response,options,result) {
						console.log(response);
						console.log(options);
						console.log(result);
					}
				});
			}
		},
		scope : this
	});
}
