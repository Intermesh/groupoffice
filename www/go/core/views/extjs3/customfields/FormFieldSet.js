/* global Ext, go */

go.customfields.FormFieldSet = Ext.extend(Ext.form.FieldSet, {
	fieldSet: null,
	hideMode: 'offsets',
	layout: "column",
	initComponent: function () {

		if(GO.util.isMobileOrTablet()) {
			this.fieldSet.columns = 1;
		}
		
		var items = [];
		
		if(this.fieldSet.description) {
			items.push({
				xtype: "box",
				autoEl: "p",
				columnWidth: 1,
				html: go.util.textToHtml(this.fieldSet.description)
			});
		}

		var fields = go.customfields.CustomFields.getFormFields(this.fieldSet.id);

		// add field sets that should be added to this tab
		go.customfields.CustomFields.getFieldSets(this.fieldSet.entity).forEach((fs) => {
			if( fs.parentFieldSetId == this.fieldSet.id) {
				fields.push(new go.customfields.FormFieldSet({fieldSet: fs, layout: "column"}));
			}
		});

		var c = fields.length;
		var fieldsPerColumn = Math.floor(c / this.fieldSet.columns);
		var fieldsInFirstColumn = fieldsPerColumn + (c % this.fieldSet.columns);

		this.defaults = {
			xtype: "container",
			labelAlign: "top",
			columnWidth: 1 / this.fieldSet.columns,
			layout: "form"
		};

		var currentCol = {items: []},
			colItemCount = 0,
			me = this,
			max = fieldsInFirstColumn;

		fields.forEach(function (field) {
			currentCol.items.push(field);
			colItemCount++;
			if(colItemCount == max) {
				items.push(currentCol);
				currentCol = {items: [], style: "padding-left: " +dp(16) + "px"};
				colItemCount = 0;
				max = fieldsPerColumn;
			}
		});
		items.push(currentCol);
		
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

		this.on("added", () => {
			setTimeout(() => {
				this.setupFilter();
			});
			}, this);

		this.on('render', () => {
			this.formTabPanel = this.findParentByType('tabpanel');
			const form = this.findParentByType("form");
			if(!form || form.changeListenersAdded2) return;
			form.changeListenersAdded2 = true;
			if (form.getXType() == "entityform") {
				form.on("setvalues",  () => {
					this.load(form, form.getValues(), fields);
				});
			} else {
				form.getForm().on("beforeaction", (form, action) => {
					if (action.type === "load") {
						this.load(form, form.getFieldValues(), fields);
					}
				});
			}
		});

		this.supr().initComponent.call(this);
	},

	load(form, values, customFields) {

		if(!this.fieldSet.isTab && this.fieldSet.collapseIfEmpty) {
			let isModified = false;
			for (const field of customFields) {

				const nameProp = field.name ? "name" : "hiddenName";

				const name = field[nameProp].replace('customFields.', '');
				if (name) {
					if (!(name in values.customFields) || values.customFields[name] == field.value ||
						(Ext.isEmpty(values.customFields[name]) && Ext.isEmpty(field.value))) {
						// not modified
					} else {
						isModified = true;
						break;
						//console.log('modified', name, field.value, '!=', values.customFields[name]);
					}
				}
			}
			if(!isModified)
				this.collapse();
			else
				this.expand();
		}
		// for(const name in values.customFields) {
		// 	if(customFields)
		// }
	},

	setupFilter: function() {
		//find entity panel
		var form = this.findParentByType("form");

		this.formTabPanel = this.findParentByType('tabpanel');

		if (!form) {
			//console.error("No go.form.EntityPanel found for filtering");
			return;
		}

		var me = this;
		//Add a beforeaction event listener that will send the custom field data JSON encoded.
		//The old framework will use this to save custom fields.
		if (!form.changeListenersAdded) {

			form.changeListenersAdded = true;

			if (form.getXType() == "entityform") {
				form.on("setvalues", function () {
					this.filter(form.getValues());
					// form.isValid();
					form.getForm().items.each( (field) => {
						if(field.checkRequiredCondition)
							field.checkRequiredCondition();
					});
				}, this);
			} else {
				//Legacy code
				form.getForm().on("beforeaction", function (form, action) {
					if (action.type !== "submit") {
						return true;
					}

					var v = form.getFieldValues();
					if (v.customFields) {
						action.options.params = action.options.params || {};
						action.options.params.customFieldsJSON = Ext.encode(v.customFields);
					}

					return true;
				});

				form.getForm().on("actioncomplete", function (f, action) {
					if (action.type === "load") {
						f.isValid(); //needed for conditionally hidden
					}
				}, this);

			}

			form.getForm().items.each( (field) => {
				field.on('change', (field) => {
					form.getForm().isValid();
				});
				field.on('check', (field, checked) => {
					form.getForm().isValid();
				});
			});
		}

		if (form.getXType() == "entityform" || form.ownerCt instanceof go.usersettings.UserSettingsDialog) {

			form.getForm().items.each( (field) => {
				field.on('change', (field) => {
					this.filter(form.getValues());
				});
			});

			form.on("setvalues", function () {

				this.filter(form.getValues());
			}, this);

			this.filter(form.getValues());

			// problem this marks fields invalid immediately and fields loose focus
			// form.isValid();

			form.getForm().items.each( (field) => {
				if(field.checkRequiredCondition)
					field.checkRequiredCondition();
			});

		} else {
			form.getForm().on("actioncomplete", function (f, action) {
				if (action.type === "load") {
					this.filter(f.getFieldValues());
				}
			}, this);

			form.getForm().items.each( (field) => {
				field.on('change', (field) => {
					this.filter(form.getForm().getValues());
				});
			});
		}



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
			} else {
				if (v != entity[name]) {
					this.setFilterVisible(false);
					return;
				}
			}
		}		
		this.setFilterVisible(true);
	},

	setFilterVisible : function(v) {
		//disable recursive so validators don't apply on hidden items
		function setDisabled(ct, v) {
			ct.setDisabled(v);

			if(!ct.items){
				return;
			}
			ct.items.each(function(i) {
				setDisabled(i, v);
			});
		}

		if(!this.isTab) {
			this.setVisible(v);
			setDisabled(this, !v);
		} else{
			setDisabled(this.ownerCt, !v);
			this.formTabPanel = this.formTabPanel || this.findParentByType('tabpanel');
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
