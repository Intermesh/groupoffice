(function () {
	var types = {};
	var CustomFieldsCls = Ext.extend(Ext.util.Observable, {
		initialized: false,
		
		fieldSets: null,
		fields: null,
		
		//init is called in GO.MainLayout.onAuthneticatiojn so custom fields are 
		//always available when modules render.
		init : function(cb, scope) {
			
			var me = this, scope = scope || me;

			me.loadModuleTypes();
			
			return new Promise(function(resolve, reject){
			
				scope = scope || me;

				

				go.Db.store("Field").all(function (success, fields) {
					me.fields = fields;

					if(me.fieldSets) {
						if(cb) {
							cb.call(scope);
						}
						success ? resolve(me) : reject(me);
					}				
				}, me);

				go.Db.store("FieldSet").all(function (success, fieldSets) {
					
					me.fieldSets = fieldSets;
					if(me.fields) {
						if(cb) {
							cb.call(scope);
						}
						success ? resolve(me) : reject(me);
					}
				}, me);
			
			});
		},

		

		loadModuleTypes : function() {
    
			var available = go.Modules.getAvailable(), pnl, config, i, i1;
			
			for(i = 0, l = available.length; i < l; i++) {
				
				config = go.Modules.getConfig(available[i].package, available[i].name);
				
				if(!config.customFieldTypes) {
					continue;
				}
				
				for(i1 = 0, l2 = config.customFieldTypes.length; i1 < l2; i1++) {
					pnl = eval(config.customFieldTypes[i1]);				
					var type = new pnl;
					types[type.name] = type;
				}
			}
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
			var r = [];

			for (var id in this.fieldSets) {
				if (this.fieldSets[id].entity === entity) {
					r.push(this.fieldSets[id]);
				}
			}
			
			return r.sort(function(a, b) {
				if (a.sortOrder === b.sortOrder) {
					return 0;
				}	else {
						return (a.sortOrder < b.sortOrder) ? -1 : 1;
				}
			});
		},
		
		/**
		 * Get filter definitions for tbsearch and user filter dialogs
		 * 
		 * @param {string} entity
		 * @returns {Array}
		 */
		getFilters : function(entity) {
			var defs = [], me = this, type;
			
			this.getFieldSets(entity).forEach(function(fs) {
				me.getFields(fs.id).forEach(function(field) {					
					type = me.getType(field.type);
					if(!type) {
						console.error("Custom field type " + field.type + " not found");
						return;
					}
					var def = type.getFilter(field);
					if(def) {
						defs.push(def);
					}
				});
			});
			return defs;
		},

		/**
		 * Get filter definitions for tbsearch and user filter dialogs
		 * 
		 * @param {string} entity
		 * @returns {Array}
		 */
		getRelations : function(entity) {
			var relations = {}, me = this, type;
			
			this.getFieldSets(entity).forEach(function(fs) {
				me.getFields(fs.id).forEach(function(field) {					
					type = me.getType(field.type);
					if(!type) {
						console.error("Custom field type " + field.type + " not found");
						return;
					}
					Ext.apply(relations,  type.getRelations(field));
				});
			});
			return relations;
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
					if(!type) {
						console.error("Custom field type " + field.type + " not found");
						return;
					}
					var def = type.getFieldDefinition(field);
					
					defs.push(def);
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
			var cols = [], me = this, type;
			
			this.getFieldSets(entity).forEach(function(fs) {
				me.getFields(fs.id).forEach(function(field) {					
					type = me.getType(field.type);
					if(!type) {
						console.error("Custom field type " + field.type + " not found");
						return;
					}
					var col = type.getColumn(field);

					if(col) {
						cols.push(col);
					}
				});
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
				formFieldSets.push(new go.customfields.FormFieldSet({fieldSet: fieldSets[i]}));				
			}
			return formFieldSets;
		},
		
		/**
		 * Show or hide fieldsets based on their "filter" property. 
		 * See FieldSet::getFilter() in the PHP documentation
		 * 
		 */
		filterFieldSets : function(formPanel) {
			var values = formPanel instanceof go.form.EntityPanel ? formPanel.getValues() : formPanel.getForm().getFieldValues();
			formPanel.findByType("customformfieldset").forEach(function(fs){
				fs.filter(values);
			}, this);
		},

		/**
		 * Get form fields for field set
		 * 
		 * @param {int} fieldSetId
		 * @returns {Array}
		 */
		getFormFields: function (fieldSetId) {
			var r = [],
							fields = this.getFields(fieldSetId),
							me = this;

			fields.forEach(function(field){
				var type = me.getType(field.type);
				if(!type) {
					console.error("Custom field type " + field.type + " not found");
					return;
				}
				var formField = type.renderFormField(field);
				formField.field = field;
				r.push(formField);						
			});

			return r;
		},

		/**
		 * Get field entities
		 * 
		 * @param {int} fieldSetId
		 * @returns {Array}
		 */
		getFields: function (fieldSetId) {
			var r = [],	field;

			for (var id in this.fields) {
				field = this.fields[id];
				if (field.fieldSetId == fieldSetId) {
					r.push(field);
				}
			}

			return r.sort(function(a, b) {
				if (a.sortOrder === b.sortOrder) {
					return 0;
				}	else {
						return (a.sortOrder < b.sortOrder) ? -1 : 1;
				}
			});
		},

		/**
		 * Render a field for the detail view
		 * 
		 * @param {int} fieldId
		 * @param {Object} values
		 * @returns {CustomFieldsL#1.CustomFieldsAnonym$0.render.values}
		 */
		renderField: function (fieldId, values) {
			var field = this.fields[fieldId];

			var type = this.getType(field.type);
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
			var field = this.fields[fieldId];
			
			var type = this.getType(field.type);
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
			
			var fieldSets = this.getFieldSets(entity), panels = [], me = this;

			fieldSets.forEach(function (fieldSet) {
				
				var items = [];		
				
				
				go.customfields.CustomFields.getFields(fieldSet.id).forEach(function (field) {					
					var type = me.getType(field.type);
					if(!type) {
						console.error("Custom field type " + field.type + " not found");
						return;
					}
					var cmp = type.getDetailField(field);					
					cmp.field = field;
					items.push(cmp);
				});
				
				panels.push({				
					xtype: "panel",
					stateId: "cf-detail-field-set-" + fieldSet.id,
					fieldSetId: fieldSet.id,
					title: fieldSet.name,
					bodyCssClass: 'icons',
					items: items,
					collapsible: true,
					onLoad: function(dv) {
						var vis = false, panel = this;							
						go.customfields.CustomFields.getFields(fieldSet.id).forEach(function (field) {
							if(!GO.util.empty(dv.data.customFields[field.databaseName])) {
								vis = true;
							}
							
							var cmp = panel.getComponent(field.databaseName), type = me.getType(field.type);
							
							if(cmp) {
								var v = type.renderDetailView(dv.data.customFields[field.databaseName], dv.data.customFields, field, cmp);
					
								if(typeof(v) !== "undefined") {
									cmp.setValue(v);
									cmp.setVisible(!!v);
								}
							}
						});

						this.setVisible(vis);				
					}
				});
			});			
			
			return panels;
		}
	});

	go.customfields.CustomFields = new CustomFieldsCls;

})();


