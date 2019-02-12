go.detail.PropertyList = Ext.extend(Ext.Container, {
	cls: "icons",
	
	setValues : function(v) {
		var i, cmp;
		for(i in v) {
			cmp = this.getComponent(i);
			if(!cmp || !cmp.setValue) {
				continue;
			}
			
			cmp.setValue(v);
		}
	}
});
