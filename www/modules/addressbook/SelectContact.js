/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: SelectContact.js 22025 2017-12-28 15:56:53Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */


GO.addressbook.SelectContact = function(config){

	if(!config.addressbook_ids)
		config.addressbook_ids = [];

	if(!config.displayField)
		config.displayField='name';
	
	if(!config.valueField)
		config.valueField='id';

	if(!config.tpl)
		config.tpl = '<tpl for="."><div class="x-combo-list-item">{' + config.displayField + '} ({ab_name}) <tpl if="department">({department})</tpl> <tpl if="go_user_id&gt;0"><i class="icon">account_box</i></tpl></div></tpl>';

	var customfieldId = config.customfieldId || '';

	var fields = {fields: ['id', 'cf', 'name', 'salutation', 'email', 'first_name', 'middle_name','last_name', 'home_phone', 'work_phone', 'cellular', 'cellular2', 'company_id','company_name','address','address_no','zip','city','state','country','ab_name','go_user_id','department'], columns:[]};
	if(go.Modules.isAvailable("core", "customfields"))
	{
		GO.customfields.addColumns("GO\\Addressbook\\Model\\Contact", fields);
	}
	
	config.store = new GO.data.JsonStore({
	    url: GO.url("addressbook/contact/selectContact"),
	    baseParams: {	    	
				addressbook_id : config.addressbook_id,
				requireEmail: config.requireEmail ? '1' : '0',
				no_user_contacts: config.noUserContacts ? '1' : '0',
				customfield_id : customfieldId,
				addressbook_ids : Ext.encode(config.addressbook_ids)
			},
	    totalProperty:'total',	    
      fields: fields.fields,
	    remoteSort: true
	});
	
	config.store.setDefaultSort('name', 'asc');

	config.triggerAction='all';
	config.selectOnFocus=true;
	config.pageSize=parseInt(GO.settings['max_rows_list']);

	GO.addressbook.SelectContact.superclass.constructor.call(this,config);
	
}
Ext.extend(GO.addressbook.SelectContact, GO.form.ComboBoxReset,{
	
	selectContactById : function(contact_id, callback, scope){
		this.getStore().load({
			params:{
				contact_id:contact_id
			},
			callback:function(){
				this.setValue(contact_id);
				
				if(callback){
					
					var record = this.store.getAt(0);
		
					if(!scope)
						scope=this;
					callback.call(scope, this, record);
				}
			},
			scope:this
		});
		
	}
});

Ext.ComponentMgr.registerType('selectcontact', GO.addressbook.SelectContact);
