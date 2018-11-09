/* global go, Ext, GO */

go.modules.community.addressbook.SelectDialog = Ext.extend(go.Window, {
	
	layout: "responsive",
	width: dp(800),
	height: dp(600),
	modal: true,
	title: t("Select from address book"),
	handler: function(name, email, id) {
		
	},
	scope: null,
	initComponent : function() {
		
		if(!this.scope) {
			this.scope = this;
		}
		
		this.addressBookTree = new go.modules.community.addressbook.AddressBookTree({
			width: dp(300),
			region: "west",
			split: true,
			selectHandler: this.selectMultiple,
			scope: this
		});
		
		this.labels = t("emailTypes");
		
		this.grid = new go.modules.community.addressbook.ContactGrid({
			region: 'center',
			tbar: [
				{
					cls: 'go-narrow',
					iconCls: "ic-menu",
					handler: function () {
						this.addressBookTree.show();
					},
					scope: this
				},
				'->',
				{
					xtype: 'tbsearch'
				}
			],
			listeners: {
				rowclick: function (grid, rowIndex, e) {

					var record = grid.getStore().getAt(rowIndex), emails = record.get("emailAddresses");
					
					if(emails.length === 1) {
						this.selectEmail(record.get("name"), emails[0].email, record.get("id"));
						return;
					}
					
					var me = this,  items = emails.map(function(a) {
						return {
							data: {
								name: record.get("name"),
								email: a.email,
								id: record.get("id")
							},
							text: "<div>" + a.email + "</div><small>" +  this.labels[a.type] + "</div>",
							handler: function() {
								me.selectEmail(this.data.name, this.data.email, this.data.id);
							}
						};
					}, this);
					
					var m = new Ext.menu.Menu({
						cls: "x-menu-no-icons",
						items: items
					});
					
					m.showAt(e.getXY());
					
				},

				scope: this
			}
		});
		
		this.items = [this.grid, this.addressBookTree];
		
		go.modules.community.addressbook.SelectDialog.superclass.initComponent.call(this);
		
		//because the root node is not visible it will auto expand on render.
		this.addressBookTree.getRootNode().on('expand', function (node) {
			//when expand is done we'll select the first node. This will trigger a selection change. which will load the grid below.
			this.addressBookTree.getSelectionModel().select(node.firstChild);
		}, this);

		//load the grid on selection change.
		this.addressBookTree.getSelectionModel().on('selectionchange', function (sm, node) {
			
			if (!node) {
				return;
			}

			if (node.id === "all") {
				this.setAddressBookId(null);
			} else if (node.attributes.entity.name === "AddressBook") {
				this.setAddressBookId(node.attributes.data.id);
			} else
			{
				this.setGroupId(node.attributes.data.id, node.attributes.data.addressBookId);
			}
		}, this);
	},
	
	
	selectEmail : function(name, email, id) {
		this.handler.call(this.scope, name, email, id);
		this.close();
	},
	
	setAddressBookId: function (addressBookId) {
		var s = this.grid.store;

		s.baseParams.filter = {
			addressBookId: addressBookId,
			hasEmailAddresses: true
		};
		s.load();
	},

	setGroupId: function (groupId, addressBookId) {
		var s = this.grid.store;
		
		s.baseParams.filter = {
			groupId: groupId,
			hasEmailAddresses: true
		};
		s.load();
	},
	
	selectMultiple : function(contacts) {
		contacts.forEach(function(contact) {
			this.selectEmail(contact.name, contact.emailAddresses[0].email, contact.id);
		}, this);
		this.close();
	}
});
