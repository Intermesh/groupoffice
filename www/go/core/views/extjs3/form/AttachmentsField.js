go.form.AttachmentsField = Ext.extend(Ext.Panel, {
	layout: "border",
	height: dp(40),
	name: null,
	isFormField: true,
	_isDirty: false,
	hidden: false,

	hasDescription: false,

	initComponent: function () {

		const fields = [
			"blobId",
			"name",
			{
				name: "inline",
				type: "bool"
			}, {
				name: "attachment",
				type: "bool"
			}
		];

		if(this.hasDescription) {
			fields.push("description");
		}

		this.store = new go.data.Store({
			fields
		});

		this.dataView = new Ext.DataView({
			store: this.store,
			region: "center",
			overClass: 'x-view-over',
			multiSelect: true,
			autoScroll: true,
			itemSelector: 'span.filetype-link',
			tpl: new Ext.XTemplate(
				'<div style="overflow-x:hidden" tabindex="0" class="go-attachments">' +
				'<tpl for=".">',
				'<span class="filetype-link filetype-{[this.getExtension(values.name)]} x-unselectable" unselectable="on" style="float:left" id="{id}">{name}' +
				'<tpl if="values.description"> - <span style="color: var(--c-secondary);">{description}</span></tpl></span>' +
				'</tpl>' +
				'</div>',
				'<div class="x-clear"></div>',
				{
					getExtension: function (name) {
						const dotPos = name.lastIndexOf(".");
						if (dotPos === -1) {
							return "unknown";
						}

						return name.substring(dotPos + 1, name.length);
					}
				}
			),
			listeners: {
				click: this.onAttachmentClick,
				scope: this
			}
		});

		this.items = [
			this.dataView,
			{
				width: dp(64),
				region: "east",
				xtype: "container",
				items: [this.createAttachBtn()]
			}
		];

		go.form.AttachmentsField.superclass.initComponent.call(this);
	},

	afterRender: function () {
		go.form.AttachmentsField.superclass.afterRender.call(this);

		this.getEl().dom.addEventListener('drop', this.onDrop.createDelegate(this));

		this.getEl().dom.addEventListener("dragover", function (event) {
			// prevent default to allow drop
			event.preventDefault();
		}, false);

	},

	onAttachmentClick: function (me, index, node, e) {
		if (!this.menu) {
			this.menu = new Ext.menu.Menu({
				items: [{
					iconCls: 'ic-cloud-download',
					text: t("Open"),
					scope: this,
					handler: function () {
						const records = this.dataView.getSelectedRecords();
						window.open(go.Jmap.downloadUrl(records[0].data.blobId, true));
					}
				},{
					hidden: !this.hasDescription,
					iconCls: 'ic-edit',
					text: t("Edit"),
					scope: this,
					handler: function() {
						const records = this.dataView.getSelectedRecords(), curr = records[0];
						let description = curr.data.description || "";
						Ext.MessageBox.prompt(t("Description"), t("Please enter a description"),
							(btn, value) => {
							if(btn === "ok") {
								curr.data.description = value || "";
								this.dataView.refresh();
								this.syncHeight();
							}
							},this, false, description);
					}
				},{
						iconCls: 'ic-delete',
						text: t("Delete"),
						scope: this,
						handler: function () {
							const records = this.dataView.getSelectedRecords();
							this.store.remove(records);
							this.syncHeight();
						}
					}]
			});
		}

		if (!this.dataView.isSelected(node)) {
			this.dataView.select(node);
		}

		e.preventDefault();
		this.menu.showAt(e.getXY());
	},

	onDrop: function (e) {
		if (!e.dataTransfer.files) {
			return;
		}
		e.preventDefault();

		Array.from(e.dataTransfer.files).forEach(function (file) {
			go.Jmap.upload(file, {
				scope: this,
				success: function (response) {
					this.addAttachment({
						blobId: response.blobId,
						attachment: true,
						inline: false,
						name: file.name
					});
				}
			});
		}, this);

	},

	getName: function () {
		return this.name;
	},

	reset: function () {
		this.setValue({});
	},

	isDirty: function () {
		return true; //// TODO ///
	},

	setValue: function (records) {
		const data = {};
		data[this.store.root] = records;

		this.store.loadData(data);
		if (this.rendered) {
			this.dataView.refresh();
		}
		this.syncHeight();
	},

	syncHeight: function () {
		this.setHeight(dp(40));
		this.dataView.setHeight(dp(40));
		if (this.rendered) {
			this.setHeight(Math.max(dp(40), this.dataView.getEl().dom.scrollHeight));
			this.ownerCt.doLayout();
		}
	},

	addAttachment: function (record) {
		this.setValue(this.getValue().concat(record));
	},

	getValue: function () {
		var records = this.store.getRange(), v = [];
		for (var i = 0, l = records.length; i < l; i++) {
			v.push(records[i].data);
		}
		return v;
	},

	markInvalid: function (msg) {
		return true;
	},

	clearInvalid: function () {
		return true;
	},

	isValid: function (preventMark) {
		return true;
	},

	validate: function () {
		return true;
	},

	createAttachBtn: function () {

		const uploadItems = [
			{
				text: t("Upload"),
				iconCls: 'ic-computer',
				scope: this,
				handler: function () {
					go.util.openFileDialog({
						multiple: true,
						directory: false,
						autoUpload: true,
						listeners: {
							upload: function (response) {
								this.addAttachment({
									blobId: response.blobId,
									name: response.name,
									attachment: true
								});
							},
							scope: this
						}
					});
				}

			}];


		if (go.Modules.isAvailable("legacy", "files")) {

			uploadItems.push({
				iconCls: 'ic-folder',
				text: t("Add from Group-Office", "email").replace('{product_name}', GO.settings.config.product_name),
				handler: function () {
					if (go.Modules.isAvailable("legacy", "files")) {
						GO.files.createSelectFileBrowser();
						GO.files.createBlobs = true;

						GO.selectFileBrowser.setFileClickHandler(function (blobs) {

							blobs.forEach(function (blob) {
								this.addAttachment({
									blobId: blob.blobId,
									name: blob.name,
									attachment: true
								});
							}, this);

							GO.selectFileBrowserWindow.hide();
						}, this, true);

						GO.selectFileBrowser.setFilesFilter('');
						GO.selectFileBrowser.setRootID(0, 0);
						GO.selectFileBrowserWindow.show();
					}
				},
				scope: this
			});
		}

		return new Ext.Button({
			iconCls: 'ic-attach-file',
			tooltip: t("Attach files"),
			menu: {
				items: uploadItems
			}
		});

	}

});

Ext.reg('attachmentfield', go.form.AttachmentsField);