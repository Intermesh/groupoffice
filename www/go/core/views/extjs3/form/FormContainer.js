/**
 * FormContainer
 * 
 * A form container is a group of fields that acts like a single form field. 
 * It returns an object with all it's child form fields a members.
 * 
 * See also FormGroup for returning an array.
 */
go.form.FormContainer = Ext.extend(Ext.Container, {
	layout: "form",

	name: null,

	isFormField: true,

	initComponent: function () {
		this.additionalFields = [];

		// this.on("add", function (e) {
		// 	//to prevent adding to Ext.form.BasicForm with add event.
		// 	//Cancels event bubbling
		// 	return false;
		// });

		go.form.FormContainer.superclass.initComponent.call(this);
	},

	getName: function () {
		return this.name;
	},

	addAdditionalField: function (f) {
		this.additionalFields.push(f);
	},

	getAllFormFields: function () {
	
		//use slice to obtain copy
		var fields = this.additionalFields.slice(), fn = function (f) {
			if (f.isFormField && !f.isComposite && f.getXType() != 'checkboxgroup') {
				fields.push(f);
			} else if (f.items) {
				if (f.items.each) {
					//Ext.util.Collection
					f.items.each(fn);
				} else
				{
					//native array
					f.items.forEach(fn);
				}
			}
		};

		if(this.items) {
			this.items.each(fn);
		}
		
		return fields;
	},
	findField: function (id) {

		//searches for the field corresponding to the given id. Used recursively for composite fields
		var field = false, findMatchingField = function (f) {
			if (f.dataIndex == id || f.id == id || f.getName() == id) {
				field = f;
				return false;
			}
		};

		this.getAllFormFields().forEach(findMatchingField);

		return field || null;
	},

	isDirty: function () {
		var dirty = false, fn = function (i) {
			if (i.isDirty && i.isDirty()) {
				dirty = true;
				//stops iteration
				return false;
			}
		};
		this.getAllFormFields().forEach(fn, this);

		return dirty;
	},

	setNotDirty : function() {
		var dirty = false, fn = function (i) {
			i.originalValue = i.getValue();
			i.dirty = false;
			if(i.setNotDirty) {
				i.setNotDirty(false);
			}
		};
		this.getAllFormFields().forEach(fn, this);
	},

	reset: function () {
		this.setValue({});
	},

	setValue: function (v) {

		for (var name in v) {
			var field = this.findField(name);
			if (field) {
				field.setValue(v[name]);
				// field.originalValue = field.getValue();
			}
		}

		this.fireEvent("setvalue", this, v);

	},

	getValue: function (dirtyOnly) {
		var v = {}, val;

		var fn = function (f) {

			if (f.getXType() == 'checkboxgroup') {
				f.items.each(fn);
				return true;
			}

			if(f.submit === false || f.disabled === true) {
				return true;
			}

			if ((!dirtyOnly || f.isDirty())) {				

				if (f.getXType() == 'numberfield') {
					f.serverFormats = false; // this will post number as number
				}

				val = f.getValue();

				if (Ext.isDate(val)) {
					val = val.serialize();
				}

				v[f.getName()] = val;
			}
		};

		this.getAllFormFields().forEach(fn, this);

		return v;
	},

	markInvalid: function (msg) {
		this.getAllFormFields().forEach(function (i) {
			i.markInvalid(msg);
		});
	},

	clearInvalid: function () {
		this.getAllFormFields().forEach(function (i) {
			i.clearInvalid();
		});
	},

	isValid : function(preventMark){
		if(this.disabled){
			return true;
		}
		var f = this.getAllFormFields();
		for(var i = 0, l = f.length; i < l; i++) {
			if(!f[i].isValid(preventMark)) {
				return false;
			}
		}
		return true;
	},

	validate: function () {
		var valid = true, fn = function (i) {
			if (i.isFormField && !i.validate()) {
				valid = false;
				//stops iteration
				return false;
			}
		};
		this.getAllFormFields().forEach(fn, this);

		return valid;
	},

	focus: function () {
		var fields = this.getAllFormFields();

		fields = fields.filter(function(field) {
			if(field.hidden) {
				return false;
			}

			if(field instanceof Ext.form.Hidden) {
				return false;
			}

			return true;
		});

		var firstFormField = fields.length ? fields[0] : false;

		if (firstFormField) {
			firstFormField.focus();
		} else
		{
			go.form.FormContainer.superclass.focus.call(this);
		}
	}
});

Ext.reg('formcontainer', go.form.FormContainer);