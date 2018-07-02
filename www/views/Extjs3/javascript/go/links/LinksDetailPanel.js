go.links.LinksDetailPanel = Ext.extend(Ext.Panel, {
	addButtonItems : null,
	cls: 'go-links-detail',
	initComponent: function () {
		this.store = new go.data.Store({
//			baseParams: {
//				limit: 20
//			},
			fields: ['id', 'to', 'toId', {name: 'createdAt', type: 'date'}, 'toEntity'],
			entityStore: go.Stores.get("Link")
//			listeners: {
//				load: function () {
//					//panel.setVisible(store.getCount() > 0);
//				},
//				scope: this
//			}
		});
		
		var me = this;
		
		
		me.entities = {};	
		
		
//		this.tools = [{
//				id:"add",
//				tooltip: t("Add link")
//		}];

		var max = 4;
		
		var tpl = new Ext.XTemplate('{[this.init()]}<div class="icons"><tpl for=".">\
		<tpl if="toEntity !== this.previousType(xindex)">\
			<h5>{[t(values.toEntity, go.Entities.get(values.toEntity).module)]}<span class="count">{[this.countEntity(values.toEntity)]}</span></h5><hr>\
		</tpl>\
		<tpl if="this.shouldPrint(values)">\
		{[this[values.toEntity] ? this[values.toEntity](values, xindex, xcount) : this.default(values, this.previousType(xindex), xindex, xcount)]}\
		</tpl>\
		{[this.printMore(values)]}\
	</tpl>\
	</div>', {
			
			
			init : function() {
				
				for(var key in me.entities) {
					me.entities[key].count = 0;
					me.entities[key].showMoreLinkPrinted = false;
				}
							
				return "";
			},
			
			printMore : function(values) {
				if(me.entities[values.toEntity].showMoreLinkPrinted || me.entities[values.toEntity].showMore || me.entities[values.toEntity].count < max){
					return "";
				}
				
				me.entities[values.toEntity].showMoreLinkPrinted = true;
				
				return "<a class=\"show-more\" data-entity="+values.toEntity+">" + t("Show more...") + "</a>";
			},
			
			//<tpl if="this.getTotalCount() &gt; 0"><a class="more">{[this.getMoreStr()]}</a></tpl></div>'
			shouldPrint : function(values) {
				if(!me.entities[values.toEntity]) {
					me.entities[values.toEntity] = {count: 0, showMore: false, showMoreLinkPrinted: false};					
				}
				
				
				me.entities[values.toEntity].count++;
				
				if(!me.entities[values.toEntity].showMore && me.entities[values.toEntity].count > max) {
					return false;
				}
				
				return true;
			},

			previousType: function (xindex) {
				var previousIndex = xindex - 2;
				if (previousIndex < 0) {
					return false;
				}
				return me.store.getAt(previousIndex).data.toEntity;
			},
			countEntity : function(entity) {
				var count = 0;
				me.store.each(function(r) {
					if(r.data.toEntity == entity) {
						count++;
					}
				});

				return count;
			},
			
			
//			getTotalCount: function () {
//				return me.store.getTotalCount() - 10;
//			},

//			getMoreStr: function () {
//				return t("{count} items more", "core").replace("{count}", this.getTotalCount());
//			},
			default: function (values, previousType, xindex, xcount) {
				Ext.apply(values, {xindex: xindex, previousType: previousType, xcount: xcount});
				return (new Ext.XTemplate('<p data-id="{id}">\
			<tpl if="toEntity !== previousType">\
				<i class="label entity {toEntity}" ext:qtip="{toEntity}"></i>\
			</tpl>\
			<tpl for="to">\
			<a>{name}</a>\
			<label>{[GO.util.dateFormat(parent.createdAt)]}</label>\
			<a class="right show-on-hover"><i class="icon">delete</i></a>\
			</tpl>\
		</p>'
		)).apply(values);

//			Event: function (values, xindex, xcount) {
//				Ext.apply(values, {xindex: xindex, xcount: xcount});
//				return (new Ext.XTemplate('<a>\
//			bam {name}\
//		</a>'))

//				.apply(values);
			}
		});



		Ext.apply(this, {
			listeners: {
				added: function(me, dv, index) {
					this.stateId = 'go-links-' + (dv.entity ? dv.entity : dv.entityStore.entity.name);
				},
				scope: this
			},
			collapsible: true,
			titleCollapse: true,
			title: t('Links'),
			items: new Ext.DataView({
				store: this.store,
				tpl: tpl,
				autoHeight: true,
				multiSelect: true,
				itemSelector: 'p',
				listeners: {
					scope: this,
					containerclick: function(dv, e) {
		
						if(e.target.classList.contains("show-more")) {
							var entity = e.target.getAttribute('data-entity');
							this.entities[entity].showMore = true;
							dv.refresh();
						}
					},
					click: function (dv, index, node, e) {
						
						var record = this.store.getAt(index);
						
						if(e.target.tagName == "I") {							
							this.delete(record);
						} else 
						{
							var record = this.store.getById(node.getAttribute('data-id'));
							
							var entity = go.Entities.get(record.data.toEntity);

							if (!entity) {
								throw record.data.toEntity + " is not a registered entity";
							}
							entity.goto(record.data.toId);
						}
					}
				}
			})

		});
		
		go.links.LinksDetailPanel.superclass.initComponent.call(this);
		
		this.addButtonItems = this.initAddButtonItems();
	},
	
	delete : function(record) {
		Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function(btn) {
			if(btn == 'yes') {
				go.Stores.get("Link").set({
					destroy: [record.id]
				});
			}
		}, this)
		
	},

	onLoad: function (dv) {
		
		this.detailView = dv;	
		
		
		this.entities = {};	

		this.store.load({
			params: {
				filter: {
						entity: dv.entity ? dv.entity : dv.entityStore.entity.name, //dv.entity exists on old DetailView or display panels
						entityId: dv.model_id ? dv.model_id : dv.currentId //model_id is from old display panel
					}
			}
		});
	},
	
	getEntityId : function() {
		return this.detailView.currentId || this.detailView.model_id; //for old display panel
	},
	
	getEntity : function() {
		return this.detailView.entity || this.detailView.entityStore.entity.name; //entity must be set on old panels
	},
	
	initAddButtonItems: function () {
		var items = [
			{
				iconCls: 'ic-link',
				text: t("Link", "links"),
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
					
					if(window.setLinkEntity) {
						window.on('show', function() {
								window.setLinkEntity({
									entity: this.getEntity(),
									data: this.detailView.data
								});
							}, this, {single: true});
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
							//in this case it's a go.form.Dialog							
							link.toId = entity.id;
						}
						
						go.Stores.get("Link").set({
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
		
		
//		items.push("-");
//		
//		items.push({
//			iconCls: "btn-folder",
//			text: t("Browse"),
//			handler: function() {
//				var lb = new go.links.LinkBrowser({
//					entity: this.getEntity(),
//					entityId: this.getEntityId()
//				});
//				
//				lb.show();
//			},
//			scope: this
//		})

		return items;
	}
});
