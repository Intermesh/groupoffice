go.modules.community.addressbook.ContactContextMenu = Ext.extend(Ext.menu.Menu,{

	records: [],

	initComponent() {

		this.items=[
			this.editButton = new Ext.menu.Item({
				iconCls: 'ic-edit',
				text: t("Edit"),
				handler: () => (new go.modules.community.addressbook.ContactDialog()).load(this.record[0].id).show()
			}),
			this.starItem = new Ext.menu.Item({
				iconCls: "ic-star",
				text: t("Star"),
				handler: () => {
					const update = {};
					this.records.map(r => {update[r.id] = {starred: true} });
					go.Db.store("Contact").set({update});
				}
			}),
			'-',
			{
			// Which groups should we suggest? should we move the contact to a different address book if skip adding of they don't match the contacts AB?
			// 	text: t('Add to group'),
			// 	iconCls: 'ic-group-add',
			// 	menu: new Ext.menu.Menu({})
			// },{
				text: t('Remove from group'),
				iconCls: 'ic-clear',
				menu: new Ext.menu.Menu({listeners: {
					'beforeshow': me => {
						me.removeAll();
						this.groups.forEach(group => {
							me.addItem({
								text: group.name,
								iconCls: 'ic-clear',
								data: group.id,
								handler: me => {
									const update = {};
									this.records.map(r => {
										if(r.data.groups && r.data.groups.indexOf(me.data) !== -1) { // is in group
											update[r.id] = {groups: r.data.groups.filter(id => (id !== me.data))}
										}
									});
									go.Db.store("Contact").set({update});
								}
							})
						});
					}
				}})
			},
			'-',
			this.printBtn = new Ext.menu.Item({
				iconCls: "ic-print",
				text: t("Print"),
				handler: () => { this.body.print({title: this.records[0].name}); }
			}),this.downloadBtn = new Ext.menu.Item({
				iconCls: "ic-cloud-download",
				text: t("Export") + " (vCard)",
				handler: () => { go.util.downloadFile(go.Jmap.downloadUrl("community/addressbook/vcard/" + this.records[0].id)); }
			}),this.sendBtn = new Ext.menu.Item({
				iconCls: "ic-attach-file",
				text: t("Send") + " (vCard)",
				handler: () => {
					Ext.getBody().mask(t("Exporting..."));
					go.Jmap.request({
						method: "Contact/export",
						params: {
							extension: 'vcf',
							ids: this.getIds()
						},
						callback: function (options, success, response) {
							Ext.getBody().unmask();
							if(!success) {
								Ext.MessageBox.alert(t("Error"), response.message);
							} else {
								GO.email.showComposer({blobs: [response.blob]});
							}
						}
					});
				}
			}),
			'-',
			this.deleteButton = new Ext.menu.Item({
				iconCls: 'ic-delete',
				text: t("Delete"),
				handler: () => {
					Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), btn => {
						if (btn === "yes")
							this.entityStore.set({destroy: [this.currentId]});
					});
				}
			})
		];
		this.supr().initComponent.call(this);
	},

	getIds() {
		return this.records.map(r => r.id);
	},

	addPrintBody(body) {
		this.body = body;
		this.printBtn.setVisible(true);
		return this;
	},

	setRecords(records) {
		this.records = records;
		this.printBtn.setVisible(false);
		this.downloadBtn.setDisabled(records.length > 1);
		this.sendBtn.setDisabled(records.length > 1);
		if(records.length > 1) {
			this.deleteButton.setText(t('Delete %d items').replace('%d', records.length));
			this.editButton.setDisabled(true);
		} else if(records.length === 1) {
			const record = records[0];
			this.starItem.setIconClass(record.data.starred ? "ic-star" : "ic-star-border");
			this.editButton.setDisabled(record.data.permissionLevel < GO.permissionLevels.write);
			this.deleteButton.setDisabled(record.data.permissionLevel < GO.permissionLevels.writeAndDelete);
		}
		const groups = {}
		records.forEach(c => {
			c.data.groups.forEach(groupId => {groups[groupId] = true});
		})

		go.Db.store('AddressBookGroup').get(Object.keys(groups), groups => {
			this.groups = groups;
		});
		return this;
	}
});