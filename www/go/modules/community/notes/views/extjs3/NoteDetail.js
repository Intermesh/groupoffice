/* global go, Ext, GO, mcrypt */

go.modules.community.notes.NoteDetail = Ext.extend(go.detail.Panel, {
	
	entityStore: "Note",

	stateId: 'no-notes-detail',

	initComponent: function () {


		this.tbar = this.initToolbar();

		Ext.apply(this, {
			items: [{
					xtype: 'readmore',
					onLoad: function (detailView) {
						this.setText("<h3>" + Ext.util.Format.htmlEncode(detailView.data.name) + "</h3><div class='go-html-formatted'>" + detailView.data.content + "</div>");
					}
				}
			]
		});
		

		go.modules.community.notes.NoteDetail.superclass.initComponent.call(this);

		this.addCustomFields();
		this.addLinks();
		this.addComments();
		this.addFiles();

		this.add(new go.detail.CreateModifyPanel());
	},

	decrypt: function () {

		if (!this.data.content || this.data.content.substring(0, 8) !== "{GOCRYPT") {
			return;
		}
		
		var key = prompt("Enter password to decrypt");

		if(this.data.content.substring(0, 9) === "{GOCRYPT}") {

			var msg = window.atob(this.data.content.substring(9));

			var iv = (msg.substring(0, 32));			 // extract iv
			var body = (msg.substring(32, msg.length - 32));	 //extract ciphertext
			var serialized = mcrypt.Decrypt(body, iv, key, "rijndael-256", "ctr");
			//result should be a serialized sting by PHP
			var match = serialized.match(/.*"([\s\S]*)"/);
			if (!match) {
				alert("Incorrect password!");
				//this.data.content = "Encrypted text";
				return;
			}

			this.data.content = Ext.util.Format.nl2br(match[1]);
		} else
		{
			//new encryption
			//this.data.content = "Decrypting...";
			
			go.Jmap.request({
				method: "Note/decrypt",
				params: {
					id: this.data.id,
					password: key
				},
				callback: function(options, success, response) {
					if(success) {
						console.log(response);
						this.data.content = response.content;
						
						this.items.item(0).onLoad(this);
					} else
					{
						Ext.MessageBox.alert(t("Error"), t("Invalid password"));
					}
				},
				scope: this
			});
		}
		
	},

	onLoad: function () {

		this.decrypt();

		this.getTopToolbar().getComponent("edit").setDisabled(this.data.permissionLevel < go.permissionLevels.write);
		this.deleteItem.setDisabled(this.data.permissionLevel < go.permissionLevels.writeAndDelete);

		go.modules.community.notes.NoteDetail.superclass.onLoad.call(this);
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
					var noteEdit = new go.modules.community.notes.NoteDialog();
					noteEdit.load(this.data.id).show();
				},
				scope: this
			},

			new go.detail.addButton({
				detailView: this
			}),

			this.moreMenu = {
				iconCls: 'ic-more-vert',
				menu: [
					{
						xtype: "linkbrowsermenuitem"
					},
					'-',
					{
						iconCls: "btn-print",
						text: t("Print"),
						handler: function () {
							this.body.print({title: this.data.name});
						},
						scope: this
					}, "-",
					this.deleteItem = new Ext.menu.Item({
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function () {
							Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function (btn) {
								if (btn !== "yes") {
									return;
								}
								this.entityStore.set({destroy: [this.currentId]});
							}, this);
						},
						scope: this
					})
				]
			}]);
		
		if(go.Modules.isAvailable("legacy", "files")) {
			this.moreMenu.menu.splice(1,0,{
				xtype: "filebrowsermenuitem"
			});
		}

		var tbarCfg = {
			disabled: true,
			items: items
		};


		return new Ext.Toolbar(tbarCfg);


	}
});
