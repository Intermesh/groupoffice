go.form.Dialog = Ext.extend(go.Window, {
	autoScroll: true,
	width: 500,
	modal: true,
	showAnimDuration: 0.16,
	hideAnimDuration: 0.16,
//	closeAction: 'hide',
	entityStore: null,
	currentId: null,
	buttonAlign: 'left',
	layout: "fit",
	initComponent: function () {

		this.formPanel = new go.form.EntityPanel({
			entityStore: this.entityStore,
			items: this.initFormItems()
		});		
		
		this.items = [this.formPanel];

		this.buttons = [this.deleteBtn = new Ext.Button({
				text: t("Delete"),
				cls: 'danger',
				handler: this.delete,
				disabled: true,
				scope: this
			}), '->', {
				text: t("Save"),
				handler: this.submit,
				scope: this
			}];

		go.form.Dialog.superclass.initComponent.call(this);
		
		this.entityStore.on('changes',this.onChanges, this);

		this.on('destroy', function() {
			this.entityStore.un('changes', this.onChanges, this);
		}, this);

		if (this.formValues) {
			this.formPanel.form.setValues(this.formValues);
			delete this.formValues;
		}
	},

	load: function (id) {
		this.currentId = id;

		if(!this.formPanel.load(id)) {			
			//If no entity was returned the entity store will load it and fire the "changes" event. This dialog listens to that event.
			this.actionStart();
		} else
		{
			//needs to fire because overrides are made to handle logic after form load.
			this.onLoad();
		}
		
		return this;
	},
	
	onChanges : function(entityStore, added, changed, destroyed) {
		
		if(changed.concat(added).indexOf(this.currentId) !== -1) {
			this.actionComplete();
		}		
	},

	delete: function () {
		this.entityStore.set({destroy: [this.currentId]}, function (options, success, response) {
			if (response.destroyed) {
				this.hide();
			}
		}, this);
	},

	actionStart: function () {
		if (this.getBottomToolbar()) {
			this.getBottomToolbar().setDisabled(true);
		}
		if (this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(true);
		}

		if (this.getFooterToolbar()) {
			this.getFooterToolbar().setDisabled(true);
		}
	},
	
	onLoad : function() {
		
	},
	
	onSubmit : function() {
		
	},

	actionComplete: function () {
		if (this.getBottomToolbar()) {
			this.getBottomToolbar().setDisabled(false);
		}

		if (this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(false);
		}
		if (this.getFooterToolbar()) {
			this.getFooterToolbar().setDisabled(false);	
		}
		
		this.onLoad();
	},
	
	isValid : function() {
		return this.formPanel.isValid();
	},
	
	focus : function() {
		this.formPanel.focus();
	},
	
	submit : function() {
		
		if (!this.isValid()) {
			return;
		}
		
		this.actionStart();
		
		this.formPanel.submit(function(formPanel, success, serverId) {
			this.actionComplete();
			this.onSubmit();
			if(success) {
				this.entityStore.entity.goto(serverId);
				this.close();
			}			
		}, this);
	},

	initFormItems: function () {
		return [
//			{
//				xtype: 'textfield',
//				name: 'name',
//				fieldLabel: "Name",
//				anchor: '100%',
//				required: true
//			}
		];
	}
});
