
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
	
	config = Ext.applyIf(config, {
		fieldLabel: t("Status", "tasks"),
		items: [
			this.taskStatusField = new GO.tasks.SelectTaskStatus({
				flex: 2,
				listeners: {
					scope: this,
					select: function (combo, record) {
						if (record.data.value == 'COMPLETED')
							this.progressField.setValue(100);
					}
				}
			}),
			this.progressField = new Ext.form.SliderField({
				fieldLabel: t("Percentage complete", "tasks"),
				flex: 1,
				name: 'percentage_complete',
				minValue: 0,
				maxValue: 100,
				increment: 10,
				value: 0,
				listeners: {
					scope: this,
					change: function (combo, newValue) {
						if (newValue == 100)
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
