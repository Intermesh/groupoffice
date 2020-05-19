/**
 * Ext.ux.form.XCheckbox - checkbox with configurable submit values
 *
 * @author  Ing. Jozef Sakalos
 * @version $Id: XCheckbox.js 21993 2017-12-20 13:57:17Z michaelhart86 $
 * @date    10. February 2008
 *
 *
 * @license Ext.ux.form.XCheckbox is licensed under the terms of
 * the Open Source LGPL 3.0 license.  Commercial use is permitted to the extent
 * that the code/component(s) do NOT become part of another Open Source or Commercially
 * licensed development library or toolkit without explicit permission.
 *
 * License details: http://www.gnu.org/licenses/lgpl.html
 */

/**
  * @class Ext.ux.XCheckbox
  * @extends Ext.form.Checkbox
  */
Ext.ns('Ext.ux.form');
Ext.ux.form.XCheckbox = Ext.extend(Ext.form.Checkbox, {
	submitOffValue:'0',
	submitOnValue:'1',
	
	// it neet to be set
	allowBlank: true,
	
	blankText : 'This field is required',	
	onRender:function(ct) {

		this.inputValue = this.submitOnValue;

		// call parent
		Ext.ux.form.XCheckbox.superclass.onRender.apply(this, arguments);

		// create hidden field that is submitted if checkbox is not checked
		this.hiddenField = this.wrap.insertFirst({
			tag:'input',
			type:'hidden'
		});
		
		
		this.on('change', function(scope, newValue, oldValue) {
			if(newValue != oldValue) {
				this.validate();
			}
		});
		
		

		// update value of hidden field
		this.updateHidden();

	}

	/**
     * Calls parent and updates hiddenField
     * @private
     */
	,
	setValue:function(val) {
		Ext.ux.form.XCheckbox.superclass.setValue.apply(this, arguments);
		this.updateHidden();
	},

	getRawValue : function() {
		return this.getValue();
	},

	/**
	 * Updates hiddenField
	 * @private
	 */
	updateHidden:function() {
		if(this.hiddenField) {
			this.hiddenField.dom.value = this.checked ? this.submitOnValue : this.submitOffValue;
			this.hiddenField.dom.name = this.checked ? '' : this.el.dom.name;
		}
	},
	setBoxLabel: function(boxLabel){
		this.boxLabel = boxLabel;
		if(this.rendered){
			this.wrap.child('.x-form-cb-label').update(boxLabel);
		}
	},

	getErrors: function(value) {
		var errors = Ext.form.Checkbox.superclass.getErrors.apply(this, arguments);
		
		if(!this.allowBlank && !this.getValue()) {
			
			errors.push(this.blankText);
		}
		
		return errors;
	},
	
	markInvalid: function (msg) {
		//don't set the error icon if we're not rendered or marking is prevented
		
        if (this.rendered && !this.preventMark) {
            msg = msg || this.invalidText;
							if(this.msgTarget){
                this.el.parent().addClass(this.invalidClass);
                var t = Ext.getDom(this.msgTarget);
                if(t){
                    t.innerHTML = msg;
                    t.style.display = this.msgDisplay;
                }
            }
        }
        
        this.setActiveError(msg);
	},
	
	clearInvalid: function () {
		 //don't remove the error icon if we're not rendered or marking is prevented
        if (this.rendered && !this.preventMark) {
            this.el.removeClass(this.invalidClass);
            
							if(this.msgTarget){
                this.el.parent().removeClass(this.invalidClass);
                var t = Ext.getDom(this.msgTarget);
                if(t){
                    t.innerHTML = '';
                    t.style.display = 'none';
                }
            }
        }
        
        this.unsetActiveError();
	}
	

}); 
Ext.reg('xcheckbox', Ext.ux.form.XCheckbox);
