
/**
 * 
 * {
			name: field.databaseName,
			type: "select",
			multiple: true,
			title: field.name,
			options: [{
					value: null,
					title: t("Not set")
			},{
					value: 1,
					title: t("Yes")
			},{
					value: -1,
					title: t("No")
			}]
		}
 */
go.filter.types.select = Ext.extend(go.form.ComboBox, {
	flex: 1,
	/**
	 * Filter definition
	 * {
					name: 'text', //Filter name
					type: "string", //Sting type of go.filters.type or a full class name
					multiple: false, // nly applies to query field parsing. You can use name: Value1,Value2 nad it will turn into an array for an OR group
					title: "Query",
					customfield: model //When it's a custom field
				},
	 */
	filter: null,
	hiddenName: 'value',
	initComponent: function () {
		
		Ext.apply(this, {				
				hideLabel: true,				
				store: new Ext.data.JsonStore({
					fields: ['value', 'title'],		
					root: "data",
					data: {data: this.filter.options}
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


