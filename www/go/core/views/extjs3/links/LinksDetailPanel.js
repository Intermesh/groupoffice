/* global Ext, go */


go.links.DetailPanel = Ext.extend(Ext.Panel, {
	cls: 'go-links-detail',
	limit: 5,

	initComponent: function () {
		var store = this.store = new go.data.Store({
			baseParams: {
				limit: this.limit,
				position: 0,
				calculateTotal: false,
				calculateHasMore: true
			},
			filters: {
				toEntity: {entities: [{name: this.link.entity, filter: this.link.filter}]}
			},
			fields: [
				'id', 
				{name: "to", type: "relation"},
				'description',
				'toId', 
				'toSearchId',
				{name: 'createdAt', type: 'date'}, 
				'toEntity',
				'data'
			],
			entityStore: "Link",
			listeners: {
				datachanged: function () {
					this.setVisible(this.store.getCount() > 0);

					if(!this.origTitle) {
						this.origTitle = this.title;
					}
					var count = this.store.getTotalCount();
					if(this.store.hasMore) {
						count += '+';
					}
					var badge = "<span class='badge'>" + count + "</span>";
					this.setTitle(this.origTitle + badge);
				},
				scope: this
			}
		});
		
		
		var tpl = this.getTemplate();
		
		
		Ext.apply(this, {
			listeners: {
				added: function(me, dv, index) {
					this.stateId = 'go-links-' + (dv.entity ? dv.entity : dv.entityStore.entity.name) + "-" + this.link.entity;
					this.initState();
				},
				expand: function() {
					this.doLayout();
				},
				scope: this
			},
//			header: false,
			collapsible: true,
			titleCollapse: true,
			hidden: true,
			hideMode: "offsets",
			title: this.link.title,
			items: [this.dataView = new Ext.DataView({
				store: this.store,
				tpl: tpl,
				autoHeight: true,
				multiSelect: true,
				itemSelector: 'p',
				listeners: {
					scope: this,
					containerclick: function(dv, e) {

						if(e.target.classList.contains("show-more")) {
							this.store.load({
								params: {
									position: this.store.getCount(),
								},
								add: true,
								callback: function() {
									this.dataView.refresh();
								},
								scope: this
							});
						}
					},
					click: function (dv, index, node, e) {


						var record = this.store.getAt(index);

						if(e.target.tagName === "I" && e.target.innerHTML == 'more_vert'){
							this.showLinkMoreMenu(node,e, record);
						} else if(go.Entities.get(record.data.toEntity).links[0].linkDetail){
							var record = this.store.getById(node.getAttribute('data-id'));
							var win = new go.links.LinkDetailWindow({
								entity: record.data.toEntity
							});

							win.load(record.data.toId);
						} else
						{
							go.Entities.get(record.data.toEntity).goto(record.data.toId);
						}
					}
				}
			})]

		});
		
		go.links.DetailPanel.superclass.initComponent.call(this);
	},

	getTemplate: function() {
		const store = this.store;
		return new Ext.XTemplate('<div class="icons"><tpl for=".">\
				<p data-id="{id}" class="s12">\
				<tpl if="xindex === 1">\
					<i class="label ' + this.link.iconCls + '" ext:qtip="{toEntity}"></i>\
				</tpl>\
				<tpl for="to">\
					<a>{name} <tpl if="parent.data && parent.data.has_attachments "><i class="icon">attachment</i></tpl></a>\
					<small class="go-top-right" title="{[go.util.Format.dateTime(values.modifiedAt)]}" style="cursor:pointer">{[go.util.Format.userDateTime(values.modifiedAt)]}</small>\
					<label>{description}</label>\
				</tpl>\
				{[this.getLinkDescription(values)]}\
				<a class="right show-on-hover"><i class="icon">more_vert</i></a>\
			</p>\
		</tpl>\
		{[this.printMore(values)]}\
		</div>', {
			getLinkDescription: function(values) {
				if(values.description && values.description.length > 0) {
					return '<small>'+Ext.util.Format.htmlEncode(values.description)+'</small>';
				}
				return "";
			},
			printMore : function(values) {
				if(store.hasMore) {
					return "<a class=\"show-more\">" + t("Show more...") + "</a>";
				} else
				{
					return "";
				}
			}
		});
	},

	showLinkMoreMenu: function (node, e, record) {
		if (!this.linkMoreMenu) {
			this.linkMoreMenu = new Ext.menu.Menu({
				items: [{
					itemId: "edit",
					iconCls: 'ic-edit',
					text: t("Edit"),
					handler: function () {
						var dlg = new go.links.EditLinkDialog();
						dlg.load(this.linkMoreMenu.record.id).show();
					},
					scope: this
				}, {
					itemId: "delete",
					iconCls: "ic-delete",
					text: t("Unlink"),
					handler: function () {
						Ext.MessageBox.confirm(t("Delete"), t("Are you sure you want to unlink this item?"), function (btn) {
							if (btn == "yes") {
								go.Db.store("Link").set({
									destroy: [this.linkMoreMenu.record.id]
								});
							}
						}, this);
					},
					scope: this
				}, {
					itemId: "open",
					iconCls: "ic-open-in-new",
					text: t("Open"),
					handler: function () {
						var win = new go.links.LinkDetailWindow({
							entity: this.linkMoreMenu.record.data.toEntity
						});

						win.load(this.linkMoreMenu.record.data.toId);
					},
					scope: this
				}]
			});
		}
		this.linkMoreMenu.data = node.attributes.data;
		this.linkMoreMenu.record = record;
		this.linkMoreMenu.showAt(e.getXY());
	},
	
	onLoad: async function (dv) {

		const entity = dv.entity ? dv.entity : dv.entityStore.entity.name, //dv.entity exists on old DetailView or display panels
		entityId =dv.model_id ? dv.model_id : dv.currentId //model_id is from old display panel

		const f = this.store.getFilter('fromEntity');

		if(f && f.entity == entity && f.entityId == entityId) {
			return;
		}

		this.detailView = dv;

		this.hide();
		
		this.store.setFilter('fromEntity', {
			entity,
			entityId
		});

		this.store.baseParams.position = 0;
		return this.store.load();
	}
});

go.links.getDetailPanels = function(sortFn) {
	
	var panels = [];
	
	go.Entities.getLinkConfigs().forEach(function (e) {
		if(e.linkDetailCards) {

			const entity = go.Entities.get(e.entity);

			go.Translate.setModule(entity.package, entity.module);

			var clss = e.linkDetailCards();
			if(!Ext.isArray(clss)) {
				clss = [clss];
			}
			panels = panels.concat(clss);
		} else {
			panels.push(new go.links.DetailPanel({
				link: e
			}));
		}
	});

	if(sortFn) {		
		panels.sort(sortFn);
	}
	
	return panels;
};
