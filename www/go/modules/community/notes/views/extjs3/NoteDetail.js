go.modules.notes.NoteDetail = Ext.extend(go.panels.DetailView, {
	entityStore: go.stores.Note,
	stateId: 'no-notes-detail',

	//model_name: "go\\modules\\community\\notes\\model\\Note", //only for backwards compatibility with older panels.

	initComponent: function () {


		this.tbar = this.initToolbar();

		Ext.apply(this, {
			items: [{
					xtype: 'readmore',
					onLoad: function (detailView) {
						this.setText("<h3>" + detailView.data.name + "</h3>" + detailView.data.content);
					}
				}
			]
		});


		go.modules.notes.NoteDetail.superclass.initComponent.call(this);

		go.CustomFields.addDetailPanels(this);

		this.add(new go.links.LinksDetailPanel());

		if (go.Modules.isAvailable("community", "comments")) {
			this.add(new go.modules.comments.CommentsDetailPanel());
		}

		if (go.Modules.isAvailable("community", "files")) {
			this.add(new go.modules.files.FilesDetailPanel());
		}
	},

	decrypt: function () {

		if (!this.data.content || this.data.content.substring(0, 9) != "{GOCRYPT}") {
			return;
		}
		var key = prompt("Enter password to decrypt");
		var msg = window.atob(this.data.content.substring(9));

		var iv = (msg.substring(0, 32));			 // extract iv
		var body = (msg.substring(32, msg.length - 32));	 //extract ciphertext
		var serialized = mcrypt.Decrypt(body, iv, key, "rijndael-256", "ctr");
		console.log(serialized);
		//result should be a serialized sting by PHP
		var match = serialized.match(/.*"([\s\S]*)"/);
		if (!match) {
			alert("Incorrect password!");
			//this.data.content = "Encrypted text";
			return;
		}

		this.data.content = Ext.util.Format.nl2br(match[1]);
	},

	onLoad: function () {

		this.decrypt();

		this.getTopToolbar().getComponent("edit").setDisabled(this.data.permissionLevel < GO.permissionLevels.write);

		go.modules.notes.NoteDetail.superclass.onLoad.call(this);
	},

	initToolbar: function () {



		var items = this.tbar || [];
		
		items = items.concat([
				'->',
				{
					itemId: "edit",
					iconCls: 'ic-edit',
					tooltip: t("Edit"),
					handler: function (btn, e) {
						var noteEdit = new go.modules.notes.NoteForm();
						noteEdit.show();
						noteEdit.load(this.data.id);
					},
					scope: this
				},

				{
					iconCls: 'ic-more-vert',
					menu: [
						{
							iconCls: "btn-print",
							text: t("Print"),
							handler: function () {
								this.body.print({title: this.data.name});
							},
							scope: this
						}

					]
				}]);

		var tbarCfg = {
			disabled: true,
			items: items
		};


		return new Ext.Toolbar(tbarCfg);


	}
});