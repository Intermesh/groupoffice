/* global Ext, go */

go.customfields.FormFieldSet = Ext.extend(Ext.form.FieldSet, {
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
		
		items = items.concat(go.customfields.CustomFields.getFormFields(this.fieldSet.id));
		
		Ext.apply(this, {
			title: this.fieldSet.name,
			items: items,
			stateId: 'cf-form-' +  (this.fieldSet.isTab ? "tab-" : 'field-set-' )  + this.fieldSet.id,
			stateful: true,
			collapsible: true
		});

		this.on("expand", function() {
			this.doLayout();
		}, this);

		this.on("afterrender", function() {


			//find entity panel
			var form = this.findParentByType("form");
			
			this.formTabPanel = this.findParentByType('tabpanel');
			
			if(!form) {
				console.error("No go.form.EntityPanel found for filtering");
				return;
			}
						
			if(form.getXType() == "entityform") {
				form.on("load", function () {
					this.filter(form.getValues());
				}, this);

				form.on("setvalues", function () {					
					this.filter(form.getValues());
				}, this);

				this.filter(form.getValues());
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

		go.customfields.FormFieldSet.superclass.initComponent.call(this);
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
					this.setFilterVisible(false);
					return;
				}
			} else
			{
				if (v != entity[name]) {
					this.setFilterVisible(false);
					return;
				}
			}
		}		
		this.setFilterVisible(true);
	},
	
	setFilterVisible : function(v) {
		
		if(!this.fieldSet.isTab) {
			this.setVisible(v);
			this.setDisabled(!v);
		} else{
			if(v) {
			 	this.formTabPanel.unhideTabStripItem(this.ownerCt);
			} else
			{
			 	this.formTabPanel.hideTabStripItem(this.ownerCt);
			}		
		}		
	
	}
});
	
	

Ext.reg("customformfieldset", go.customfields.FormFieldSet);