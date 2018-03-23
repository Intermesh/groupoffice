go.links.LinkToButton = Ext.extend(Ext.Button, {

	detailView: null,

	initComponent: function () {
		var menu = new Ext.menu.Menu({
			items: this.initMenuItems()
		});

		Ext.applyIf(this, {
//			iconCls: 'btn-link',
			text: t("Add"),
			menu: menu
		});

		go.links.LinkToButton.superclass.initComponent.call(this);
	},
	
	getEntityId : function() {
		return this.detailView.currentId || this.detailView.model_id; //for old display panel
	},
	
	getEntity : function() {
		return this.detailView.entity || this.detailView.entityStore.entity.name; //entity must be set on old panels
	},

	initMenuItems: function () {
		var items = [
			{
				iconCls: 'btn-search',
				text: t("Existing item", "links"),
				handler: function () {
					var linkWindow = new go.links.CreateLinkWindow({
						entityId: this.getEntityId(),
						entity: this.getEntity()
					}
					);
					linkWindow.show();
				},
				scope: this
			}
		];

		if (!go.Links.linkToWindows.length) {
			return items;
		}

		items.push("-");		

		var me = this;
		
		go.Links.linkToWindows.sort(function(a, b) {
			return a.title.localeCompare(b.title);
		})
		
		go.Links.linkToWindows.forEach(function (i) {			
			
			items.push({
				iconCls: 'entity ' + i.entity,
				text: i.title,
				handler: function () {
					var window = i.openWindowFunction.call(i.scope, this.getEntity(), this.getEntityId());					
					
					if(!window) {
						return;
					}
					
					if(!window.isVisible()) {
						window.show();
					}
					
					window.on('save', function (window, entity) {						
						
						//hack for event dialog because save event is different
						if(i.entity == "Event") {
							entity = arguments[2].result.id;
						}
						
						var link = {
									fromEntity: this.getEntity(),
									fromId: this.getEntityId(),
									toEntity: i.entity,
									toId: null
								}
						
						if(!Ext.isObject(entity)) {
							//old modules just pass ID
							link.toId = entity;
						} else
						{
							//in this case it's a go.form.FormWindow							
							link.toId = entity.id;
						}
						
						go.stores.Link.set({
							create: [link]
						}, function(options, success, result) {
							if(result.notCreated) {
								throw "Could not create link";
							}
						});

					}, this, {single: true});

				},
				scope: me
			});
		});
		
		
		items.push("-");
		
		items.push({
			iconCls: "btn-folder",
			text: t("Browse"),
			handler: function() {
				var lb = new go.links.LinkBrowser({
					entity: this.getEntity(),
					entityId: this.getEntityId()
				});
				
				lb.show();
			},
			scope: this
		})

		return items;
	}
});
