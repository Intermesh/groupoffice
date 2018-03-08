GO.grid.RowEditor = Ext.extend(Ext.ux.grid.RowEditor, {
	isDirty: function(){
		var dirty;
		this.items.each(function(f){
			
			if (typeof(f.getValue())=='object') {
				// This probably 
				var fValueString = f.getValue().format(GO.settings.date_format);
			} else {
				var fValueString = String(f.getValue());
			}
			
			if(String(this.values[f.id]) !== fValueString){
					dirty = true;
					return false;
			}
		}, this);
		return dirty;
	}	
	
});