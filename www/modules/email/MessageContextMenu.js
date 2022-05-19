GO.email.MessageContextMenu = Ext.extend(Ext.menu.Menu, {
	shadow: "frame",
	minWidth: 180,
	main: null,
	initComponent: function() {



		var addSendersItems = [{
			text:t("To", "email"),
			field:'to',
			handler:this.main.addSendersTo,
			scope:this.main
		},{
			text:'CC',
			field:'cc',
			handler:this.main.addSendersTo,
			scope:this.main
		},{
			text:'BCC',
			field:'bcc',
			handler:this.main.addSendersTo,
			scope:this.main
		}];

		if(go.Modules.isAvailable("business", "newsletters")) {
			addSendersItems.push({
				text: t("Address list", "newsletters", "business"),
				handler: this.main.addSendersToAddresslist,
				scope: this.main
			});
		}

		var deleteSendersItems = [];

		if(go.Modules.isAvailable("business", "newsletters")) {
			deleteSendersItems.push({
				text: t("Address list",  "newsletters", "business"),
				handler: this.main.deleteSendersFromAddresslist,
				scope: this.main
			});
		}

		var contextItems = [
			this.contextMenuMarkAsRead = new Ext.menu.Item({
				iconCls: 'ic-markunread',
				text: t("Mark as read", "email"),
				handler: function(){
					this.main.flagMessages('Seen', false);
				},
				scope:this,
				multiple:true
			}),
			this.contextMenuMarkAsUnread = new Ext.menu.Item({
				iconCls: 'ic-markunread',
				text: t("Mark as unread", "email"),
				handler: function(){
					this.main.flagMessages('Seen', true);
				},
				scope: this,
				multiple:true
			}),
			this.contextMenuFlag = new Ext.menu.Item({
				iconCls: 'ic-flag',
				text: t("Add flag", "email"),
				handler: function(){
					this.main.flagMessages('Flagged', false);
				},
				scope: this,
				multiple:true
			}),
			this.contextMenuUnflag = new Ext.menu.Item({
				iconCls: 'ic-flag',
				text: t("Remove flag", "email"),
				handler: function(){
					this.main.flagMessages('Flagged', true);
				},
				scope: this,
				multiple:true
			}),
			'-',
			this.contextMenuSource = new Ext.menu.Item ({
				text: t("View source", "email"),
				iconCls: 'ic-code',
				handler: function(){

					var record = this.main.messagesGrid.selModel.getSelected();
					if(record) {
						var win = window.open(GO.url("email/message/source",{account_id:this.main.account_id,mailbox:record.data.mailbox,uid:record.data.uid}));
						win.focus();
					}

				},
				scope: this
			}),'-',
			this.contextMenuCopyTo = new Ext.menu.Item ({
				iconCls: 'ic-content-copy',
				text: t("Copy email to...", "email"),
				handler: function(a,b,c){
					var selectedEmails = this.main.messagesGrid.getSelectionModel().getSelections();
					this.main.showCopyMailToDialog(selectedEmails);
				},
				scope: this,
				multiple:true
			}),

			this.contextMenuMoveTo = new Ext.menu.Item ({
				iconCls: 'ic-move-to-inbox',
				text: t("Move email to...", "email"),
				handler: function(a,b,c){
					var selectedEmails = this.main.messagesGrid.getSelectionModel().getSelections();
					this.main.showCopyMailToDialog(selectedEmails, true);
				},
				scope: this,
				multiple:true
			}),
			this.addEmailButton = new Ext.menu.Item({
				iconCls: 'ic-mail',
				text: t("Forward as attachment ", "email"),
				handler: function(){
					var records = this.main.messagesGrid.selModel.getSelections();
					if(records) {

						var addEmailAsAttachmentList = [];

						Ext.each(records, function(record) {
							addEmailAsAttachmentList.push({
								uid: record.get('uid'),
								mailbox: record.get('mailbox')
							})
						});

						GO.email.showComposer({
							values: addEmailAsAttachmentList.length == 1 ? {subject: "Fwd: " + records[0].data.subject} : undefined,
							account_id: this.main.account_id,
							addEmailAsAttachmentList: addEmailAsAttachmentList
						});
					}
				},
				scope:this,
				multiple:true
			}),
			'-',
			this.contextMenuDelete = new Ext.menu.Item({
				iconCls: 'ic-delete',
				text: t("Delete"),
				handler: function(){
					this.main.messagesGrid.deleteSelected();
				},
				scope: this,
				multiple:true
			}),
			this.contextMoveToSpamFolder = new Ext.menu.Item({
				iconCls: 'ic-report',
				text: t("Move to spam folder", "email"),
				handler: function(){
					var records = this.main.messagesGrid.selModel.getSelections();
					if(!records) {
						return;
					}
					const uids = [];
					const mailbox = records[0].get("mailbox");

					Ext.each(records, function(record) {
						uids.push(record.get('uid'));
					}, this);

					GO.email.moveToSpam(uids, mailbox, this.main.account_id);

				},
				scope: this,
				multiple:true
			})
			,'-',{
				iconCls: 'ic-add',
				text: t("Add senders to...", "email"),
				menu: {
					items: addSendersItems
				},
				multiple:true
			},{
				iconCls: 'ic-delete',
				text: t("Delete senders from...", "email"),
				menu: {
					items: deleteSendersItems
				},
				multiple:true,
				hidden: (deleteSendersItems.length === 0)
			}];

		if(GO.email.saveAsItems && GO.email.saveAsItems.length) {
			this.saveAsMenu = new Ext.menu.Menu({
				items:GO.email.saveAsItems
			});

			this.saveAsMenu.on('show', function(menu){
				var sm = this.main.messagesGrid.getSelectionModel();
				var multiple = sm.getSelections().length>1;
				var none = sm.getSelections().length==0;

				for(var i=0;i<menu.items.getCount();i++)
				{
					var item = menu.items.get(i);
					item.setDisabled(none || (!item.multiple && multiple));
				}
			}, this);

			contextItems.push({
				iconCls: 'ic-save',
				text:t("Save as"),
				menu:this.saveAsMenu,
				multiple:true
			});
		}

		this.setCheckStateOnLabelsMenu = function(onload) {
			if (this.labelsContextMenu.store.loaded || onload) {

				var flags = [];

				this.labelsContextMenu.items.each(function(item) {
					flags[item.flag] = item;
					item.textEl.setStyle('color', '#' + item.color);
					item.setChecked(false);
				});

				var selectedRows = this.main.messagesGrid.selModel.selections.keys, record;

				Ext.each(selectedRows, function(id) {
					record = this.main.messagesGrid.store.getById(id);

					Ext.each(record.get('labels'), function(label) {
						if (Ext.isDefined(flags[label.flag])) {
							flags[label.flag].setChecked(true);
						}
					});
				}, this);
			}
		};

		contextItems.push(
			this.contextMenuLabels = new Ext.menu.Item ({
				iconCls: 'ic-label',
				text: t("Labels", "email"),
				menu: this.labelsContextMenu = new GO.menu.JsonMenu({
					id: 'email-messages-labels-menu',
					store: new GO.data.JsonStore({
						url: GO.url("email/label/store"),
						baseParams: {
							account_id: 0,
							forContextMenu: true
						},
						fields: ['flag', 'text', 'color'],
						remoteSort: true
					}),
					listeners:{
						scope:this,
						load: function() {
							this.setCheckStateOnLabelsMenu();
						},

						beforeshow: function() {
							var isDefined = Ext.isDefined(this.labelsContextMenu.store.baseParams.account_id) && this.labelsContextMenu.store.baseParams.account_id !== null;
							if (!isDefined || (isDefined && this.labelsContextMenu.store.baseParams.account_id != this.main.messagesStore.baseParams.account_id)) {
								this.labelsContextMenu.store.loaded = true; //hack - ignore initial store load
								this.labelsContextMenu.store.baseParams.account_id = this.main.messagesStore.baseParams.account_id;
								this.labelsContextMenu.store.load();
							}
						},

						show: function() {
							this.setCheckStateOnLabelsMenu();
						},

						itemclick : function(item, e) {
							this.main.flagMessages(item.flag, item.checked);
							if (this.main.messagePanel.uid) {
								this.main.messagePanel.loadMessage();
							}
							var recs = this.main.messagesGrid.getSelectionModel().getSelections();

							Ext.each(recs, function (rec) {
								var isRemovet = false;
								for(var i=0; i<rec.data.labels.length; i++) {
									var label = rec.data.labels[i];

									if(label.flag == item.flag) {
										rec.data.labels.splice(i);
										isRemovet = true;
									}
								}

								if(!isRemovet) {
									rec.data.labels.push(item);
								}
							})
						}
					}
				}),
				multiple:true
			})
		);

this.items = contextItems;

		this.on("show", function(){

			var record = this.main.messagesGrid.selModel.getSelected();

			this.contextMenuMarkAsUnread.setVisible(record.data.seen);
			this.contextMenuMarkAsRead.setVisible(!record.data.seen);

			this.contextMenuMarkAsUnread.setDisabled(this.permissionLevel<GO.permissionLevels.create);
			this.contextMenuMarkAsRead.setDisabled(this.permissionLevel<GO.email.permissionLevels.delegated);

			this.contextMenuFlag.setVisible(!record.data.flagged);
			this.contextMenuUnflag.setVisible(record.data.flagged);

			this.contextMenuFlag.setDisabled(this.permissionLevel<GO.email.permissionLevels.delegated);
			this.contextMenuUnflag.setDisabled(this.permissionLevel<GO.email.permissionLevels.delegated);

			this.contextMenuDelete.setDisabled(this.readOnly);
		}, this);



		this.supr().initComponent.call(this);
	}
});

