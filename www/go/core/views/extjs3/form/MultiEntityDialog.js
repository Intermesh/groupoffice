/* global Ext, go, GO */

go.form.MultiEntityDialog = Ext.extend(go.Window, {

	title: "",
	entityStore: null,
	itemCfg: null, // {xtype:'entityform',...} to be repeated in FormGroup
	btnCfg: {
		text: t("Add another"),
		iconCls: 'ic-add'
	},
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
	
	load: function(ids) {
		this.entityStore.get(ids, function(entries) {
			entries.forEach(function(entry) {
				var wrap = this.formGroup.addPanel(),
					ff = wrap.formField, 
					entityPanel = ff.items.get(0);

				this.formGroup.doLayout();
				this.formGroup.markDeleted = [];
				entityPanel.currentId = ff.key = entry.id;
				entityPanel.setValues(entry);
				entityPanel.entity = entry;
				this.loadEntity(entityPanel, entry, wrap);
			}, this)
		}, this);
	},

	loadEntity: function(entityPanel, entity, wrap) {
		// overwrite todo something when entity is loaded
		// method is called once for each entity
	}
	
});
