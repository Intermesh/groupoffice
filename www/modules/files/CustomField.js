GO.moduleManager.onModuleReady('customfields', function(){
	GO.customfields.nonGridTypes.push('file');
	GO.customfields.dataTypes["GO\\Files\\Customfieldtype\\File"]={
		label : GO.files.lang.file,
		getFormField : function(customfield, config){
			return {
				xtype: 'selectfile',
       	fieldLabel: customfield.name,
        name:customfield.dataname,
        anchor:'-20'
			}
		}
	}

}, this);