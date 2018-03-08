GO.moduleManager.onModuleReady('customfields', function(){
	
	GO.customfields.nonGridTypes.push('sitefile');
	GO.customfields.dataTypes["GO\\Site\\Customfieldtype\\Sitefile"]={
		label : GO.site.lang.siteFile,
		getFormField : function(customfield, config){
			return {
				xtype: 'siteselectfile',
       	fieldLabel: customfield.name,
        name:customfield.dataname,
        anchor:'-20'
			}
		}
	}
	
	GO.customfields.nonGridTypes.push('siteselectmultifile');
	GO.customfields.dataTypes["GO\\Site\\Customfieldtype\\Sitemultifile"]={
		label : GO.site.lang.siteMultiFile,
		getFormField : function(customfield, config){
			return {
				xtype: 'siteselectmultifile',
       	fieldLabel: customfield.name,
				customfield:customfield, // make customfield available in the object
        name:customfield.dataname,
        anchor:'-20'
			}
		}
	}

}, this);