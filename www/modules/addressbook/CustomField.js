GO.moduleManager.onModuleReady('customfields', function(){
	//GO.customfields.nonGridTypes.push('contact');
	GO.customfields.dataTypes["GO\\Addressbook\\Customfieldtype\\Contact"]={
		label : GO.addressbook.lang.contact,
		getFormField : function(customfield, config){

			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

			delete f.name;
			
			return Ext.apply(f, {
				xtype: 'selectcontact',
				noUserContacts:true,
				idValuePair:true,
				hiddenName:customfield.dataname,
				forceSelection:true,				
				valueField:'cf',
				customfieldId: customfield.dataname
			});
		}
	}
	
	GO.customfields.dataTypes["GO\\Addressbook\\Customfieldtype\\Company"]={
		label : GO.addressbook.lang.company,
		getFormField : function(customfield, config){

			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

			delete f.name;

			return Ext.apply(f, {
				xtype: 'selectcompany',
				idValuePair:true,
				hiddenName:customfield.dataname,
				forceSelection:true,				
				valueField:'cf',
				customfieldId: customfield.dataname
			});
		}
	}

}, this);