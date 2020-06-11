/* global Ext, go, GO */

go.form.MultiEntityDialog = Ext.extend(go.Window, {

	title: "",
	entityStore: null,
	/**
	 * string[] relation names defined in entity store
	 * When specified the Detailview will listen to these store and fetch the related entities
	 */
	relations: [],
	itemCfg: null, // {xtype:'entityform',...} to be repeated in FormGroup
	btnCfg: {
		text: t("Add another"),
		iconCls: 'ic-add'
	},
	autoScroll: true,
	editable: true,
	constantValues: {}, //values to be set on every entity before save
	
	initComponent: function() {
		
		this.buttons=['->', {
			text: t("Save"),
			handler: function() {this.submit();},
			scope: this
		}];
		
		this.items = [new Ext.Container({
			layout: "column",
			defaults: {
				columnWidth: 1
			},
			items: [this.formGroup = new go.form.FormGroup({
				name: "entities",
				mapKey: 'id',//for markDeleted
				btnCfg: this.btnCfg,
				editable: this.editable,
				itemCfg: {
					items:[this.itemCfg]
				}
			})]
		})];

		go.form.MultiEntityDialog.superclass.initComponent.call(this);
	},
	
	submit: function() {
		var params = {
			update:{},
			create:{}
		};

		var valid = true;
		this.formGroup.each(function(item) {
			var form = item.items.get(0);
			valid = valid && form.isValid();
			var values = form.getValues(!!form.currentId);
			if(Object.keys(values).length === 0) {
				return;
			}
			for(var key in this.constantValues) {
				values[key] = this.constantValues[key];
			}
			if (form.currentId) {
				params.update[form.currentId] = values;
			} else {
				params.create[Ext.id()] = values;
			}
		}, this);

		params.destroy = [];
		for(var i = 0; i < this.formGroup.markDeleted.length; i++) {
			params.destroy.push(this.formGroup.markDeleted[i]);
		}

		if(!valid) {
			return;
		}

		this.entityStore.set(params, function (options, success, response) { 
			if(success === true) {
				this.close();
			}
		}, this);
	},

	internalLoad : function(entityPanel, entity, wrap) {
		this.watchRelations = {};
		var me = this;
		
		if(!this.relations.length) {
			this.onLoadEntity(entityPanel, entity, wrap);
			return;
		}

		go.Relations.get(this.entityStore, entity, this.relations).then(function(result) {
			me.watchRelations = result.watch;
			me.onLoadEntity(entityPanel, entity, wrap);
		}).catch(function(result) {
			console.warn("Failed to fetch relation", result);
		});
		
	},
	
	load: function(ids) {
		this.entityStore.get(ids, function(entries) {
			entries.forEach(function(entity) {
				var wrap = this.formGroup.addPanel(),
					ff = wrap.formField, 
					entityPanel = ff.items.get(0);

				

				this.formGroup.doLayout();
				this.formGroup.markDeleted = [];
				entityPanel.currentId = ff.key = entity.id;
				entityPanel.setValues(entity, true);
				entityPanel.entity = entity;
				
				this.internalLoad(entityPanel, entity, wrap);
			}, this)
			this.doLayout();
		}, this);
	},

	onLoadEntity: function(entityPanel, entity, wrap) {
		// overwrite todo something when entity is loaded
		// method is called once for each entity
	}
	
});
