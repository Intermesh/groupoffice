

	go.links.EntityCombo = Ext.extend(go.form.ComboBox, {
		anchor: "100%",
		fieldLabel: t("Entity"),
		hiddenName: "entity",
		mode: "local",
		store: {
			xtype: "arraystore",
			fields: ['id', 'entity', 'name', 'filter', 'iconCls'],
			idIndex: 0
		},
		displayField: "name",
		valueField: "entity",
		triggerAction: "all",
		forceSelection: true,
		initComponent: function() {


			this.supr().initComponent.call(this);

			const data = [], allEntities = go.Entities.getLinkConfigs();

			let id;

			allEntities.forEach(function (link) {
				id = link.entity;
				if (link.filter) {
					id += "-" + link.filter;
				} else {
					link.filter = null;
				}
				data.push([id, link.entity, link.title, link.filter, link.iconCls]);
			});

			this.store.loadData(data);
		}

	});


