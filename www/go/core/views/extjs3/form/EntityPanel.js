/* global Ext, go */

go.form.EntityPanel = Ext.extend(Ext.form.FormPanel, {
	currentId : null, 
	entityStore: null,
	buttonAlign: 'left',
	autoScroll: true,
	entity: null,
	values : null,
	
	initComponent : function() {
		go.form.EntityPanel.superclass.initComponent.call(this);			
		
		this.values = {};
		
		this.getForm().trackResetOnLoad = true;
		
		this.addEvents({load: true, setvalues: true, beforesetvalues: true});
	},	
	
	onChanges : function(entityStore, added, changed, destroyed) {
		//don't update on our own submit
		if(this.submitting) {
			return;
		}
		var entity = added[this.currentId] || changed[this.currentId] || false;
		if(entity) {			
			this.entity = entity;
			//TODO, This will bluntly overwrite user's modification when modified.
			this.getForm().setValues(entity);
		}		
	},
	
	isValid : function() {
		return this.getForm().isValid();
	},
	
	load: function (id, callback, scope) {
		this.currentId = id;

		var me = this;

		this.entityStore.single(id).then(function(entity) {
			me.setValues(entity, true);
			me.entity = entity;
			
			if(callback) {
				callback.call(scope || me, entity);
			}
			
			me.fireEvent("load", me, entity);
		});
	},
	
	getValues : function (dirtyOnly) {	
		var v = {};
		for(var name in this.values) {
			if(!dirtyOnly || this.entity == null || !go.util.isEqual(this.entity[name], this.values[name])) {
				v[name] = this.values[name];
			}
		}
		
		Ext.apply(v, this.getForm().getFieldValues(dirtyOnly));
		return v;
	},
	
	setValues : function(v, trackReset) {
		var field, name;

		this.fireEvent("beforesetvalues", this, v);
		
		//set all non form values.
		for(name in v) {		
			field = this.getForm().findField(name);
			if(!field) {
				//Use clone otherwise dirty check will never work because of the reference
				this.values[name] = go.util.clone(v[name]);
			}
		}

		//Set the form values after. It's important to do this after setting this.values otherwise it will add joined object value names like customFields.name
		var oldReset = this.getForm().trackResetOnLoad;
		this.getForm().trackResetOnLoad = trackReset;
		this.getForm().setValues(v);
		this.getForm().trackResetOnLoad = oldReset;
		
		this.fireEvent('setvalues', this, v);
		return this;
	},

	reset: function() {
		this.currentId = null
		this.entity = null;
		this.getForm().reset();
	},

	submit: function (cb, scope) {

		if (!this.isValid()) {
			return;
		}		
		//get only modified values on existing items, otherwise get all values.
		var id, params = {}, values = this.getValues(!!this.currentId), me = this;
		
		if (this.currentId) {

			id = this.currentId;

			params.update = {};
			params.update[this.currentId] = values;
		} else {

			id = Ext.id();
			params.create = {};
			params.create[id] = values;
		}

		this.submitting = true;

		this.fireEvent('beforesubmit', this, values);
		
		return me.entityStore.set(params).then(function(response) {

			var saved = (params.create ? response.created : response.updated) || {};
			if (id in saved) {
				me.fireEvent("save", me, values, serverId);

				var serverId = params.create ? response.created[id].id : id;

				if(cb) {
					cb.call(scope, me, true, serverId);
				}

				me.fireEvent("submit", me, true, serverId);

				return me.entityStore.single(serverId).then(function(entity) {
					me.entity = entity;
					me.currentId = serverId;
					return serverId;
				});
			} else
			{
				//something went wrong
				var notSaved = (params.create ? response.notCreated : response.notUpdated) || {};
				if (!notSaved[id]) {
					notSaved[id] = {type: "unknown"};
				}

				switch (notSaved[id].type) {
					case "forbidden":
						response.message = t("Sorry, you don't have permissions to update this item");
						break;

					default:

						var firstErrorMsg = me.markServerValidationErrors(notSaved[id].validationErrors);

						if(!response.message) {
							response.message = firstErrorMsg;
						}
						break;
				}
				if(cb) {
					cb.call(scope, me, false, null);
				}
				me.fireEvent("submit", me, false, null, notSaved[id]);

				//unhandled rejection will finally be handled in www/go/core/views/extjs3/Module.js it will show the error dialog.
				//to prevent this from happening use an override in the dialog submit:
				//  submit: function() {
				// 		return this.supr().submit.call(this).catch(function(error) {
				// 			GO.errorDialog.show("Oopsie");
				// 		})
				// 	}
				return Promise.reject(response);
			}
		}, me).catch(function(error){
			if(cb) {
				cb.call(scope, me, false, null);
			}
			me.fireEvent("submit", me, false, null, error);

			return Promise.reject(error);
		}).finally(function() {
			me.submitting = false;
		})

	},

	markServerValidationErrors : function(e, fieldPrefix) {
		var firstError;
		if(!fieldPrefix) {
			fieldPrefix = "";
		}
		//mark validation errors
		for(var name in e) {
			var field = this.getForm().findField(fieldPrefix + name);
			if(field) {
				field.markInvalid(e[name].description);
			} else
			{
				console.warn("Could not find form field for server error " + name, e[name]);
			}
			if(!firstError && e[name].code != 4) { // code 4 means error in related record. It will be found deeper in the recursion.
				firstError = e[name].description;
			}

			if(e[name].validationErrors) {
				var subFirst = this.markServerValidationErrors(e[name].validationErrors, name + ".");
				if(!firstError) {
					firstError = subFirst;
				}
			}
		}

		return firstError;
	}
});

Ext.reg("entityform", go.form.EntityPanel);