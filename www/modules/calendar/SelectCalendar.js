/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: SelectCalendar.js 22112 2018-01-12 07:59:41Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.calendar.SelectCalendar = function(config){

	config = config || {};

	if(!config.hiddenName)
		config.hiddenName='calendar_id';

	if(!config.fieldLabel)
	{
		config.fieldLabel=t("Calendar", "calendar");
	}

	Ext.apply(this, config);


	this.store = new GO.data.JsonStore({
		url: GO.url("calendar/calendar/calendarsWithGroup"),
		baseParams:{
			permissionLevel:GO.permissionLevels.create
		},
		fields:['id','name', 'group_name', 'user_name', 'group_id', 'customfields','permissionLevel'],
		remoteSort:true
	});

	if(!config.emptyText)
		this.emptyText=t("Please select...");

	GO.calendar.SelectCalendar.superclass.constructor.call(this,{
		displayField: 'name',
		valueField: 'id',
		triggerAction:'all',
		editable: true,
		selectOnFocus:true,
		forceSelection: true,
		typeAhead: true,
		pageSize:parseInt(GO.settings['max_rows_list']),
		mode:'remote',
        tpl: new Ext.XTemplate(
            '<tpl for=".">',
            '<tpl if="this.group_name != values.group_name">',
            '<tpl exec="this.group_name = values.group_name"></tpl>',
            '<h4>{group_name}</h4>',
            '</tpl>',
            '<div class="x-combo-list-item">{name}</div>',
            '</tpl>'
    	)
	});

}
Ext.extend(GO.calendar.SelectCalendar, GO.form.ComboBoxReset, {

});
