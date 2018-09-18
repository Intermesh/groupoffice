/* global Ext, go */

go.modules.core.customfields.FormFieldSet = Ext.extend(Ext.form.FieldSet, {
	fieldSet: null,
	initComponent: function () {
		Ext.apply(this, {
			title: this.fieldSet.name,
			items: go.modules.core.customfields.CustomFields.getFormFields(this.fieldSet.id),
			stateId: 'cf-form-field-set-' + this.fieldSet.id,
			stateful: true,
			collapsible: true
		});

		this.on("afterrender", function() {
			var dlg = this.findParentByType("formdialog");
			
			if(!dlg) {
				console.error("No go.form.Dialog found for filtering");
				return;
			}

			dlg.on("load", function () {
				this.filter(dlg.formPanel.getValues());
			}, this);
			
			if(dlg.formPanel.entity) {
				this.filter(dlg.formPanel.getValues());
			}
		}, this);

		go.modules.core.customfields.FormFieldSet.superclass.initComponent.call(this);
	},

	filter: function (entity) {
		for (var name in this.fieldSet.filter) {
			var v = this.fieldSet.filter[name];

			if (Ext.isArray(v)) {
				if (v.indexOf(entity[name]) === -1) {
					this.setVisible(false);
					return;
				}
			} else
			{
				if (v !== entity[name]) {
					this.setVisible(false);
					return;
				}
			}
		}		
		this.setVisible(true);
	}
});
