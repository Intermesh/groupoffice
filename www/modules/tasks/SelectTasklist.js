/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: SelectTasklist.js 22112 2018-01-12 07:59:41Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.tasks.SelectTasklist = function(config){
	
	config = config || {};

	if(!config.hiddenName)
		config.hiddenName='tasklist_id';

	if(!config.fieldLabel)
	{
		config.fieldLabel=t("Tasklist", "tasks");
	}

	Ext.apply(this, config);
	
	
	this.store = new GO.data.JsonStore({
		url: GO.url('tasks/tasklist/store'),
		baseParams:{permissionLevel: GO.permissionLevels.create},
		fields:['id','name','user_name'],
		remoteSort:true
	});	

	GO.tasks.SelectTasklist.superclass.constructor.call(this,{
		displayField: 'name',	
		valueField: 'id',
		triggerAction:'all',		
		mode:'remote',
		editable: true,
		selectOnFocus:true,
		forceSelection: true,
		typeAhead: true,
		emptyText:t("Please select..."),
		pageSize: parseInt(GO.settings.max_rows_list)
	});
	
}
Ext.extend(GO.tasks.SelectTasklist, GO.form.ComboBoxReset, {
	
	/*afterRender : function(){
		
		
		this.store.load({
			
			callback:function(){
				GO.tasks.SelectTasklist.superclass.afterRender.call(this);		
			},
			scope: this
			
		});	
	}*/	
});
