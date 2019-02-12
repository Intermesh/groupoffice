/* global go, Ext */

/**
 * 
 * Typical usage
 * 
 * var dlg = new Dlg();
 * dlg.load(1).show();
 */

go.form.Dialog = Ext.extend(go.Window, {
	autoScroll: true,
	width: dp(500),
	modal: true,
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
		
		
		//Add a hidden submit button so the form will submit on enter
		this.formPanel.add(new Ext.Button({
					hidden: true,
					hideMode: "offsets",
					type: "submit",
					handler: function() {
						this.submit();
					},
					scope: this
				}));
				
		
		this.formPanel.on("save", function(fp, entity) {
			this.fireEvent("save", this, entity);
		}, this);
		
		this.items = [this.formPanel];

		Ext.applyIf(this,{
			buttons:[
				'->', 
				{
					text: t("Save"),
					handler: this.submit,
					scope: this
				}
			]
		});

		go.form.Dialog.superclass.initComponent.call(this);		
		
		if(this.entityStore.entity.linkable) {
			this.addCreateLinkButton();
		}

		//deprecated
		if (this.formValues) {
			this.formPanel.setValues(this.formValues);
			delete this.formValues;
		}
		
		this.addEvents({load: true, submit: true});
	},
	

	addCreateLinkButton : function() {
		
		this.getFooterToolbar().insert(0, this.createLinkButton = new go.links.CreateLinkButton());	
		
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
	
	setValues : function(v) {
		this.formPanel.setValues(v);
		
		return this;
	},
	
	getValues : function() {
		return this.formPanel.getValues();
	},

	load: function (id) {
		
		var me = this;
		
		function innerLoad(){
			me.currentId = id;
			me.actionStart();
			me.formPanel.load(id, function(entityValues) {
				me.onLoad(entityValues);
				me.actionComplete();
			}, this);
		}
		
		// The form needs to be rendered before the data can be set
		if(!this.rendered){
			this.on('afterrender',innerLoad,this,{single:true});
		} else {
			innerLoad.call(this);
		}

		return this;
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
	
	onLoad : function(entityValues) {
		this.fireEvent("load", this, entityValues);
//		this.deleteBtn.setDisabled(this.formPanel.entity.permissionLevel < GO.permissionLevels.writeAndDelete);
	},

	onSubmit: function (success, serverId) {
		if (success) {
			this.entityStore.entity.goto(serverId);
		}
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

	isValid: function () {
		return this.formPanel.isValid();
	},

	focus: function () {		
		this.formPanel.focus();
	},
	
	onBeforeSubmit: function() {
		return true;
	},

	submit: function () {
		
		if(!this.onBeforeSubmit()) {
			return;
		}

		if (!this.isValid()) {
			return;
		}
		
		this.actionStart();

		this.formPanel.submit(function (formPanel, success, serverId) {
			this.actionComplete();
			this.onSubmit(success, serverId);
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

Ext.reg("formdialog", go.form.Dialog);
