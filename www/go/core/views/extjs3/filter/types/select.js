go.filter.types.select = Ext.extend(go.form.ComboBox, {
	flex: 1,
	options: null,
	hiddenName: 'value',
	initComponent: function () {
		
		Ext.apply(this, {
				submit: false,
				hideLabel: true,				
				store: new Ext.data.JsonStore({
					fields: ['value', 'title'],		
					root: "data",
					data: {data: this.options.options}
				}),
				valueField: 'value',
				displayField: 'title',
				mode: 'local',
				triggerAction: 'all',
				editable: false,
				selectOnFocus: true,
				forceSelection: true				
			});			
			
		go.filter.types.select.superclass.initComponent.call(this);
	}	
});


