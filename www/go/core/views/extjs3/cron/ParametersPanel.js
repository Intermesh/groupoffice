go.cron.ParametersPanel = Ext.extend(Ext.Panel, {
	title: t("Params", "cron"),
	cls: 'go-form-panel',
	layout: 'form',
	labelWidth: 100,
	paramElements: [],
	buildForm: function (params) {
		this.setDisabled(params.length == 0);
		this.removeComponents();

		for (var key in params) {
			this.addField(key, params[key], key);
		}

		this.doLayout(false, true);
	},
	addField: function (name, value, label) {

		var f = this.ownerCt.ownerCt.form;

		var inputField = new Ext.form.TextField({
			name: name,
			value: value,
			fieldLabel: label
		});

		this.paramElements.push(inputField);

		this.add(inputField);
		f.add(inputField);

		this.add(inputField);

	},
	removeComponents: function () {
		var f = this.ownerCt.ownerCt.form;
		for (var i = 0; i < this.paramElements.length; i++)
		{
			f.remove(this.paramElements[i]);
			this.remove(this.paramElements[i], true);
		}
		this.paramElements = [];
	}

});
