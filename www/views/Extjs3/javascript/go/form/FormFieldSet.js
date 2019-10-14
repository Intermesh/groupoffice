

go.form.FormFieldSet = Ext.extend(Ext.form.FieldSet, {
//	labelStyle: 'width: 0;display:none',
//	elementStyle: {"padding-left": 0},
	hideLabel: true,
	//make it a form field
	isFormField: true,

	getName: function () {
		return 'customFields';
	},

	setValue: function (v) {
		this._isDirty = true;
		for (var name in v) {
			var field = this.findField(name);
			if (field) {
				field.setValue(v[name]);
			}
		}
	},
	getValue: function () {
		var v = {};

		this.items.each(function (f) {
			if (f.getValue) {
				v[f.getName()] = f.getValue();
			}
		});

		return v;

	},
	markInvalid: function () {

	},
	clearInvalid: function () {

	},
	
	_isDirty : false,
	
	isDirty : function() {
		return this._isDirty;
	},
	/**
	 * Find a {@link Ext.form.Field} in this form.
	 * @param {String} id The value to search for (specify either a {@link Ext.Component#id id},
	 * {@link Ext.grid.Column#dataIndex dataIndex}, {@link Ext.form.Field#getName name or hiddenName}).
	 * @return Field
	 */
	findField: function (id) {
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
	afterRender : function() {
		this.hideLabel = false;
		go.form.FormFieldSet.superclass.afterRender.call(this);
	}

});




// registre xtype
Ext.reg('formfieldset', go.form.FormFieldSet);
