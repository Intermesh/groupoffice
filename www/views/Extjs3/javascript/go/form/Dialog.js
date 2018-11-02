/* global go */

/**
 * 
 * Typical usage
 * 
 * var dlg = new Dlg();
 * dlg.load(1).show();
 */

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
	
	/**
	 * Redirect to the entity detail view after save.
	 */
	redirectOnSave: true,
	
	initComponent: function () {

		this.formPanel = new go.form.EntityPanel({
			entityStore: this.entityStore,
			items: this.initFormItems()
		});		
		
		this.formPanel.on("save", function(fp, entity) {
			this.fireEvent("save", this, entity);
		}, this);
		
		this.items = [this.formPanel];

		this.buttons = [
			'->', 
			{
				text: t("Save"),
				handler: this.submit,
				scope: this
			}];

		go.form.Dialog.superclass.initComponent.call(this);		
		
		if(this.entityStore.entity.linkable) {
			this.addCreateLinkButton();
		}		

		if (this.formValues) {
			this.formPanel.form.setValues(this.formValues);
			delete this.formValues;
		}
	},
	
	
	addCreateLinkButton : function() {
		
		this.getFooterToolbar().insert(0, this.createLinkButton = new go.modules.core.links.CreateLinkButton());	
		
		this.on("load", function() {
			this.createLinkButton.setEntity(this.entityStore.entity.name, this.currentId);
		}, this);

		this.on("show", function() {
			if(!this.currentId) {
				this.createLinkButton.reset();
			}
		}, this);

		this.on("submit", function(dlg, success, serverId) {			
			this.createLinkButton.setEntity(this.entityStore.entity.name, serverId);
			this.createLinkButton.save();
		}, this);
	
	},

	load: function (id) {
		
		var me = this;
		
		function innerLoad(){
			me.currentId = id;

			if(!me.formPanel.load(id)) {			
				//If no entity was returned the entity store will load it and fire the "changes" event. This dialog listens to that event.
				me.actionStart();
			} else {
				//needs to fire because overrides are made to handle logic after form load.
				me.onLoad();
			}
		}
		
		// The form needs to be rendered before the data can be set
		if(!this.rendered){
			this.on('afterrender',innerLoad,this,{single:true});
		} else {
			innerLoad.call(this);
		}
		
		return this;
	},
	
	onChanges : function(entityStore, added, changed, destroyed) {
		
		var entity = added[this.currentId] || changed[this.currentId] || false;
		
		if(entity) {
			this.actionComplete();
			this.onLoad();
		}		
	},

	delete: function () {
		
		Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function (btn) {
			if (btn !== "yes") {
				return;
			}
			
			this.entityStore.set({destroy: [this.currentId]}, function (options, success, response) {
				if (response.destroyed) {
					this.hide();
				}
			}, this);
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
		this.fireEvent("load", this);
//		this.deleteBtn.setDisabled(this.formPanel.entity.permissionLevel < GO.permissionLevels.writeAndDelete);
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
			this.fireEvent("submit", this, success, serverId);
			
			if(!success) {
				return;
			}
			if(this.redirectOnSave) {
				this.entityStore.entity.goto(serverId);
			}
			this.close();
						
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
