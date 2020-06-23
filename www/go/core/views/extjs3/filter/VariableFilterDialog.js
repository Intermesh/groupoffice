go.filter.VariableFilterDialog = Ext.extend(go.form.Dialog, {
	title: t('Input field'),
	entityStore: "EntityFilter",
	entity: null,
	autoScroll: true,
	height: dp(600),
	width: dp(1000),
	initFormItems: function () {

		this.addPanel(new go.permissions.SharePanel());

		this.nameCombo = new go.form.ComboBox({
			fieldLabel: t("Filter"),
			hiddenName: "name",
			store: new Ext.data.ArrayStore({
				fields: ['display', 'value'],
				id: 'value'
			}),
			valueField: 'value',
			displayField: 'display',
			mode: 'local',
			triggerAction: 'all',
			editable: true,
			selectOnFocus: true,
			forceSelection: true,
			anchor: '100%'
		});
		var f, filters = go.Entities.get(this.entity).filters;

		for ( var name in filters) {
			f = filters[name];
			this.nameCombo.store.loadData([[f.title, f.name], ], true);
		}

		return [{
				xtype: 'fieldset',
				items: [{
						xtype: "hidden",
						name: 'entity',
						value: this.entity
				},{
					xtype: "hidden",
					name: 'type',
					value: "variable"
				},
					this.nameCombo
					]
		}];
	}
});
