(function () {
	var types = {};
	var CustomFieldsCls = Ext.extend(Ext.util.Observable, {
		initialized: false,
		
		init : function() {
			go.Stores.get("Field").getUpdates(function (store) {
				go.CustomFields.fieldsLoaded = true;
				go.CustomFields.fireReady();
	//		console.log(go.Stores.get("Field"));
			});

			go.Stores.get("FieldSet").getUpdates(function (store) {
	//		console.log(go.Stores.get("FieldSet"));
				go.CustomFields.fieldSetsLoaded = true;
				go.CustomFields.fireReady();
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
		 * @param {go.core.DetailView} detailView
		 * @returns {void}
		 */
		addDetailPanels: function (detailView) {			

			go.CustomFields.onReady(function () {
				
				var fieldSets = go.CustomFields.getFieldSets(Ext.isString(detailView.entity) ?  detailView.entity : detailView.entityStore.entity.name);

				fieldSets.forEach(function (fieldSet) {
					var tpl = '<tpl for="customFields"><div class="icons">';

					go.CustomFields.getFields(fieldSet.id).forEach(function (field) {
						
						
						
						tpl += '<tpl if="!GO.util.empty(go.CustomFields.renderField(\'' + field.id + '\',values))"><p><i class="icon label ' + go.CustomFields.getFieldIcon(field.id) + '"></i>\
					<span>{[go.CustomFields.renderField("' + field.id + '",values)]}</span>\
						<label>' + t(field.name) + '</label>\
						</p><hr /></tpl>';
					});

					tpl += '</div></tpl>';

					detailView.add({						
						id: "cf-detail-field-set-" + fieldSet.id,
						fieldSetId: fieldSet.id,
						title: fieldSet.name,
						tpl: tpl,
						collapsible: true,
						onLoad: function(dv) {
							
							var vis = false;							
							go.CustomFields.getFields(fieldSet.id).forEach(function (field) {
								if(!GO.util.empty(dv.data.customFields[field.databaseName])) {
									vis = true;
								}
							});
							
							this.setVisible(vis);
						}
					});

					if (detailView.rendered) {
						detailView.doLayout();
					}
				});

			});
		},

		fieldSetsLoaded: false,
		fieldsLoaded: false,
		fireReady: function () { //internal
			if (this.fieldSetsLoaded && this.fieldsLoaded) {
				this.fireEvent('internalready', this);
			}
		},
		/**
		 * Use this to do stuff after the custom fields data has been loaded
		 * 
		 * @param {type} fn
		 * @param {type} scope
		 * @returns {undefined}
		 */
		onReady: function (fn, scope) {
			if(!this.initialized) {
				this.initialized = true;
				this.init();
			}
			if (!this.fieldSetsLoaded || !this.fieldsLoaded) {
				this.on('internalready', fn, scope || this);
			} else {
				fn.call(scope || this, this);
			}
		}
	});

	go.CustomFields = new CustomFieldsCls;

})();


