/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: SelectAddressbook.js 14816 2013-05-21 08:31:20Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.addressbook.SelectAddressbook = function(config){

	config = config || {};

	if(!config.hiddenName)
		config.hiddenName='addressbook_id';

	if(!config.fieldLabel)
	{
		config.fieldLabel=GO.addressbook.lang.addressbook;
	}


	config.store = GO.addressbook.writableAddressbooksStore;

	Ext.apply(config, {
		displayField: 'name',
		valueField: 'id',
		triggerAction:'all',
		mode:'remote',
		editable: true,
		selectOnFocus:true,
		forceSelection: true,
		typeAhead: true,
		emptyText:GO.lang.strPleaseSelect,
		pageSize: parseInt(GO.settings.max_rows_list)
	});

	GO.addressbook.SelectAddressbook.superclass.constructor.call(this,config);

}
Ext.extend(GO.addressbook.SelectAddressbook, GO.form.ComboBox, {
	
});


