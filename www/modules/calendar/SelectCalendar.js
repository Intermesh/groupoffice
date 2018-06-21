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
	setValue: function (id) {

		if (!id) {
			GO.calendar.SelectCalendar.superclass.setValue.call(this, id);
			return;
		}
		var r = this.findRecord(this.valueField, id);

		if (!r)
		{
			GO.request({
				url: 'calendar/calendar/load',
				params: {id: id},
				success: function (response, options, result) {

					var comboRecord = Ext.data.Record.create([{
							name: this.valueField
						}, {
							name: this.displayField
						}]);

					var recordData = {};

					if (this.store.fields && this.store.fields.keys) {
						for (var i = 0; i < this.store.fields.keys.length; i++) {
							recordData[this.store.fields.keys[i]] = "";
						}
					}

					recordData[this.valueField] = id;
					recordData[this.displayField] = result.data.name;

					var currentRecord = new comboRecord(recordData);
					this.store.add(currentRecord);
					GO.calendar.SelectCalendar.superclass.setValue.call(this, id);
				},
				scope: this
			});
		} else
		{
			GO.calendar.SelectCalendar.superclass.setValue.call(this, id);
		}


	}
});
