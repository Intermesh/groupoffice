Ext.override(Ext.form.BasicForm, {
	
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
		var o = {},
						n,
						key,
						val,
						me = this;

		var fn = function (f) {
			if (dirtyOnly !== true || f.isDirty()) {
			
				if (f.getXType() == 'compositefield' || f.getXType() == 'checkboxgroup') {
					f.items.each(fn);
					return true;
				}

				if(f.submit === false) {
					return true;
				}

				n = f.getName();
				key = o[n];
				if (f.getXType() == 'numberfield') {
					f.serverFormats = false; // this will post number as number
				} 
				val = f.getValue();
				
				
				if(Ext.isDate(val)) {
					val = me.serializeDate(val);
				}				

				if (Ext.isDefined(key)) {
					if (Ext.isArray(key)) {
						o[n].push(val);
					} else {
						o[n] = [key, val];
					}
				} else {
					o[n] = val;
				}
			}
		};
		
		this.items.each(fn);
  

		var keys, converted = {}, currentJSONlevel;

		for (var key in o) {

			keys = key.split('.');

			currentJSONlevel = converted;

			for (var i = 0; i < keys.length; i++) {
				if (i === (keys.length - 1)) {
					currentJSONlevel[keys[i]] = o[key];
				} else
				{
					currentJSONlevel[keys[i]] = currentJSONlevel[keys[i]] || {};
					currentJSONlevel = currentJSONlevel[keys[i]];
				}
			}

		}
				
		return converted;
	},
	
	serializeDate : function(date) {
		if(date.getHours() == 0 && date.getMinutes() == 0 && date.getSeconds() == 0) {
			//no time
			return date.format("Y-m-d");
		} else
		{
			return date.format('c');
		}
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

