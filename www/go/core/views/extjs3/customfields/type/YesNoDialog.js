 go.customfields.type.YesNoDialog = Ext.extend(go.customfields.FieldDialog, {
	 initFormItems : function() {		 
		 var items =  go.customfields.type.YesNoDialog.superclass.initFormItems.call(this);


		 var store = new Ext.data.SimpleStore({
			 id: 'id',
			 fields: ['id', 'text'],
			 data: [
				 [1, t("Yes")],
				 [-1, t("No")]],
			 remoteSort: false
		 });

		 
		 items[0].items  = items[0].items.concat([ {
			 xtype: 'comboboxreset',
			 fieldLabel: t("Default"),
			 store: store,
			 valueField: 'id',
			 displayField: 'text',
			 hiddenName: "default",
			 mode: 'local',
			 editable: false,
			 triggerAction: 'all',
			 selectOnFocus: true,
			 forceSelection: false
		 }]);
		
		 return items;
	 }
 });
