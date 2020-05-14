/**
 * Property component
 * 
 * Used in groupoffice/www/go/modules/core/customfields/views/extjs3/CustomFields.js
 * 
 * Panel / Container must use bodyCssClass= icons.
 * 
 */
go.detail.Property = Ext.extend(Ext.Container, {
	autoEl: "p",
	label : "",
	icon: "ic-star",
	value: "",
	initComponent: function() {
		
		this.iconCmp = new Ext.BoxComponent({
			autoEl: "i",
			cls: "icon label " +this.icon
		});
		
		this.labelCmp = new Ext.BoxComponent({
			autoEl: "label",			
			html: this.label
		});
		
		this.valueCmp = new Ext.BoxComponent({
			autoEl: "span",
			html: this.value
		});
		
		this.items = [
			this.iconCmp,			
			this.valueCmp,
			this.labelCmp
		];
		
		go.detail.Property.superclass.initComponent.call(this);
	},
	
	format : function(v) {
		return v;
	},
	
	setValue : function(v) {
		this.value = this.format(v);
		if(this.rendered) {
			this.valueCmp.update(this.value);
		} 
	},

	
	setIcon : function(v) {
		this.icon = v;
		if(this.rendered) {
			this.iconCmp.update(v);
		}
	},
	
	
	setLabel : function(v) {
		this.label = v;
		if(this.rendered) {
			this.labelCmp.update(v);
		}
	}
	
});
