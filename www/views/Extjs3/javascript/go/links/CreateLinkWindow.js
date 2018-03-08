go.links.CreateLinkWindow = Ext.extend(go.Window, {
	entity: null,
	entityId: null,
	stateId: "go-create-link-windows",

	search: function (v) {
		this.store.load({
			params: {
				filter: [{
						q: v
					}]
			}
		});
	},

	initComponent: function () {

		var me = this;

		this.searchField = new Ext.form.TwinTriggerField({
			emptyText: t("Search"),
			hideLabel: true,
			xtype: "twintrigger",
			anchor: "100%",
			validationEvent: false,
			validateOnBlur: false,
			trigger1Class: 'x-form-search-trigger',
			trigger2Class: 'x-form-clear-trigger',
//							enableKeyEvents: true,

			onTrigger1Click: function () {
				me.search(this.getValue());
			},
			onTrigger2Click: function () {
				this.setValue("");
				me.search("");
			},
			listeners: {
				specialkey: function (field, e) {
					if (e.getKey() == Ext.EventObject.ENTER) {
						this.search(field.getValue());
					}
				},
				scope: this
			}
		});

		var search = new Ext.Panel({
			layout: "form",
			region: "north",
			autoHeight: true,
			items: [{
					xtype: "fieldset",
					items: [this.searchField]
				}]
		});

		this.store = new go.data.Store({
			autoDestroy: true,
			fields: ['id', 'entityId', 'entity', 'name', 'description', {name: 'modifiedAt', type: 'date'}],
			entityStore: go.stores.Search,
			autoLoad: true
		});

		this.grid = new go.grid.GridPanel({
			cls: 'go-search-grid',
			region: "center",
			listeners: {
				dblclick: function () {
					this.link();
				},
				scope: this
			},
			store: this.store,
			columns: [
				{
					id: 'name',
					header: t('Name'),
					width: 75,
					sortable: true,
					dataIndex: 'name',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						return '<i class="entity ' + record.data.entity + '"></i>' + value;
					}
				}, {

					header: t('Type'),
					width: 75,
					sortable: false,
					dataIndex: 'entity'
				},
				{
					id: 'modifiedAt',
					header: t('Modified at'),
					width: 160,
					sortable: true,
					dataIndex: 'modifiedAt',
					renderer: Ext.util.Format.dateRenderer(go.User.dateTimeFormat)
				}
			],
			autoExpandColumn: 'name'
		});

		Ext.apply(this, {
			title: t("Create link", "links"),
			width: 600,
			height: 600,
			layout: 'border',
			items: [search, this.grid],
			listeners: {
				render: function () {
					//this.store.load();
				},
				scope: this
			},
			buttons: [{
					text: t("Ok"),
					handler: function () {
						this.link();
					},
					scope: this
				}
			]
		});



		go.links.CreateLinkWindow.superclass.initComponent.call(this);
	},
	
	focus : function() {
		this.searchField.focus();
	},

	link: function () {
		var selections = this.grid.getSelectionModel().getSelections();
		var me = this;
		var links = [];

		selections.forEach(function (record) {
			var link = {
				fromEntity: me.entity,
				fromId: me.entityId,
				toEntity: record.get('entity'),
				toId: record.get('entityId')
			}

			links.push(link);
		});

		go.stores.Link.set({
			create: links
		}, function () {
			me.close();
		});
	}
});


