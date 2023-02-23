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
		return  go.util.textToHtml(value);
	},
	
	/**
	 * Returns config object to create the form field
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig : function( customfield, config) {
		config = config || {};

		var required = customfield.required;

		if (!go.util.empty(customfield.options.validationRegex)) {

			if (!go.util.empty(customfield.options.validationModifiers)) {
				config.regex = new RegExp(customfield.options.validationRegex, customfield.options.validationModifiers);
			} else {
				config.regex = new RegExp(customfield.options.validationRegex);
			}
		}

		if (!go.util.empty(customfield.hint)) {
			config.hint = customfield.hint;
		}

		var fieldLabel = customfield.name;

		if (!go.util.empty(customfield.prefix)) {
			fieldLabel = fieldLabel + ' (' + customfield.prefix + ')';
		}

		if (customfield.options.maxLength) {
			config.maxLength = customfield.options.maxLength;
		}



		if (!go.util.empty(customfield.relatedFieldCondition)) {
			config.listeners = config.listeners || {};
			config.checkRequiredCondition = this.checkRequiredCondition;
			config.getConditionString = this.getConditionString;
			config.getFormValue = this.getFormValue;
			config.listeners.valid = function(field) {
				field.checkRequiredCondition();
			};

			config.listeners.invalid = function(field) {
				field.checkRequiredCondition();
			};
		}

		return Ext.apply({
			customfield: customfield,
			xtype: 'textfield',
			serverFormats: false, //for backwards compatibility with old framework. Can be removed when all is refactored.
			name: 'customFields.' + customfield.databaseName,
			fieldLabel: fieldLabel + (required ? '*' : ''),
			anchor: '100%',
			allowBlank: !required,
			value: customfield.default,
			hidden: customfield.conditionallyHidden || false
			// conditionallyHidden: customfield.conditionallyHidden || false,
			// conditionallyRequired: customfield.conditionallyRequired || false
		}, config);
	},

	/**
	 * Try to get a field value from the current form element. If a form element does not exist, merely return an empty
	 * string. Maybe give an alert if a field does not exist?
	 *
	 * @param {string} fieldName
	 * @returns {string}
	 */
	getFormValue: function (fieldName) {
		var form = this.findParentByType('form').getForm();
		var field = form.findField(fieldName) || form.findField('customFields.' + fieldName);
		if (!field) {
			console.warn("Field " + fieldName + ' not found in string.'); // TODO: Alert?
			return ''; // As yet, return an empty string if a field is not found
		}
		let fieldValue = field.getRawValue ? field.getRawValue() : field.getValue();

		switch (field.xtype) {
			case 'xcheckbox':
			case 'checkbox':
				fieldValue = fieldValue | 0;
				if (fieldValue === "true") {
					fieldValue = '1';
				} else if (fieldValue === "false") {
					fieldValue = '0';
				}
				break;
			case 'treeselectfield':
			case 'chips':
				if(fieldValue === null) {
					fieldValue = "";
				}
				break
			default:
				break;
		}

		return String(fieldValue); // TODO: Actually string?
	},

	/**
	 * Break a condition string into conditions and grouped subconditions
	 *
	 * @param {string} conditionString
	 * @returns {array}
	 */
	getConditionString: function(conditionString) {
		conditionString = conditionString.replace(/\sAND\s/g, ' && ')
			.replace(/\sOR\s/g, ' || ')
			.replace(/\s=\s/g,' == ');

		var arOperators = ['==', '<', '>', '>=', '<=', '!='], arCnd = conditionString.split(' '),
			arLogicalOperators = ['&&', '||'];

		for (var ii = 0, il = arCnd.length; ii < il; ii++) {
			var currWord = arCnd[ii], prevIdx = ii - 1, nextIdx = ii + 1, prefix = '', postfix = '';
			if (arOperators.indexOf(currWord) > -1) {
				var prevWord = String(arCnd[prevIdx]);
				if (prevWord.charAt(0) === '(') {
					prefix = '(';
					prevWord = prevWord.substr(1);
				}
				arCnd[prevIdx] = prefix + 'this.getFormValue("' + prevWord + '")';
				arCnd[nextIdx] = '"' + arCnd[nextIdx];
			} else if (arLogicalOperators.indexOf(currWord) > -1) {
				var prevWord = String(arCnd[prevIdx]);
				if (prevWord.indexOf(')') === prevWord.length - 1) {
					postfix = ')';
					prevWord = prevWord.substring(0, prevWord.length - 1);
				}
				if(prevWord !== 'empty') {
					postfix = '"' + postfix;
				}
				arCnd[prevIdx] = prevWord + postfix;
			} else if (ii === il - 1) {
				if (currWord.indexOf(')') === currWord.length - 1) {
					postfix = ')';
					currWord = currWord.substring(0, currWord.length - 1);
				}
				// Catch is empty / is not empty conditions
				if(currWord !== 'empty') {
					currWord = currWord + '"';
				}
				arCnd[ii] = currWord + postfix;
			}
		}
		conditionString = arCnd.join(' ');

		var reNotEmptyCondition = new RegExp(/(?<fldName>\w+) is not empty/, 'g'),
			match = reNotEmptyCondition.exec(conditionString);
		do {
			if(match !== null) { // Y THO
				conditionString = conditionString.replace(reNotEmptyCondition, '!Ext.isEmpty(this.getFormValue("' + match.groups.fldName + '"))');
			}
		} while ((match = reNotEmptyCondition.exec(conditionString)) !== null);

		var reEmptyCondition = new RegExp(/(?<fldName>\w+) is empty/, 'g'),
			rematch = reEmptyCondition.exec(conditionString);
		do {
			if(rematch !== null) { // Y THO
				conditionString = conditionString.replace(reEmptyCondition, 'Ext.isEmpty(this.getFormValue("' + rematch.groups.fldName + '"))');

			}
		} while ((rematch = reEmptyCondition.exec(conditionString)) !== null);

		return "return ("+conditionString + ");";
	},

	/**
	 * Required condition validator
	 *
	 */
	checkRequiredCondition: function () {
		this.requiredConditionMatches = false;

		if (Ext.isEmpty(this.customfield.relatedFieldCondition)) {
			return false;
		}

		var strConditionString = this.getConditionString(this.customfield.relatedFieldCondition);

		try {
			var func = new Function(strConditionString);
			this.requiredConditionMatches = func.call(this);
		} catch(e) {
			console.error("Required condition '" + this.customfield.relatedFieldCondition + "' failed with error: " + e);
		}

		if (this.customfield.conditionallyRequired) {
			this.allowBlank = !this.requiredConditionMatches;
			this.customfield.allowBlank = !this.requiredConditionMatches;
			this.customfield.fieldLabel = this.customfield.name + (this.requiredConditionMatches ? '*' : '');
			if (this.xtype === 'treeselectfield') {
				this.items.itemAt(0).allowBlank = !this.requiredConditionMatches;
			}
		}

		if (!this.customfield.conditionallyHidden) {
			return this.requiredConditionMatches;
		}

		if (! this.requiredConditionMatches) {
			this.show();
		} else {
			this.hide();
		}

		this.ownerCt.doLayout();

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
			mapping: function(obj) {
				//required for customfields starting with numbers. It's no longer possible to create them but existing customers might have them.
				return obj.customFields[field.databaseName];
			},
			customField: field,
			customFieldType: this,
			columnxtype: this.getColumnXType()
		};
	},

	getRelations : function(customfield) {
		return {};
	},
	
	/**
	 * Get grid column definition
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

