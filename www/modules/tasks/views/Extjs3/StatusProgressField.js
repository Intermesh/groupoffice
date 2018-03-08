
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: StatusProgressField.js 15954 2013-10-17 12:04:36Z johan $
 * @copyright Copyright Intermesh
 * @author Johan Overeem <JOvereem@intermesh.nl>
 */

GO.tasks.StatusProgressField = function (config) {
	var config = config || {};
	
	var percentages = [];
	for (var i = 0; i <= 100; i += 10) {
		percentages.push([i, i + "%"]);
	}
	
	this.progressInpercentagesStore = new Ext.data.SimpleStore({
		fields: ['value', 'text'],
		data: percentages
	});
	
	config = Ext.applyIf(config, {
		fieldLabel: GO.tasks.lang.taskStatus,
		items: [
			this.taskStatusField = new GO.tasks.SelectTaskStatus({
				flex: 3,
				listeners: {
					scope: this,
					select: function (combo, record) {
						if (record.data.value == 'COMPLETED')
							this.progressField.setValue(100);
					}
				}
			}),
			this.progressField = new Ext.form.ComboBox({
				fieldLabel: GO.tasks.lang.taskPercentage_complete,
				flex: 1,
				hiddenName: 'percentage_complete',
				store: this.progressInpercentagesStore,
				value: '0',
				valueField: 'value',
				displayField: 'text',
				mode: 'local',
				triggerAction: 'all',
				editable: false,
				selectOnFocus: true,
				listeners: {
					scope: this,
					select: function (combo, record) {
						if (record.data.value == 100)
							this.taskStatusField.setValue("COMPLETED");
					}
				}
			})
		]
	});
	
	GO.tasks.StatusProgressField.superclass.constructor.call(this, config);

};


Ext.extend(GO.tasks.StatusProgressField, Ext.form.CompositeField,{
	
	
	
});
