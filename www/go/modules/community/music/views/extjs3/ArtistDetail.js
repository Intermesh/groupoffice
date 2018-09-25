go.modules.community.music.ArtistDetail = Ext.extend(go.panels.DetailView, {
	
	// The entity store is connected. The detail view is automatically updated.
	entityStore: go.Stores.get("Artist"),
	
	//set to true to enable state saving
	stateful: false,
	stateId: 'music-contact-detail',
	
	initComponent: function () {
		this.tbar = this.initToolbar();

		Ext.apply(this, {
			// all items are updated automatically if they have a "tpl" (Ext.XTemplate) property or an "onLoad" function. The panel is passed as argument.
			items: [
				
				//Artist name component
				{
					cls: 'content',
					xtype: 'box',
					tpl: '<h3>{name}</h3>'
				}, 
				
				//Render the avatar
				{
					xtype: "box",
					cls: "content",
					tpl: new Ext.XTemplate('<div class="go-detail-view-avatar">\
<div class="avatar" style="{[this.getStyle(values.photo)]}"></div></div>', 
					{
						getCls: function (isOrganization) {
							return isOrganization ? "organization" : "";
						},
						getStyle: function (photoBlobId) {
							return photoBlobId ? 'background-image: url(' + go.Jmap.downloadUrl(photoBlobId) + ')"' : "";
						}
					})
				},
				
				// Albums component
				{
					collapsible: true,
					title: t("Albums"),
					xtype: "panel",
					
					//onLoad is called on each item. The DetailView is passed as argument
					onLoad : function(dv) {
						this.setVisible(dv.data.albums.length);
						if(!dv.data.albums.length) {							
							return;
						}
						
						if(!this.template) {
							this.template = new Ext.XTemplate('<div class="icons">\
					<tpl for=".">\
						<p class="s6"><tpl if="xindex == 1"><i class="icon label">album</i></tpl>\
							<span>{name}</span>\
							<label>{[GO.util.dateFormat(values.releaseDate)]} - {[go.Stores.get("Genre").get([values.genreId])[0].name]}</label>\
						</p>\
					</tpl>\
					</div>').compile();
						}
						
						//make sure genres are loaded before rendering the album template
						var ids = dv.data.albums.column('genreId');
						
						go.Stores.get("Genre").get(ids, function(genres) {
							this.update(this.template.apply(dv.data.albums));
						}, this);
					}
				}
			]
		});


		go.modules.community.music.ArtistDetail.superclass.initComponent.call(this);

	},

	onLoad: function () {

		// Enable edit button according to permission level.
		this.getTopToolbar().getComponent("edit").setDisabled(this.data.permissionLevel < GO.permissionLevels.write);
		this.deleteItem.setDisabled(this.data.permissionLevel < GO.permissionLevels.writeAndDelete);

		go.modules.community.music.ArtistDetail.superclass.onLoad.call(this);
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
					var dlg = new go.modules.community.music.ArtistDialog();
					dlg.show();
					dlg.load(this.data.id);
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
					}, 
					'-',
					this.deleteItem = new Ext.menu.Item({
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function () {
							Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function (btn) {
								if (btn != "yes") {
									return;
								}
								this.entityStore.set({destroy: [this.currentId]});
							}, this);
						},
						scope: this
					})

				]
			}]);

		var tbarCfg = {
			disabled: true,
			items: items
		};


		return new Ext.Toolbar(tbarCfg);


	}
});

