/* global go, Ext, GO, mcrypt */

go.modules.community.notes.NoteDetail = Ext.extend(go.detail.Panel, {
	
	entityStore: "Note",

	stateId: 'no-notes-detail',

	initComponent: function () {


		this.tbar = this.initToolbar();

		Ext.apply(this, {
			items: [{
				title: t("Note"),
				onLoad: function (detailView) {
					this.setTitle(Ext.util.Format.htmlEncode(detailView.data.name));
					this.items.itemAt(0).setText("<div class='go-html-formatted'>" + detailView.data.content + "</div>");
				},
				items: [{
					xtype: 'readmore'
				}]
			}]
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

		var data = this.data.content, me = this;
		this.data.content = t("Encrypted data");
		go.modules.community.notes.Decrypter.decrypt(data).then(function(text) {
			me.data.content = text;
			me.items.item(0).onLoad(me);
		}).catch(function(){});


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
			items.splice(items.length - 1, 0,{
				xtype: "detailfilebrowserbutton"
			});
		}

		var tbarCfg = {
			disabled: true,
			items: items
		};


		return new Ext.Toolbar(tbarCfg);


	}
});
