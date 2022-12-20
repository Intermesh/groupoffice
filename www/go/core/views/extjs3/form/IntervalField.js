go.form.IntervalField = Ext.extend(Ext.Container, {
	layout: "hbox",

	name: null,

	isFormField: true,

	value: "P7D",

	initComponent: function() {


		this.numberField = new go.form.NumberField({
			decimals: 0
		});

		this.periodSelect = new go.form.SelectField({
			options: [
				['H', t("hours")],
				['D', t("days")],
				['M', t("months")],
				['Y', t("years")]
			]
		});

		this.items = [
			this.numberField,
			this.periodSelect
		];

		this.supr().initComponent.call(this);

		this.setValue(this.value);
	},

	getName: function () {
		return this.name;
	},

	reset: function () {
		this.setValue("");
	},

	setNotDirty : function() {
		this.numberField.dirty = false;
		this.numberField.originalValue = this.numberField.getValue();
		this.periodSelect.dirty = false;
		this.periodSelect.originalValue = this.periodSelect.getValue();

	},


	isDirty: function () {
		return this.numberField.isDirty() || this.periodSelect.isDirty();
	},

	setValue: function (v) {
		const matches = v.match(/P(-?[0-9]+)(H|D|M|Y)/);
		if(matches) {
			this.numberField.setValue(matches[1]);
			this.periodSelect.setValue(matches[2]);
		}
	},

	getValue: function (dirtyOnly) {
		return "P" + this.numberField.getValue() + this.periodSelect.getValue();
	},

	markInvalid: function (msg) {
		this.numberField.markInvalid(msg);
	},

	clearInvalid: function () {
		this.numberField.clearInvalid();
	},

	isValid : function(preventMark){
		return this.numberField.isValid(preventMark);
	},

	validate: function () {
		return this.numberField.validate();
	},

	focus: function () {
		this.numberField.focus();
	}
});