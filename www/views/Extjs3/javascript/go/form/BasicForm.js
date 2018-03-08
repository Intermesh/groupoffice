Ext.override(Ext.form.BasicForm, {
	
	getFieldValuesOrig: Ext.form.BasicForm.prototype.getFieldValues,
	setValuesOrig: Ext.form.BasicForm.prototype.setValues,
	/**
	 * Retrieves the fields in the form as a set of key/value pairs, using the {@link Ext.form.Field#getValue getValue()} method.
	 * If multiple fields exist with the same name they are returned as an array.
	 * 
	 * Use submit: false on fields you don't wish to include here.
	 * 
	 * @param {Boolean} dirtyOnly (optional) True to return only fields that are dirty.
	 * @return {Object} The values in the form
	 */
	getFieldValues: function (dirtyOnly) {
		var v = this.getFieldValuesOrig(dirtyOnly);
		
		//fix for submitValue not working in getFieldValues()
		this.items.each(function(f) {
			if(f.submit === false) {
				delete v[f.getName()];
			}
		});

		var keys, converted = {}, currentJSONlevel;

		for (var key in v) {

			keys = key.split('.');

			currentJSONlevel = converted;

			for (var i = 0; i < keys.length; i++) {
				if (i === (keys.length - 1)) {
					currentJSONlevel[keys[i]] = v[key];
				} else
				{
					currentJSONlevel[keys[i]] = currentJSONlevel[keys[i]] || {};
					currentJSONlevel = currentJSONlevel[keys[i]];
				}
			}

		}
				
		return converted;
	},
	
	setValues: function (values) {		
		values = this.joinValues(values);		
	  return this.setValuesOrig(values);		
	},
	
	joinValues : function(v) {
		
		if(!Ext.isObject(v)){
			return v;
		}
		
		var converted = {};
		
		for(var name in v) {
			if(!Ext.isDate(v[name]) && Ext.isObject(v[name]) ){
				
				for(var subname in v[name]) {
					converted[name + '.' + subname] = this.joinValues(v[name][subname]);
				}
				
			} else
			{
				converted[name] = v[name];
			}
			
		}
		
		return converted;
		
	}
});

