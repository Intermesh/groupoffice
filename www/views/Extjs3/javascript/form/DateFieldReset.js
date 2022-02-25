/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ComboReset.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.form.DateFieldReset = Ext.extend(Ext.form.DateField, {
	initComponent: function() {
		this.triggerConfig = {
				tag:'span', cls:'x-form-twin-triggers', cn:[
				{tag: "button", cls: "x-form-trigger x-form-clear-trigger"},
				{tag: "button", cls: "x-form-trigger x-form-date-trigger"}
		]};
		GO.form.DateFieldReset.superclass.initComponent.call(this);
	},
	onTrigger1Click : function()
	{
			this.setValue('');                 // clear content
	},

	getTrigger: Ext.form.TwinTriggerField.prototype.getTrigger,
	initTrigger: Ext.form.TwinTriggerField.prototype.initTrigger,
	onTrigger2Click: Ext.form.DateField.prototype.onTriggerClick,
	trigger1Class: Ext.form.DateField.prototype.triggerClass,
	trigger2Class: Ext.form.DateField.prototype.triggerClass
});

Ext.reg('datefieldreset', GO.form.DateFieldReset);
