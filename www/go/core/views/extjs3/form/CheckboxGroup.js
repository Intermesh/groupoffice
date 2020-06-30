/**
 * This checkbox group will post the checked boxes as array of Ids
 * Used in the addScaler relation of the API
 */
go.form.CheckboxGroup = Ext.extend(Ext.Container, {
	isFormField: true,
	cls: "go-form-checkboxgroup",
	dirty: false,

	initComponent: function () {
		go.form.CheckboxGroup.superclass.initComponent.call(this);

		this.on("add", function (e) {
			//to prevent adding to Ext.form.BasicForm with add event.
			//Cancels event bubbling
			return false;
		});


	},

	getName: function() {
		return this.name;
	},

	isDirty: function () {
		if(this.dirty) {
			return true;
		}
		
		var dirty = false;
		this.items.each(function(cb) {
			if(cb.isDirty()) {
				dirty = true;
				//stops iteration
				return false;
			}
		}, this);
		
		return dirty;
	},
	reset : function() {
		this.setValue([]); // todo set orig values?
		this.dirty = false;
	},

	getValue: function () {	
		var v = [];
		this.items.each(function(cb) {
			if(cb.checked) {
				v.push(cb.value);
			}
		});
		return v;
	},

	setValue: function (ids) {
		this.items.each(function(cb) {
			if(ids.indexOf(parseInt(cb.value)) !== -1) {
				cb.setValue(true);
			} else {
				cb.setValue(false);
			}
		});
	},

	markInvalid: function (msg) {
		this.items.each(function(cb) {
			cb.markInvalid(msg);			
		});
	},
	
	clearInvalid: function () {
		this.items.each(function(cb) {
			cb.clearInvalid();			
		});
	},

	validate: function () {
		this.items.each(function(cb) {
			if(!cb.validate()) {
				return false;
			}		
		});
		return true;
	},

	isValid: function (preventMark) {
		this.items.each(function(cb) {
			if(!cb.isValid(preventMark)) {
				return false;
			}
		});
		return true;
	}
});