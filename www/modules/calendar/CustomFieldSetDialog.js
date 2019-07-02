GO.calendar.CustomFieldSetDialog = Ext.extend(go.customfields.FieldSetDialog, {	

	initComponent: function() {
		GO.calendar.CustomFieldSetDialog.superclass.initComponent.call(this);
		
		this.formPanel.on("beforesubmit", function(entityPanel, values) {
			var enableFilter = entityPanel.getForm().findField("enableFilter").getValue();

			if (!enableFilter) {
				if(values.filter) {
					delete values.filter.group_id;
				} else
				{
					values.filter = {};
				}
			}
		}, this);
	},
	
	initFormItems: function () {
		var items = GO.calendar.CustomFieldSetDialog.superclass.initFormItems.call(this);
		
		this.groupStore = new GO.data.JsonStore({
				url: GO.url("calendar/group/store"),
				fields:['id','name','user_name','fields','acl_id'],
				baseParams: {'limit': 0}
			});

		items[0].items = items[0].items.concat([
			{
				xtype: "checkbox",
				name: "enableFilter",
				boxLabel: t("Only show this field set for selected calendar groups"),
				hideLabel: true,
				submit: false,
				listeners: {
					check: function (f, checked) {
						this.formPanel.getForm().findField("filter.group_id").setDisabled(!checked);
					},
					scope: this
				}
			},
			{
				anchor: '100%',
				disabled: true,
				xtype: "chips",
				comboStore: this.groupStore,
				valueField: 'id',
				displayField: "name",
				name: "filter.group_id",
				fieldLabel: t("Groups")
			}
		]);		
		
		return items;
	},
	
	load: function (id) {
		
		//templatestore must be loaded before form loads for chips component
		if(!this.groupStore.loaded) {
			
			this.loading = true;
			
			this.groupStore.load({
				callback: function() {
					GO.calendar.CustomFieldSetDialog.superclass.load.call(this, id);
				},
				scope: this
			});
		} else
		{
			GO.calendar.CustomFieldSetDialog.superclass.load.call(this, id);
		}
		
		return this;
	},
	
	
	
	show: function () {
		
		var p = arguments;
		
		//templatestore must be loaded before form loads for chips component
		if(!this.groupStore.loaded && !this.loading) {
			
			this.loading = true;
			
			this.groupStore.load({
				callback: function() {
					GO.calendar.CustomFieldSetDialog.superclass.show.apply(this, p);
				},
				scope: this
			});
		} else
		{
			GO.calendar.CustomFieldSetDialog.superclass.show.apply(this, p);
		}
		
		return this;
	},

	onLoad: function () {
		this.formPanel.getForm().findField("enableFilter").setValue(!!this.formPanel.entity.filter.group_id);
		
		return GO.calendar.CustomFieldSetDialog.superclass.onLoad.call(this);
	}
});

