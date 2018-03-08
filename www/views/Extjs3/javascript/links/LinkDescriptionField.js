//GO.form.LinkDescriptionField = Ext.extend(function(config){
//	config = config || {};
//	
//	config.store = new GO.data.JsonStore({				
//		url: GO.settings.config.host+ 'json.php',
//    baseParams: {
//    	task: 'link_descriptions'
//    	},
//    root: 'results',
//    id: 'id',
//    totalProperty:'total',
//    fields: ['id','description'],
//    remoteSort: true	
//	});	
//	config.displayField='description';
//  config.triggerAction='all';
//	config.selectOnFocus=false;
//	config.pageSize=parseInt(GO.settings['max_rows_list']);
//	
//	GO.form.LinkDescriptionField.superclass.constructor.call(this, config);
//	
//},GO.form.ComboBoxReset, {
//
//	hiddenName: 'description',
//	maxLength:100
//	
//});

GO.form.LinkDescriptionField = Ext.extend(Ext.form.TextField, {
	name:'description'
});