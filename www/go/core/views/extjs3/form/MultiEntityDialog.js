/* global Ext, go, GO */

go.form.MultiEntityDialog = Ext.extend(go.Window, {

	title: "",
	entityStore: null,
	itemCfg: null, // {xtype:'entityform',...} to be repeated in FormGroup
	btnCfg: {
		text: t("Add another"),
		iconCls: 'ic-add'
	},
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
				pad: true,
				btnCfg: this.btnCfg,
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
				values[key] = constantValues[key];
			}
			if (form.currentId) {
				params.update[form.currentId] = values;
			} else {
				params.create[Ext.id()] = values;
			}
		}, this);

		params.destory = [];
		for(var id in this.formGroup.markDeleted) {
			params.destory.push(id);
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
				var entityPanel = this.formGroup.addPanel().formField.items.get(0);
				this.formGroup.doLayout();
				entityPanel.currentId = entry.id;
				entityPanel.setValues(entry);
				entityPanel.entity = entry;
				this.loadEntity(entityPanel, entry);
			}, this)
		}, this);
	},

	loadEntity: function(entityPanel, entity) {
		// overwrite todo something when entity is loaded
		// method is called once for each entity
	}
	
});
