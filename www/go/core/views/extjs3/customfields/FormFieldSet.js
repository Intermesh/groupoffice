/* global Ext, go */

go.modules.core.core.FormFieldSet = Ext.extend(Ext.form.FieldSet, {
	fieldSet: null,
	initComponent: function () {
		
		var items = [];
		
		if(this.fieldSet.description) {
			items.push({
				xtype: "box",
				autoEl: "p",
				html: go.util.textToHtml(this.fieldSet.description)
			});
		}
		
		items = items.concat(go.modules.core.core.CustomFields.getFormFields(this.fieldSet.id));
		
		Ext.apply(this, {
			title: this.fieldSet.name,
			items: items,
			stateId: 'cf-form-field-set-' + this.fieldSet.id,
			stateful: true,
			collapsible: true
		});

		this.on("afterrender", function() {
			//find entity panel
			var form = this.findParentByType("form");
			
			if(!form) {
				console.error("No go.form.EntityPanel found for filtering");
				return;
			}
						
			if(form.getXType() == "entityform") {
				form.on("load", function () {
					this.filter(form.getValues());
				}, this);

				if(form.entity) {
					this.filter(form.getValues());
				}
			} else
			{
				//Legacy code
				
				//Add a beforeaction event listener that will send the custom field data JSON encoded.
				//The old framework will use this to save custom fields.
				if(!form.legacyParamAdded) {
					form.getForm().on("beforeaction", function(form, action) {	
						if(action.type !== "submit") {
							return true;
						}

						var v = form.getFieldValues();
						if(v.customFields) {
							action.options.params = action.options.params || {};
							action.options.params.customFieldsJSON = Ext.encode(v.customFields);
						}

						return true;
					});
					form.legacyParamAdded = true;
				}
				
				form.getForm().on("actioncomplete", function(f, action) {
					if(action.type === "load") {
						this.filter(f.getFieldValues());						
					}
				}, this);
			}
			
			
		}, this);

		go.modules.core.core.FormFieldSet.superclass.initComponent.call(this);
	},

	/**
	 * Show this fieldset by filtering the entity values.
	 * 
	 * @param {object} entity
	 * @returns {undefined}
	 */
	filter: function (entity) {
		for (var name in this.fieldSet.filter) {
			var v = this.fieldSet.filter[name];

			if (Ext.isArray(v)) {
				if (v.indexOfLoose(entity[name]) === -1) {
					this.setVisible(false);
					return;
				}
			} else
			{
				if (v != entity[name]) {
					this.setVisible(false);
					return;
				}
			}
		}		
		this.setVisible(true);
	}
});

Ext.reg("customformfieldset", go.modules.core.core.FormFieldSet);