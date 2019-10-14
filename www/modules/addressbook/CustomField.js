Ext.onReady(function() {
	GO.customfields.dataTypes["GO\\Addressbook\\Customfieldtype\\Contact"]={
		label : t("Contact", "addressbook"),
		getFormField : function(customfield, config){

			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

			delete f.name;
			
			return Ext.apply(f, {
				xtype: 'selectcontact',
				noUserContacts:true,
				idValuePair:true,
				hiddenName: 'customFields.' + customfield.databaseName,
				forceSelection:true,				
				valueField:'cf',
				customfieldId: customfield.id
			});
		}
	}
	
	GO.customfields.dataTypes["GO\\Addressbook\\Customfieldtype\\Company"]={
		label : t("Company", "addressbook"),
		getFormField : function(customfield, config){

			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

			delete f.name;

			return Ext.apply(f, {
				xtype: 'selectcompany',
				idValuePair:true,
				hiddenName: 'customFields.' + customfield.databaseName,
				forceSelection:true,				
				valueField:'cf',
				customfieldId: customfield.id
			});
		}
	}
});