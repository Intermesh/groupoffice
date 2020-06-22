Ext.ns("go.customfields.type");

go.customfields.type.Text = Ext.extend(Ext.util.Observable, {
	
	name : "Text",
	
	label: t("Text"),
	
	iconCls: "ic-description",	
	
	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog : function() {
		return new go.customfields.type.TextDialog();
	},
	
	/**
	 * Render's the custom field value for the detail views
	 * 
	 * If nothing is returned then you must manage the value in the function itself
	 * by calling detailComponent.setValue();
	 * 
	 * See User.js for an asynchronous example.
	 * 
	 * @param {mixed} value
	 * @param {object} data Complete entity
	 * @param {object} customfield Field entity from custom fields
	 * @param {go.detail.Property} detailComponent The property component that renders the value
	 * @returns {string}|undefined
	 */
	renderDetailView: function (value, data, customfield, detailComponent) {
		return Ext.util.Format.htmlEncode(value);
	},
	
	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig : function( customfield, config) {
		config = config || {};

		if (!GO.util.empty(customfield.options.validationRegex)) {

			if (!GO.util.empty(customfield.options.validationModifiers))
				config.regex = new RegExp(customfield.options.validationRegex, customfield.options.validationModifiers);
			else
				config.regex = new RegExp(customfield.options.validationRegex);
		}

		if (!GO.util.empty(customfield.hint)) {
			config.hint = customfield.hint;
		}

		var fieldLabel = customfield.name;

		if (!GO.util.empty(customfield.prefix))
			fieldLabel = fieldLabel + ' (' + customfield.prefix + ')';

		if (!GO.util.empty(customfield.required)) {
			fieldLabel += '*';
		}

		if (customfield.options.maxLength) {
			config.maxLength = customfield.options.maxLength;
		}

		if (!GO.util.empty(customfield.relatedFieldCondition)) {
			config.checkRequiredCondition = this.checkRequiredCondition;
			if (customfield.type === 'Select') {
				config.validate = function () {
					this.checkRequiredCondition.call(this, customfield);
					return go.customfields.type.TreeSelectField.prototype.validate.apply(this);
				}
			} else if (customfield.type === 'MultiSelect') {
				config.validate = function () {
					this.checkRequiredCondition.call(this, customfield);
					return go.form.Chips.prototype.validate.apply(this);
				}
			} else {
				config.validateValue = function () {

					this.checkRequiredCondition.call(this, customfield, this);
					return Ext.form.Field.prototype.validateValue.apply(this);
				}
			}
		}

		return Ext.apply({
			xtype: 'textfield',
			serverFormats: false, //for backwards compatibility with old framework. Can be removed when all is refactored.
			name: 'customFields.' + customfield.databaseName,
			fieldLabel: fieldLabel,
			anchor: '100%',
			allowBlank: !customfield.required,
			value: customfield.default,
			hidden: customfield.conditionallyHidden || false,
			conditionallyHidden: customfield.conditionallyHidden || false,
			conditionallyRequired: customfield.conditionallyRequired || false
		}, config);

		//ALTER TABLE `core_customfields_field`
		// ADD `conditionallyHidden` tinyint(1) NOT NULL DEFAULT '0' AFTER `requiredCondition`;
	},

	// /**
	//  *
	//  * @param field
	//  * @returns {boolean}
	//  * @private
	//  */
	// _changeRelatedFieldsVisibility: function(field) {
	// 	// if (!field.relatedFields) {
	// 	// 	return false;
	// 	// }
	// 	//
	// 	// // relatedFields are populated in FormFieldSet. The validation of this field can affect visibility of other fields
	// 	// // using this fields value in the requiredCondition
	// 	// Ext.each(field.relatedFields, function(relatedField) {
	// 	// 	relatedField.validate();
	// 	// }, field);
	// },

	/**
	 * @param customField
	 * @returns {*}
	 */
	getRequiredConditionField: function(customField) {
		var condition = customField.field.relatedFieldCondition,
			form = this.findParentByType('form').getForm(),
			conditionParts,
			field, fieldName;

		if (Ext.isEmpty(condition)) {
			return false;
		}

		if (condition.includes('is empty')) {
			condition = condition.replace('is empty', '');
			fieldName = condition.trim(' ');
			field = form.findField(fieldName) || form.findField('customFields.' + fieldName);
		} else if (condition.includes('is not empty')) {
			condition = condition.replace('is not empty', '');
			fieldName = condition.trim(' ');
			field = form.findField(fieldName) || form.findField('customFields.' + fieldName);
		} else {
			conditionParts = condition.split(' ');
			if (conditionParts.length === 3) { //valid condition
				field = form.findField(conditionParts[0]) || form.findField('customFields.' + conditionParts[0]);
				if (!field) {
					field = form.findField(conditionParts[2]) || form.findField('customFields.' + conditionParts[2]);
				}
			}
		}

		return field;
	},

	/**
	 * Required condition validator
	 *
	 * @param customfield
	 */
	checkRequiredCondition: function (customfield) {

		this.requiredConditionMatches = false;

		var condition = customfield.relatedFieldCondition,
			form,
			conditionParts,
			isEmptyCondition = false,
			isNotEmptyCondition = false,
			field, fieldName, operator,
			value, fieldValue;

		if (Ext.isEmpty(condition)) {
			return false;
		}

		form = this.findParentByType('form').getForm();

		if (condition.includes('is empty')) {
			isEmptyCondition = true;
			condition = condition.replace('is empty', '');
			fieldName = condition.trim(' ');
			field = form.findField(fieldName) || form.findField('customFields.' + fieldName);
		} else if (condition.includes('is not empty')) {
			isNotEmptyCondition = true;
			condition = condition.replace('is not empty', '');
			fieldName = condition.trim(' ');
			field = form.findField(fieldName) || form.findField('customFields.' + fieldName);
		} else {
			conditionParts = condition.split(' ');
			if (conditionParts.length > 2) { //valid condition
				fieldName = conditionParts.shift();
				operator = conditionParts.shift();
				field = form.findField(fieldName) || form.findField('customFields.' + fieldName);
				value = conditionParts.join(" ").trim();
			}
		}

		if (!field) {
			return false;
		}

		fieldValue = field.getRawValue ? field.getRawValue() : field.getValue();


		if (field.xtype === 'xcheckbox' || field.xtype === 'checkbox') {
			fieldValue = fieldValue | 0;
			if(value === "true") {
				value = 1;
			} else if(value === "false") {
				value = 0;
			}
		}


		console.log(fieldValue, value, operator);

		if (isEmptyCondition) {
			this.requiredConditionMatches = !Ext.isEmpty(fieldValue);
		} else if (isNotEmptyCondition) {
			this.requiredConditionMatches = Ext.isEmpty(fieldValue);
		} else {
			switch (operator) {
				case '=':
				case '==':
					this.requiredConditionMatches = (fieldValue == value);
					break;
				case '>':
					this.requiredConditionMatches = (fieldValue > value);
					break;
				case '<':
					this.requiredConditionMatches = (fieldValue < value);
					break
			}
		}

		var customFieldCmp = this;

		if(customfield.conditionallyRequired) {
			customFieldCmp.allowBlank = !this.requiredConditionMatches;
			if (this.xtype === 'treeselectfield') {
				this.items.itemAt(0).allowBlank = !this.requiredConditionMatches;
			}
		}

		if (!customfield.conditionallyHidden) {
			return this.requiredConditionMatches;
		}

		if (this.requiredConditionMatches) {
			customFieldCmp.show();
		} else {
			customFieldCmp.hide();
		}

		customFieldCmp.ownerCt.doLayout();

		return this.requiredConditionMatches;
	},
	
	/**
	 * Return's a form field to render
	 * 
	 * @param {object} customfield
	 * @param {object} config
	 * @returns {Ext.form.Field}
	 */
	renderFormField: function (customfield, config) {		
		var field = this.createFormFieldConfig(customfield, config);
		return this.applySuffix(customfield, field);
	},

	/**
	 * Returns a component to display the custom field value on the go.detail.Panel of a contact for example.
	 * When a contact loads the component is set with a value from
	 * @see this.renderDetailView()
	 *
	 * @param customfield
	 * @param config
	 */
	getDetailField: function(customfield, config) {
		return new go.detail.Property({
			itemId: customfield.databaseName,
			label: customfield.name,
			icon: this.iconCls
		});
	}, 
	
	applySuffix: function (customfield, field) {
		if (!GO.util.empty(customfield.suffix)) {
			field.flex = 1;
			delete field.anchor;
			var hint = field.hint;
			delete field.hint;
			return {
				hint: hint,
				anchor: '-20',
				xtype: 'compositefield',
				fieldLabel: field.fieldLabel,
				items: [field, {
						xtype: 'label',
						text: customfield.suffix,
//							hideLapplySuffixabel: true,
						columnWidth: '.1'
					}]
			};
		} else {
			return field;
		}
	},
	
	getFieldType : function() {
		return "string";
	},
	
	/**
	 * Get the field definition for creating Ext.data.Store's
	 * 
	 * Also the customFieldType (this) and customField (Entity Field) are added
	 * 
	 * @see https://docs.sencha.com/extjs/3.4.0/#!/api/Ext.data.Field
	 * @returns {Object}
	 */
	getFieldDefinition : function(field) {
		return {
			name: "customFields." + field.databaseName,
			type: this.getFieldType(),
			customField: field,
			customFieldType: this,
			columnxtype: this.getColumnXType()
		};
	},

	getRelations : function(customfield) {
		return {};
	},
	
	/**
	 * Get grid column defnitition
	 * 
	 * @param {type} field
	 * @returns {TextAnonym$0.getColumn.TextAnonym$6}
	 */
	getColumn : function(field) {
		
		var def = this.getFieldDefinition(field);
		return {
			dataIndex: def.name,
			header: def.customField.name,
			hidden: def.customField.hiddenInGrid,
			id: "custom-field-" + encodeURIComponent(def.customField.databaseName),
			sortable: true,
			hideable: true,
			draggable: true,
			xtype: this.getColumnXType()
		};
	},

	getFilterCmp : function(field) {
		var cmp =  this.createFormFieldConfig(field);
		cmp.name = cmp.hiddenName = field.databaseName;
		return cmp;
	},


	
	/**
	 * See https://docs.sencha.com/extjs/3.4.0/#!/api/Ext.grid.Column-cfg-xtype
	 * @returns {String}
	 */
	getColumnXType : function() {
		return "gridcolumn";
	},
	
	getFilter : function(field) {
		return {
			name: field.databaseName,
			type: "string",
			wildcards: true,
			multiple: true,
			title: field.name,
			customfield: field
		};
	}
	
	
});

// go.customfields.CustomFields.registerType(new go.customfields.type.Text());
