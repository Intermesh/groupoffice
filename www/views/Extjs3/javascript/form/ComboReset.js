/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ComboReset.js 21892 2017-12-12 09:15:19Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.form.ComboBoxReset = Ext.extend(GO.form.ComboBox, {
			initComponent : Ext.form.TwinTriggerField.prototype.initComponent,
			getTrigger : Ext.form.TwinTriggerField.prototype.getTrigger,
			getTriggerWidth : function() {return 0; },
			initTrigger : Ext.form.TwinTriggerField.prototype.initTrigger,
			trigger1Class : 'x-form-clear-trigger',
			//hideTrigger1 : true,
			onTrigger2Click : function() {
				this.onTriggerClick();
			},
			onTrigger1Click : function() {
				
				if(this.disabled)
					return;

				var oldValue = this.getValue();

				this.clearValue();
				//this.triggers[0].setDisplayed(false);

				this.fireEvent('change', this, this.getValue(), oldValue);
				this.fireEvent('clear', this);
			},
			setValue : function(v){
				GO.form.ComboBoxReset.superclass.setValue.call(this, v);
				if(this.rendered)
				{					
					//this.triggers[0].setDisplayed(v!='');					
				}
			}			
		});

Ext.reg('comboboxreset', GO.form.ComboBoxReset);
