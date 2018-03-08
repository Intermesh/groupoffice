GO.form.HtmlComponent = Ext.extend(Ext.BoxComponent, {
	onRender : function(ct, position){
		this.el = ct.createChild({
			tag: 'div', 
			html: this.html, 
			cls: this.cls, 
			style:this.style
			});
	},
	// Added because otherwise you get an JS error about this function does not exist for this element
	// when you add this to forms
	reset : function(){ 
		return this;
	},
	// Added because otherwise you get an JS error about this function does not exist for this element
	clearInvalid : function(){ 
		return this;
	},
	// Added because otherwise you get an JS error about this function does not exist for this element
	validate : function(){
		return true; // True because a heading is always valid
	},
	setValue : function(v){
		
	}
});

Ext.reg('htmlcomponent', GO.form.HtmlComponent);