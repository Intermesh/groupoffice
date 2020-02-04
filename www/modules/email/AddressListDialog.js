GO.email.AddressListDialog = Ext.extend(go.Window, {
	stateId: 'address-addToForm',
	entityStore: "AddressList",
	width: dp(800),
	height: dp(800),
	maximizable: true,
	collapsible: true,
	modal: false,

	createGrid: function () {
		this.grid = new GO.email.AddressListGrid({
			stateId: 'grid-address-list',
			region: 'center',
			height: dp(800),
			email: this.email,
			delete: this.delete,
			tbar: [
				'->',
				{
					xtype: 'tbsearch'
				}
			]
		});
		return this.grid;
	},
	initComponent: function () {

		this.items = [{
				xtype: 'fieldset',
				items: [this.createGrid()]
			}
		];

		this.buttons = [new Ext.Button({
			text : this.delete ? t("Remove") : t("Add"),
			handler : function() {

					var allChecks = this.grid.getColumnChecks();
					var store = this.grid.store;
					var deleteDialog = this.delete;
					var me = this;
					var email = this.email;
					var from = this.from;

					go.Db.store("Contact").query({
						filter: {
							email: this.email,
							permissionLevel: go.permissionLevels.write
						},
						limit: 1
					}).then(function(result) {

						// contact found
						if(result.ids.length) {

							//alert(allChecks[i]);
							var contact = go.Db.store("Contact").single(result.ids[0]).then(function (contact) {
								if (!contact.addressLists) {
									contact.addressLists = {};
								}
								for(var i = 0; i < allChecks.length;i++) {
									contact.addressLists[allChecks[i]] = !deleteDialog;
								}
								go.Db.store("Contact").save({addressLists: contact.addressLists}, contact.id);

								if (deleteDialog) {
									Ext.Msg.show({
										title: t("Address list","email"),
										msg: t("Senders have been removed from address list","email"),
										buttons: Ext.Msg.OK
									});
								} else {
									Ext.Msg.show({
										title: t("Address list","email"),
										msg: t("Senders have been added to address list","email"),
										buttons: Ext.Msg.OK
									});
								}


							});

							store.load();
							store.reload();
							me.hide();
						} else {

							// create new contact
							var dialog = new go.modules.community.addressbook.ContactDialog();
							var addressLists = {};
							for(var i = 0; i < allChecks.length;i++) {
								addressLists[allChecks[i]] = true;
							}
							dialog.show();

							var nameParts = from.split(" ");

							var name = from;
							var firstName = nameParts.shift();
							var lastName = nameParts.join(" ");

							var v = {
								emailAddresses: [{
									type: "work",
									email: email,
								}],
								name: name,
								firstName: firstName,
								lastName: lastName,
								addressLists: addressLists
							};

							dialog.setValues(v);
							me.hide();
						}
					});
			},
			scope : this
		})];
		GO.email.AddressListDialog.superclass.initComponent.call(this);
	},

	// onLoad : function(entityValues) {
	// 	this.supr().onLoad.call(this, entityValues);
	//
	// 	if (!entityValues.content || entityValues.content.substring(0, 8) !== "{GOCRYPT") {
	// 		return;
	// 	}
	//
	// 	var data = entityValues.content, me = this;
	// 	me.setValues({"content": t("Encrypted data")});
	// 	go.modules.community.notes.Decrypter.decrypt(data).then(function(text) {
	// 		me.setValues({"content": text});
	// 	}).catch(function(){});
	//
	// }
});
