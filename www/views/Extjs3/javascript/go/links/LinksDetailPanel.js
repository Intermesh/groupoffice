go.links.LinksDetailPanel = Ext.extend(Ext.Panel, {
	
	initComponent: function () {
		this.store = new go.data.Store({
			baseParams: {
				limit: 10
			},
			fields: ['id', 'to', 'toId', {name: 'createdAt', type: 'date'}, 'toEntity'],
			entityStore: go.Stores.get("community", "Link")
//			listeners: {
//				load: function () {
//					//panel.setVisible(store.getCount() > 0);
//				},
//				scope: this
//			}
		});
		
		var me = this;
		
		var tpl = new Ext.XTemplate('<div class="icons"><tpl for=".">\
		<tpl if="toEntity !== this.previousType(xindex)">\
			<h5>{[t(values.toEntity, go.entities[values.toEntity].module)]}</h5><hr>\
		</tpl>\
		{[this[values.toEntity] ? this[values.toEntity](values, xindex, xcount) : this.default(values, this.previousType(xindex), xindex, xcount)]}\
	</tpl><tpl if="this.getTotalCount() &gt; 0"><p class="more">{[this.getMoreStr()]}</p></tpl></div>', {

			previousType: function (xindex) {
				var previousIndex = xindex - 2;
				if (previousIndex < 0) {
					return false;
				}
				return me.store.getAt(previousIndex).data.toEntity;
			},
			getTotalCount: function () {
				return me.store.getTotalCount() - 10;
			},

			getMoreStr: function () {
				return t("{count} items more", "core").replace("{count}", this.getTotalCount());
			},
			default: function (values, previousType, xindex, xcount) {
				Ext.apply(values, {xindex: xindex, previousType: previousType, xcount: xcount});
				return (new Ext.XTemplate('<a>\
			<tpl if="toEntity !== previousType">\
				<i class="label entity {toEntity}" ext:qtip="{toEntity}"></i>\
			</tpl>\
			<tpl for="to">\
			<span>{name}</span>\
			<label>{[fm.date(parent.createdAt, go.User.dateTimeFormat)]}</label>\
			</tpl>\
		</a>')).apply(values);

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
			bbar: [
				new go.links.LinkToButton({
					itemId: "linkTo"
				})
			],
			items: new Ext.DataView({
				store: this.store,
				tpl: tpl,
				autoHeight: true,
				multiSelect: true,
				itemSelector: 'a',
				listeners: {
					scope: this,
					click: function (dv, index, node, e) {

						var record = this.store.getAt(index);
						var entity = go.entities[record.data.toEntity];

						if (!entity) {
							throw record.data.toEntity + " is not a registered entity";
						}
						entity.goto(record.data.toId);
					}
				}
			})

		});
		
		go.links.LinksDetailPanel.superclass.initComponent.call(this);
	},

	onLoad: function (dv) {
		var linkToBtn = this.getBottomToolbar().getComponent("linkTo");
		linkToBtn.detailView = dv;
		linkToBtn.setDisabled(dv.data.permissionLevel < GO.permissionLevels.write);

		this.store.load({
			params: {
				filter: [{
						entity: dv.entity ? dv.entity : dv.entityStore.entity.name, //dv.entity exists on old DetailView or display panels
						entityId: dv.model_id ? dv.model_id : dv.currentId //model_id is from old display panel
					}]
			}
		});
	}
});
