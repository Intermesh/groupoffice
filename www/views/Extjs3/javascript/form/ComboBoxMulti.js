/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ComboBoxMulti.js 22070 2018-01-05 15:13:12Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
/**
 * @class GO.form.ComboBoxMulti
 * @extends GO.form.ComboBox
 * Adds freeform multiselect and duplicate entry prevention to the standard combobox
 * @constructor
 * Create a new ComboBoxMulti.
 * @param {Object} config Configuration options
 */
GO.form.ComboBoxMulti = function(config){
   
    // this option will interfere will expected operation
    config.typeAhead = false;
    // these options customize behavior
    config.minChars = 3;
		config.queryDelay=500;
		
    config.hideTrigger = true;
    config.defaultAutoCreate = {
        tag: "textarea",
        autocomplete: "off"
    };
		 //config.height = dp(24);
    GO.form.ComboBoxMulti.superclass.constructor.call(this, config);
		
		
		
		this.on('render', function() {			
			//this.syncHeight();
			this.getEl().on('input', function(e) {								
				this.syncHeight();
      }, this);
			
		}, this);
   
//    this.on('focus', function(){this.focused=true;}, this);
//    this.on('blur', function(){this.focused=false;}, this);
};

Ext.extend(GO.form.ComboBoxMulti, GO.form.ComboBox, {
		/**
     * @cfg {String} sep is used to separate text entries
     */
		sep : ',',

		//private
		focused : false,
		
		maxHeight: 100,
		
		
		syncHeight : function() {
			
			this.el.dom.style.overflowY = 'auto';
			var changed = false;
			if(this.el.dom.offsetHeight > dp(32)){
				this.el.dom.style.height = dp(32) + "px";
				changed = true;
			}
			console.log(this.el.dom);
			var height = Math.min(this.el.dom.scrollHeight, this.maxHeight);
			if(height > dp(32)) {
				this.el.dom.style.height = height + "px";
				changed = true;
			}
			
			if(changed) {
				this.fireEvent('grow', this);
			}
		},
		
		
		// private
//    onViewClick : function(doFocus){
//			
//			//don't autoselect on tab. But do this on enter only.
//			if(doFocus === false)
//				return this.collapse();
//			else
//				return GO.form.ComboBoxMulti.superclass.onViewClick.call(this, doFocus);
//    },
		
    getCursorPosition: function(){
		
	    if (document.selection) { // IE
	        var r = document.selection.createRange();
					if(!r)
						return false;

	        var d = r.duplicate();

					if(!this.el.dom)
						return false;

	        d.moveToElementText(this.el.dom);
	        d.setEndPoint('EndToEnd', r);
	        return d.text.length;            
	    }
	    else {
	        return this.el.dom.selectionEnd;
	    }
    },
    
    getActiveRange: function(){
        var s = this.sep;
        var p = this.getCursorPosition();
        var v = this.getRawValue();
        var left = p;
        while (left > 0 && v.charAt(left) != s) {
            --left;
        }
        if (left > 0) {
            left++;
        }
        return {
            left: left,
            right: p
        };
    },
    
    getActiveEntry: function(){
        var r = this.getActiveRange();
        return this.getRawValue().substring(r.left, r.right).trim();//.replace(/^s+|s+$/g, '');
    },
    
    replaceActiveEntry: function(value){
        var r = this.getActiveRange();
        var v = this.getRawValue();
        if (this.preventDuplicates && v.indexOf(value) >= 0) {
            return;
        }
        var pad = (this.sep == ' ' ? '' : ' ');
//				
//				This code messed up names with utf8
//				var typedValue = v.substring(r.left, r.right).trim();			
				
//				var parts = typedValue.toLowerCase().split(' ')
				
//				for(var i=0;i<parts.length;i++){
//					if(value.toLowerCase().indexOf(parts[i])==-1){
//						//don't replace with different value
//						//return false;
//						value=typedValue;
//						break;
//					}
//				}				
				
				this.setValue(v.substring(0, r.left) + (r.left > 0 ? pad : '') + value + this.sep + pad + v.substring(r.right));
				
        var p = r.left + value.length + 2 + pad.length;
        this.selectText.defer(200, this, [p, p]);
    },
    
    onSelect: function(record, index){
        if (this.fireEvent('beforeselect', this, record, index) !== false) {
            var value = Ext.util.Format.htmlDecode(record.data[this.valueField || this.displayField]);
            if (this.sep) {
                this.replaceActiveEntry(value);
            }
            else {
                this.setValue(value);
            }
            this.collapse();
            this.fireEvent('select', this, record, index);
        }
    },
    
    initQuery: function(){
			if(this.getEl().id === document.activeElement.id)
				this.doQuery(this.sep ? this.getActiveEntry() : this.getRawValue());
			
//    	if(this.focused)
//        this.doQuery(this.sep ? this.getActiveEntry() : this.getRawValue());
    }
});
