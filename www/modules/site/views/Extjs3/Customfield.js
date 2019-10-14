//Ext.onReady(function() {
//	
//	GO.customfields.nonGridTypes.push('sitefile');
//	GO.customfields.dataTypes["GO\\Site\\Customfieldtype\\Sitefile"]={
//		label : t("Site File", "site"),
//		getFormField : function(customfield, config){
//			return {
//				xtype: 'siteselectfile',
//       	fieldLabel: customfield.name,
//        name:customfield.dataname,
//        anchor:'-20'
//			}
//		}
//	}
//	
//	GO.customfields.nonGridTypes.push('siteselectmultifile');
//	GO.customfields.dataTypes["GO\\Site\\Customfieldtype\\Sitemultifile"]={
//		label : t("Site Multifile", "site"),
//		getFormField : function(customfield, config){
//			return {
//				xtype: 'siteselectmultifile',
//       	fieldLabel: customfield.name,
//				customfield:customfield, // make customfield available in the object
//        name:customfield.dataname,
//        anchor:'-20'
//			}
//		}
//	}
//
//});
