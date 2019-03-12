go.form.FormContainer = Ext.extend(Ext.Container, {
	layout: "form",
	
	name: null,
	
	isFormField: true,
	
	origValue : null,
	
	initComponent : function() {
		this.origValue = {};
		go.form.FormContainer.superclass.initComponent.call(this);
	},
	
	getName: function() {
		return this.name;
	},	
	
	findField : function(id) {

		var field = this.items.get(id);

		if (!Ext.isObject(field)) {
			//searches for the field corresponding to the given id. Used recursively for composite fields
			var findMatchingField = function (f) {
				if (f.isFormField) {
					if (f.dataIndex == id || f.id == id || f.getName() == id) {
						field = f;
						return false;
					} else if (f.isComposite) {
						return f.items.each(findMatchingField);
					} else if (f instanceof Ext.form.CheckboxGroup && f.rendered) {
						return f.eachItem(findMatchingField);
					}
				}
			};

			this.items.each(findMatchingField);
		}
		return field || null;
	},
	
	isDirty: function () {		
		var dirty = false;
		this.items.each(function(i) {
			if(i.isDirty && i.isDirty()) {
				dirty = true;
				//stops iteration
				return false;
			}
		}, this);
		
		return dirty;
	},
	
	reset : function() {
		this.setValue({});		
	},

	setValue: function (v) {
		this.origValue = v;
		
		for(var name in v) {
			var field = this.findField(name);
			if(field) {
				field.setValue(v[name]);
			}
		}
	},

	getValue: function (dirtyOnly) {
		var v = dirtyOnly ? {} : this.origValue, val;
				
		var fn = function(f) {						
			if (f.getXType() == 'compositefield' || f.getXType() == 'checkboxgroup') {
				f.items.each(fn);
				return true;
			}

			if(f.isFormField && (!dirtyOnly || f.isDirty())) {

				if (f.getXType() == 'numberfield') {
					f.serverFormats = false; // this will post number as number
				} 
				val = f.getValue();				

				if(Ext.isDate(val)) {
					val = val.serialize();
				}				

				v[f.getName()] = val;
			}
		}
		
		this.items.each(fn, this);
		
		return v;
	},	
	
	markInvalid: function (msg) {
		this.items.each(function(i) {
			if(i.isFormField) {
				i.markInvalid(msg);			
			}
		});
	},
	
	clearInvalid: function () {
		this.items.each(function(i) {
			if(i.isFormField) {
				i.clearInvalid();			
			}
		});
	},

	validate: function () {
		var valid = true;
		this.items.each(function(i) {
			if(i.isFormField && !i.validate()) {
				valid = false;
				//stops iteration
				return false;
			}
		}, this);
		
		return valid;
	},
	
	focus : function() {
		var firstFormField = this.items.find(function(i){
			return i.isFormField;
		});
		
		if(firstFormField) {
			firstFormField.focus();
		} else
		{
			go.form.FormContainer.superclass.focus.call(this);
		}
	}
});

Ext.reg('formcontainer', go.form.FormContainer);