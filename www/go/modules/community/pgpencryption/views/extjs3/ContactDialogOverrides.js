Ext.onReady(function () {
	var proto = go.modules.community.addressbook.ContactDialog.prototype;
	var originalInitFormItems = proto.initFormItems;

	Ext.override(go.modules.community.addressbook.ContactDialog, {

		initComponent: proto.initComponent.createSequence(function () {
			this.pgpRemovedIds = [];

			this.pgpStore.on("remove", function (store, record) {
				if (record.data.id) {
					this.pgpRemovedIds.push(record.data.id);
				}
			}, this);

			this.formPanel.on("setvalues", function (form, v) {
				this.loadPgpKeys(v.id);
			}, this);

			this.on("submit", function (dlg, success, serverId) {
				if (success) {
					this.savePgpKeys(serverId || this.currentId);
				}
			}, this);
		}),

		initFormItems: function () {
			var items = originalInitFormItems.call(this);

			this.pgpStore = new Ext.data.JsonStore({
				fields: ["id", "email", "key", {name: "expiresAt", type: "date", dateFormat: "Y-m-d"}]
			});

			this.pgpGrid = new Ext.grid.EditorGridPanel({
				store: this.pgpStore,
				height: dp(300),
				clicksToEdit: 1,
				viewConfig: {forceFit: true},
				columns: [
					{
						header: t("E-mail address", "pgpencryption"),
						dataIndex: "email",
						editor: new Ext.form.TextField({allowBlank: false})
					},
					{
						header: t("Public key", "pgpencryption"),
						dataIndex: "key",
						editor: new Ext.form.TextArea({allowBlank: false})
					},
					{
						header: t("Expires at", "pgpencryption"),
						dataIndex: "expiresAt",
						xtype: "datecolumn",
						format: "Y-m-d",
						editor: new Ext.form.DateField({format: "Y-m-d"})
					}
				],
				tbar: [
					{
						iconCls: "ic-file-upload", text: t("Import"), handler: function () {
							this.importPgpKeyFile();
						}, scope: this
					},
					{
						iconCls: "ic-add", text: t("Add"), handler: function () {
							this.pgpStore.add(new this.pgpStore.recordType({email: "", key: "", expiresAt: null}));
						}, scope: this
					},
					{
						iconCls: "ic-delete", text: t("Delete"), handler: function () {
							var sel = this.pgpGrid.getSelectionModel().getSelectedCell();
							if (sel) {
								this.pgpStore.removeAt(sel[0]);
							}
						}, scope: this
					}
				]
			});

			this.addPanel(new Ext.Panel({
				title: t("PGP encryption", "pgpencryption"),
				items: [{xtype: "fieldset", items: [this.pgpGrid]}]
			}));

			return items;
		},

		loadPgpKeys: function (contactId) {
			this.pgpStore.removeAll();
			this.pgpRemovedIds = [];

			if (!contactId) {
				return;
			}

			var ds = go.Db.store("PublicKey");

			ds.query({filter: {contactId: contactId}}).then(function (result) {
				return Promise.all(result.ids.map(function (id) {
					return ds.single(id);
				}));
			}).then(function (keys) {
				this.pgpStore.loadData(keys);
				this.pgpRemovedIds = [];
			}.bind(this));
		},

		savePgpKeys: function (contactId) {
			var ds = go.Db.store("PublicKey");
			var saves = [];

			this.pgpStore.each(function (r) {
				if (!r.data.id || r.dirty) {
					saves.push(ds.save({
						email: r.data.email,
						key: r.data.key,
						expiresAt: r.data.expiresAt ? Ext.util.Format.date(r.data.expiresAt, "Y-m-d") : null,
						contactId: contactId
					}, r.data.id || undefined));
				}
			});

			this.pgpRemovedIds.forEach(function (id) {
				saves.push(ds.destroy(id));
			});

			return Promise.all(saves).catch(function (e) {
				GO.errorDialog.show(e && e.message ? e.message : t("Could not save the PGP keys", "pgpencryption"));
			});
		},

		importPgpKeyFile: function () {
			const input = document.createElement("input");
			input.type = "file";
			input.accept = ".asc,.pgp,.gpg,.key,text/plain";

			input.onchange = async () => {
				const file = input.files[0];

				if (!file) {
					return;
				}

				try {
					const text = await file.text();
					const match = text.match(/-----BEGIN PGP PUBLIC KEY BLOCK-----[\s\S]*?-----END PGP PUBLIC KEY BLOCK-----/);

					if (!match) {
						throw new Error(t("No public key block found in this file.", "pgpencryption"));
					}

					const armored = match[0];
					const key = await openpgp.readKey({armoredKey: armored});
					const userId = key.getUserIDs()[0] || "";
					const emailMatch = userId.match(/<([^>]+)>/);
					const expires = await key.getExpirationTime();

					this.pgpStore.add(new this.pgpStore.recordType({
						email: emailMatch ? emailMatch[1] : "",
						key: armored,
						expiresAt: expires instanceof Date ? expires : null
					}));
				} catch (e) {
					GO.errorDialog.show(e && e.message ? e.message : String(e));
				}
			};

			input.click();
		},
	});
});