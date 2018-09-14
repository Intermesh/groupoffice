(function () {
	var types = {};
	var CustomFieldsCls = Ext.extend(Ext.util.Observable, {
		initialized: false,
		
		
		//init is called in GO.MainLayout.onAuthneticatiojn so custom fields are 
		//always available when modules render.
		init : function() {

			go.Stores.get("Field").getUpdates(function (store) {

			});

			go.Stores.get("FieldSet").getUpdates(function (store) {

			});
		},
		
		registerType : function(type) {
			types[type.name] = type;
		},
		
		getType : function(name) {
			return types[name] || null;
		},
		
		
		getTypes : function() {
			return types;
		},
		
		/**
		 * Get field set entitiues
		 * @param {string} entity eg. "note"
		 * @returns {Array}
		 */
		getFieldSets: function (entity) {
			var r = [],
							all = go.Stores.get("FieldSet").data;

			for (var id in all) {
				if (all[id].entity === entity) {
					r.push(all[id]);
				}
			}

			return r;
		},
		
		/**
		 * Get all Ext.data.Store field definitions for an entity's custom fields
		 * 
		 * @param {string} entity eg. "Contact"
		 * @returns {Array}
		 */
		getFieldDefinitions : function(entity) {
			
			var defs = [], me = this, type;
			
			this.getFieldSets(entity).forEach(function(fs) {
				me.getFields(fs.id).forEach(function(field) {					
					type = me.getType(field.type);
					defs.push(type.getFieldDefinition(field));
				});
			});
			return defs;
		},
		
		/**
		 * Get all Ext.grid.Column definitions for an entity's custom fields
		 * @param {string} entity eg. "Contact"
		 * @returns {Array}
		 */
		getColumns : function(entity) {
			var cols = [];
			
			this.getFieldDefinitions(entity).forEach(function(def) {
				cols.push({
					dataIndex: def.name,
					header: def.customField.name,
					hidden: true,
					id: "custom-field-" + encodeURIComponent(def.customField.databaseName),
					sortable: true,
					hideable: true,
					draggable: true
				})
			});
			
			return cols;
		},

		/**
		 * Get form fieldsets
		 * 
		 * @param {string} entity eg. "note"
		 * @returns {Array}
		 */
		getFormFieldSets: function (entity) {
			var fieldSets = this.getFieldSets(entity), formFieldSets = [];

			for (var i = 0, l = fieldSets.length; i < l; i++) {
				formFieldSets.push(new go.modules.core.customfields.FormFieldSet({fieldSet: fieldSets[i]}));				
			}
			return formFieldSets;
		},

		/**
		 * Get form fields for field set
		 * 
		 * @param {int} fieldSetId
		 * @returns {Array}
		 */
		getFormFields: function (fieldSetId) {
			var r = [],
							all = go.Stores.get("Field").data,
							field,
							formField, 
							type;

			for (var id in all) {
				field = all[id];
				if (field.fieldSetId == fieldSetId) {		
					type = this.getType(field.type);
					if(!type) {
						console.error("Custom field type " + field.type + " not found");
						continue;
					}
					
					formField = type.renderFormField(field);
					r.push(formField);
				}
			}

			return r;
		},

		/**
		 * Get field entities
		 * 
		 * @param {int} fieldSetId
		 * @returns {Array}
		 */
		getFields: function (fieldSetId) {
			var r = [],
							all = go.Stores.get("Field").data,
							field;

			for (var id in all) {
				field = all[id];
				if (field.fieldSetId == fieldSetId) {
					r.push(field);
				}
			}

			return r;
		},

		/**
		 * Render a field for the detail view
		 * 
		 * @param {int} fieldId
		 * @param {Object} values
		 * @returns {CustomFieldsL#1.CustomFieldsAnonym$0.render.values}
		 */
		renderField: function (fieldId, values) {
			var field = go.Stores.get("Field").data[fieldId];

			type = this.getType(field.type);
			if(!type) {							
				console.error("Custom field type " + field.type + " not found");
				return "";
			}

			return type.renderDetailView(values[field.databaseName], values, field);			
		},

		/**
		 * Get a field's icon
		 * 
		 * @param {int} fieldId
		 * @returns {String} The material design icon text
		 */
		getFieldIcon: function (fieldId) {
			var field = go.Stores.get("Field").data[fieldId];
			
			type = this.getType(field.type);
			if(!type) {							
				console.error("Custom field type " + field.type + " not found");
				return "";
			}

			return type.iconCls;
		},

		/**
		 * Add panels to detail view
		 * 
		 * @param {string} entity eg. "Contact"
		 * @returns {Array}
		 */
		getDetailPanels: function (entity) {
			
			var fieldSets = go.modules.core.customfields.CustomFields.getFieldSets(entity), panels = [];

			fieldSets.forEach(function (fieldSet) {
				var tpl = '<tpl for="customFields"><div class="icons">';

				go.modules.core.customfields.CustomFields.getFields(fieldSet.id).forEach(function (field) {
					tpl += '<tpl if="!GO.util.empty(go.modules.core.customfields.CustomFields.renderField(\'' + field.id + '\',values))"><p><i class="icon label ' + go.modules.core.customfields.CustomFields.getFieldIcon(field.id) + '"></i>\
				<span>{[go.modules.core.customfields.CustomFields.renderField("' + field.id + '",values)]}</span>\
					<label>' + t(field.name) + '</label>\
					</p><hr /></tpl>';
				});

				tpl += '</div></tpl>';

				panels.push({						
					id: "cf-detail-field-set-" + fieldSet.id,
					fieldSetId: fieldSet.id,
					title: fieldSet.name,
					tpl: tpl,
					collapsible: true,
					onLoad: function(dv) {

						var vis = false;							
						go.modules.core.customfields.CustomFields.getFields(fieldSet.id).forEach(function (field) {
							if(!GO.util.empty(dv.data.customFields[field.databaseName])) {
								vis = true;
							}
						});

						this.setVisible(vis);
					}
				});
			});			
			
			return panels;
		}
	});

	go.modules.core.customfields.CustomFields = new CustomFieldsCls;

})();


