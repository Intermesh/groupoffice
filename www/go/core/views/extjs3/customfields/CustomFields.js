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
		 * Get all Ext.grid.Column definitions for an entity's custom fields
		 * @param {string} entity eg. "Contact"
		 * @returns {Array}
		 */
		getFilterCmps : function(entity) {
			var filters = [], me = this, type;



			this.getFieldSets(entity).forEach(function(fs) {
				me.getFields(fs.id).forEach(function(field) {
					type = me.getType(field.type);
					if(!type) {
						console.error("Custom field type " + field.type + " not found");
						return;
					}

					if(!field.filterable) {
						return;
					}

					var filter = type.getFilterCmpWrap(field);

					if(filter) {
						filters.push(filter);
					}
				});
			});
			return filters;
		},

		getFilterPanel : function(entity, store) {
			// var items = this.getFilterCmps(entity);

			function load() {
				var filter = {
					operator: 'AND',
					conditions: []
				};
				pnl.findByType('chipsview').forEach(function(cv) {
					if(cv.store.getCount() == 0) {
						return;
					}

					var conditions = [];

					cv.store.getRange().forEach(function(r) {
						var c = {};
						c[cv.filter.name] = r.data.value;
						conditions.push(c)
					});

					filter.conditions.push({
						operator: 'OR',
						conditions: conditions
					})

				});

				store.setFilter('customfilters', filter);
				store.load();
			}

			var entityStore = go.Db.store("EntityFilter"), me = this;

			entityStore.query({
				filter: {
					entity: entity,
					type: "variable"
				}
			}).then(function(response) {
				return entityStore.get(response.ids);
			}).then(function(result) {
				result.entities.forEach(function(f) {
					var filterConfig = go.Entities.get(entity).filters[f.name];
					if(!filterConfig) {
						console.warn('No such filter: ' + f.name);
						return;
					}
					var cmp = Ext.create(me.getFilterCmp(filterConfig));

					var chipView = new go.form.ChipsView();
					chipView.filter = f;
					chipView.store.on('add', load);
					chipView.store.on('remove', load)
					var event = cmp.events.select ? 'select' : 'change';
					cmp.on(event, function(cmp) {
						var v = cmp.getValue();

						if(v instanceof Date) {
							v = v.serialize();
						}
						chipView.store.loadData({records: [{
							value: v,
							display: cmp.getRawValue()
						}]}, true);

						cmp.reset();


					});

					cmp.on('specialkey' , function(field, e) {
						// e.HOME, e.END, e.PAGE_UP, e.PAGE_DOWN,
						// e.TAB, e.ESC, arrow keys: e.LEFT, e.RIGHT, e.UP, e.DOWN
						if (e.getKey() == e.ENTER) {
							chipView.store.loadData({records: [{
									value: cmp.getValue(),
									display: cmp.getRawValue()
								}]}, true);

							cmp.reset();
						}
					})

					cmp.fieldLabel = filterConfig.title;
					pnl.items.itemAt(0).add(cmp);
					pnl.items.itemAt(0).add(chipView);

				});

				pnl.doLayout();
			});

			var pnl = new Ext.form.FormPanel({
				labelAlign: "top",
				items: [{
					xtype: "fieldset",
					items: []
				}]
			});



			return pnl;
		},



		getFilterCmp : function(filter) {

			var cls;

			switch(filter.type) {
				case 'string':
					cls = Ext.form.TextField;
					break;
				case 'date':
					cls = go.filter.variabletypes.Date;
					break;
				default:
				cls = eval(filter.type);
			}

			if(!filter.typeConfig) {
				filter.typeConfig = {};
			}

			Ext.apply(filter.typeConfig, {
				anchor: '100%',
				filter: filter,
				name: filter.name,
				collapseOnSelect: false,
				hiddenName: filter.name,
				customfield: filter.customfield //Might be null if this is a standard filter.
			});

			return new cls(filter.typeConfig);
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
				if(formField) {
					formField.field = field;
					r.push(formField);
				}
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
		 * @returns {String}
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
				console.error("Custom field type '" + field.type + "' not found");
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
				panels.push(new go.customfields.DetailPanel({
					fieldSet: fieldSet
				}));
			});
			return panels;
		}
	});

	go.customfields.CustomFields = new CustomFieldsCls;

})();
