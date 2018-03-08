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

GO.form.ComboBoxReset = Ext.extend(GO.form.ComboBox, {
			initComponent : Ext.form.TwinTriggerField.prototype.initComponent,
			getTrigger : Ext.form.TwinTriggerField.prototype.getTrigger,
			initTrigger : Ext.form.TwinTriggerField.prototype.initTrigger,
			trigger1Class : 'x-form-clear-trigger',
			//hideTrigger1 : true,
			onViewClick : Ext.form.ComboBox.prototype.onViewClick.createSequence(function() {
				//this.triggers[0].setDisplayed(true);
			}),
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
			},
			afterRender:function(){
				GO.form.ComboBoxReset.superclass.afterRender.call(this);
				if(Ext.isIE8)this.el.setTop(0);
				
				//this.on('resize', function(combo, adjWidth, adjHeight, rawWidth, rawHeight ){console.log(adjWidth);}, this);
			}			
		});

Ext.reg('comboboxreset', GO.form.ComboBoxReset);