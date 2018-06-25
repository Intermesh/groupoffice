Ext.onReady(function() {
	GO.customfields.nonGridTypes.push('file');
	GO.customfields.dataTypes["GO\\Files\\Customfieldtype\\File"]={
		label : t("File", "files"),
		getFormField : function(customfield, config){
			return {
				xtype: 'selectfile',
       	fieldLabel: customfield.name,
        name:customfield.dataname,
        anchor:'-20'
			}
		}
	}

});
