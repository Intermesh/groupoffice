/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SelectUser.js 22151 2018-01-17 13:59:21Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
/**
 * @class GO.form.SelectUser
 * @extends GO.form.ComboBox
 *
 * Selects a Group-Office user.
 *
 * @constructor
 * Creates a new SelectUser
 * @param {Object} config Configuration options
 */
GO.form.SelectUser = function(config){

	config = config || {};

	if(typeof(config.allowBlank)=='undefined')
		config.allowBlank=false;

	Ext.apply(this, config);
	
	if (typeof(config.store)=='undefined') {
		this.store = new GO.data.JsonStore({
			url: GO.url('core/users'),
			root: 'results',
			totalProperty: 'total',
			id: 'id',
			fields:['id','name','email','username', 'displayName', 'avatarId', 'company','first_name', 'middle_name', 'last_name', 'address', 'address_no', 'zip', 'city', 'state', 'country','cf'],
			remoteSort: true
		});
		this.store.setDefaultSort('name', 'asc');
	} else {
		this.store = config.store;
	}

	if(!this.hiddenName)
		this.hiddenName='user_id';

	if(!this.valueField)
		this.valueField='id';
	
	GO.form.SelectUser.superclass.constructor.call(this,{
		displayField: 'displayName',
		triggerAction: 'all',
		selectOnFocus:true,
		forceSelection: true,
		pageSize: parseInt(GO.settings['max_rows_list'])
	});
	
	if(!config.startBlank){
		this.setRemoteValue(GO.settings.user_id, GO.settings.displayName);
		this.value=GO.settings.user_id;
	}

	this.tpl = new Ext.XTemplate(
			'<tpl for=".">',
			'<div class="x-combo-list-item"><div class="user">\
				 <tpl if="!avatarId"><div class="avatar"></div></tpl>\\n\
				 <tpl if="avatarId"><div class="avatar" style="background-image:url({[go.Jmap.thumbUrl(values.avatarId, {w: 40, h: 40, zc: 1})]})"></div></tpl>\
				 <div class="wrap">\
					 <div>{displayName}</div><small style="color:#333;">{username}</small>\
				 </div>\
			 </div></div>',
			'</tpl>'
		 );

}

Ext.extend(GO.form.SelectUser, GO.form.ComboBoxReset,{
	fieldLabel:t("User"),
	setRemoteValue : function(user_id, name)
	{
		this.setValue(user_id);
		this.setRemoteText(name);
	}	
});

Ext.reg('selectuser', GO.form.SelectUser);
