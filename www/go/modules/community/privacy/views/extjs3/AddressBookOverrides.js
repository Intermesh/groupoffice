GO.moduleManager.onModuleReady('addressbook',function() {
	Ext.override(go.modules.community.addressbook.ContactDetail, {
		initComponent: go.modules.community.addressbook.ContactDetail.prototype.initComponent.createSequence(function () {
			this.deleteInXDays = -1;
			this.privacyPanel = new Ext.Panel({
				title: t("Privacy"),
				hidden: true,
				onLoad: (detailView) => {
					this.privacyPanel.hide();
					if (detailView.data.deletionDate || this.deleteInXDays > -1) {
						this.privacyPanel.show();
					}
				},
				tpl: '<div class="icons">' +
					'<p class="s6">' +
					'<i class="icon label">security</i>' +
					'<span>{[go.util.Format.date(values.deletionDate?.deleteAt)]}</span>	' +
					'<label>' + t("Delete at") + '</label>' +
					'</p>' +
					'</div>'
			})
			this.deletionPanel = new Ext.Panel({
				hidden: true,
				cls: "go-message-panel",
				html: "<i class='icon danger'>warning</i> " + t("This contact is inactive and will be moved to trash in {x} days.").replace("{x}", this.deleteInXDays),
				onLoad: (dv) => {
					this.deletionPanel.hide();
					if (this.deleteInXDays > -1) {
						this.deletionPanel.update({
							html: "<i class='icon danger'>warning</i> " +
								t("This contact is inactive and will be moved to trash in {x} days.").replace("{x}", this.deleteInXDays),
						});
						this.deletionPanel.show();
					}
				}
			});
			this.insert(2, this.privacyPanel);
			this.insert(0, this.deletionPanel);
		}),

		onLoad: function () {
			this.getTopToolbar().getComponent("edit").setDisabled(this.data.permissionLevel < go.permissionLevels.write);

			this.deleteInXDays = -1;
			const module = go.Modules.get("community", "privacy"), settings = module.settings,
				arAbs = settings.monitorAddressBooks.split(",").map(x => parseInt(x)),
				today = new Date();
			let referenceDate, deletionDate;

			// First check whether there is an explicit deletion date
			if (this.data.deletionDate && this.data.deletionDate.deleteAt && this.data.addressBookId !== settings.trashAddressBook) {
				deletionDate = new Date(this.data.deletionDate.deleteAt);
			} else if (arAbs.indexOf(this.data.addressBookId) > -1) {
				// Otherwise, check whether contact is in one of the monitored address book and should be deleted
				deletionDate = new Date(this.data.createdAt).add(Date.DAY, settings.trashAfterXDays);
			}
			if (deletionDate) {
				referenceDate = deletionDate.add(Date.DAY, (0 - settings.warnXDaysBeforeDeletion))
				if (referenceDate <= today) {
					this.deleteInXDays = deletionDate.calculateDaysBetweenDates(today);
				}
			}
			go.modules.community.addressbook.ContactDetail.superclass.onLoad.call(this);

		},

	});

	Ext.override(go.modules.community.addressbook.ContactDialog, {
		initComponent: go.modules.community.addressbook.ContactDialog.prototype.initComponent.createSequence(function () {
			this.privacyFieldset = new Ext.form.FieldSet({
				xtype: 'fieldset',
				items: [new go.form.DateField({
					flex: 1,
					xtype: "datefield",
					allowBlank: true,
					name: "deletionDate.deleteAt",
					setFocus: true,
					fieldLabel: t("Delete at"),
				})],
				title: t("Privacy")
			});
			this.mainPanel.insert(1, this.privacyFieldset);
		})
	});

	Ext.override(go.modules.community.addressbook.MainPanel, {
		initComponent: go.modules.community.addressbook.MainPanel.prototype.initComponent.createSequence(function () {
			const tt = this.grid.topToolbar;
			this.emptyTrashBtn = new Ext.Button({
				iconCls: 'ic-delete-forever',
				hidden: true,
				cls: 'danger',
				text: t("Empty trash"),
				tooltip: t('Empty trash'),
				handler: go.modules.community.privacy.emptyTrashHandler
			});
			tt.insertButton(0, this.emptyTrashBtn);
		}),
		setAddressBookId: go.modules.community.addressbook.MainPanel.prototype.setAddressBookId.createSequence(function (addressBookIds) {
			this.emptyTrashBtn.hide();
			if (go.User.isAdmin) {
				const module = go.Modules.get("community", "privacy"), settings = module.settings,
					trashABId = settings.trashAddressBook;
				if (!go.util.empty(addressBookIds) && addressBookIds.indexOf(trashABId) > -1) {
					this.emptyTrashBtn.show();
				}
			}
		}),
	});

	Ext.override(go.modules.community.addressbook.AddressBookTree, {
		initComponent: go.modules.community.addressbook.AddressBookTree.prototype.initComponent.createSequence(function () {
			this.emptyTrashBtn = null;
		}),

		showAddressBookMoreMenu: go.modules.community.addressbook.AddressBookTree.prototype.showAddressBookMoreMenu.createSequence(function (node, e) {
			const module = go.Modules.get("community", "privacy"), settings = module.settings, trashABId = settings.trashAddressBook;

			if(!this.emptyTrashBtn && go.User.isAdmin) {
				this.emptyTrashBtn = new Ext.menu.Item({
					itemId: "empty-trash",
					iconCls: "ic-delete-forever",
					text: t("Empty trash"),
					handler: go.modules.community.privacy.emptyTrashHandler,
					scope: this
				});
				this.addressBookMoreMenu.insert(2, this.emptyTrashBtn);
			}
			if(node.attributes.data.id === trashABId && go.User.isAdmin) {
				this.addressBookMoreMenu.getComponent("empty-trash").enable();
			} else {
				this.addressBookMoreMenu.getComponent("empty-trash").disable();
			}

		}),
	});
});