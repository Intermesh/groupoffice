(function () {
	var types = {};
	var CustomFieldsCls = Ext.extend(Ext.util.Observable, {
		initialized: false,
		
		fieldSets: null,
		fields: null,
		
		//init is called in GO.MainLayout.onAuthentication so custom fields are
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
						success ? resolve (me) : reject(me);
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
		 * Get field set entities
		 * @param {string} entity eg. "note"
		 * @returns {Array}
		 */
		getFieldSets: function (entity) {
			return Object.values(this.fieldSets).filter(function(f) {
				return f.entity === entity;
			});
		},

		/**
		 * Get filter definitions for tbsearch and user filter dialogs
		 * 
		 * @param {string} entity
		 * @returns {Array}
		 */
		getFilters : function(entity) {
			var defs = [];

			this.getFieldsForEntity(entity).forEach((field) => {
				const type = this.getType(field.type);
				var def = type.getFilter(field);
				if(def) {
					defs.push(def);
				}

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
			var relations = {};

			this.getFieldsForEntity(entity).forEach((field) => {
				const type = this.getType(field.type);
				if(!type) {
					console.error("Custom field type " + field.type + " not found");
					return;
				}
				Ext.apply(relations,  type.getRelations(field));

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
			
			var defs = [];

			this.getFieldsForEntity(entity).forEach((field) => {
				const type = this.getType(field.type);
				var def = type.getFieldDefinition(field);
				defs.push(def);
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
			
			this.getFieldsForEntity(entity).forEach((field) => {
				const type = this.getType(field.type);
				var col = type.getColumn(field);

				if(col) {
					cols.push(col);
				}
			});
			return cols;
		},

		_entityFields: {},

		getFieldsForEntity : function(entity) {
			if(this._entityFields[entity]) {
				return this._entityFields[entity];
			}

			this._entityFields[entity] = [];

			this.getFieldSets(entity).forEach((fs) => {
				this.getFields(fs.id).forEach( (field) => {
					const type = this.getType(field.type);
					if (!type) {
						console.error(`Custom field type '${field.type}' for field with name '${field.databaseName}' for entity '${entity}' not found`);
						return;
					}

					this._entityFields[entity].push(field);
				})
			});

			return this._entityFields[entity];
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
				if(!fieldSets[i].parentFieldSetId) {
					let fs = new go.customfields.FormFieldSet({fieldSet: fieldSets[i], isTab: fieldSets[i].isTab});
					formFieldSets.push(fs);
				}

			}
			return formFieldSets;
		},
		
		/**
		 * Show or hide fieldsets based on their "filter" property. 
		 * See FieldSet::getFilter() in the PHP documentation
		 * 
		 */
		filterFieldSets : function(formPanel, values) {
			values = values || (formPanel instanceof go.form.EntityPanel ? formPanel.getValues() : formPanel.getForm().getFieldValues());
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
			return Object.values(this.fields).filter(function(f) {
				return f.fieldSetId == fieldSetId;
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
			const fieldSets = this.getFieldSets(entity);
			let panels = [];

			fieldSets.forEach( (fieldSet) => {
				panels.push(new go.customfields.DetailPanel({
					fieldSet: fieldSet
				}));
			});
			return panels;
		},

		/**
		 * Get fields that have the current entity as its value
		 *
		 * @param {string} entity
		 * @param {int} id
		 * @returns {Promise}
		 */
		getCFRelations: function(entity, id) {
			let rels = [], ids;
			return new Promise((resolve) => {
				go.Db.store("Field").query({
					filter: {type: entity}
				}, function (response) {
					if (!response.ids.length) {
						resolve([]);
					}
					ids = response.ids;
				}, this).then(() => {
					go.Db.store("Field").get(ids, function (fields) {
						let p = [], arFlds = [];
						fields.forEach(function (fld) {
							const fldOptions = fld.options;
							if (Ext.isDefined(fldOptions.showInformationPanel) && fldOptions.showInformationPanel) {
								arFlds.push(fld);
								p.push(go.Db.store("Fieldset").single(fld.fieldSetId));
							}
						});
						Promise.all(p).then((result)=> {
							result.forEach((fldset, index) =>
							{
								const tgtEntity = fldset.entity, fld=arFlds[index];
								let tgtXtype = tgtEntity + "relationgrid";
								if (!Ext.ComponentMgr.isRegistered(tgtXtype)) {
									return; // TODO: implement generic grid?
								}
								rels.push({
									title: fld.options.informationPanelTitle,
									expandByDefault: fld.options.expandByDefault,
									entity: tgtEntity,
									xtype: tgtXtype,
									currentId: id,
									fieldId: fld.id
								});
							});
							resolve(rels);
						});
					});
				});
			});
		},

		/**
		 * Get panels with grids of related items per custom field.
		 *
		 * If an entity type has a custom field that is an entity, list the entities linked to the target entity through
		 * said custom field
		 *
		 * @param {string} entity
		 * @param {int} id
		 * @return {array} panels
		 */
		getRelationPanels: async function(entity, id) {

			let panels = [];
			const configs = await this.getCFRelations(entity, id);
			configs.forEach((config) => {
				panels.push(config);
			});
			return panels;
		}
	});

	go.customfields.CustomFields = new CustomFieldsCls;

})();
