/* global go, Ext */

/**
 * 
 * Typical usage
 * 
 * var dlg = new Dlg();
 * dlg.load(1).show();
 */

go.form.Dialog = Ext.extend(go.Window, {
	autoScroll: false,
	width: dp(500),
	modal: true,
	maximizable: !GO.util.isMobileOrTablet(),
	entityStore: null,

	/**
	 * The current ID of the loaded entity
	 * @var int
	 */
	currentId: null,

	buttonAlign: 'left',
	layout: "fit",
	showCustomfields:true,
	showLinks: true,

	/**
	 * When the entity is modified by another user / process ask to load these changes
	 */
	loadExternalChanges: true,
	deferredRender: true, // for contained tab panel. set to false to eager render

	/**
	 * If set then the title bar will be appended with ": "+ value of the field.
	 */
	titleField: "name",


	/**
	 * Layout of the automatically created form panel
	 */
	formPanelLayout: "form",
	
	/**
	 * Redirect to the entity detail view after save.
	 */
	redirectOnSave: true,

	/**
	 * Close dialog on submit
	 */
	closeOnSubmit: true,

	panels : null,

	/**
	 * When overriding then add items to "mainPanel" instead of "formPanel" for consistency with tabbed dialogs and non
	 * tabbed dialogs.
	 */
	initComponent: function () {

		this.panels = [];

		this.formPanel = this.createFormPanel();
		
		//In case this.createFormPanel() is overridden it can provide the entityStore too.
		this.entityStore = this.formPanel.entityStore;
		
		
		//Add a hidden submit button so the form will submit on enter
		this.formPanel.add(new Ext.Button({
			hidden: true,
			hideMode: "offsets",
			type: "submit",
			handler: function() {
				this.submit().catch((error) => {
					GO.errorDialog.show(error);
				});
			},
			scope: this
		}));
		
		
		this.formPanel.on("save", function(fp, entity) {
			this.currentId = entity.id;
			this.fireEvent("save", this, entity);
		}, this);
		
		this.items = [this.formPanel];

		this.initButtons();

		go.form.Dialog.superclass.initComponent.call(this);		
		
		if(this.showLinks && this.entityStore.entity.links && this.entityStore.entity.links.length) {
			this.addCreateLinkButton();
		}

		//deprecated
		if (this.formValues) {
			this.formPanel.setValues(this.formValues);
			delete this.formValues;
		}
		
		this.addEvents({load: true, submit: true});

		this.initTitleField();

		this.on("beforeclose", this.onBeforeClose, this);

	},

	closeWithModifications: false,

	onBeforeClose : function() {
		if(!this.closeWithModifications && this.formPanel.isDirty()) {
			return confirm(t("Are you sure you want to close this window and discard your changes?"));
		}
	},

	initButtons: function() {
		Ext.applyIf(this,{
			buttons:[
				'->', 
				this.saveButton = new Ext.Button({
					cls: "primary",
					text: t("Save"),
					handler: function() {
						this.submit().catch(function(error) {
							GO.errorDialog.show(error );
						});
					},
					scope: this
				})
			]
		});
	},

	initTitleField : function() {
		if(this.titleField) {
			this.titleField = this.formPanel.getForm().findField(this.titleField);
			if(this.titleField) {
				this.titleField.on("change", this.updateTitle, this);
				this.formPanel.on("load", this.updateTitle, this);
			}			
		}
	},

	updateTitle : function() {
		if(!this.origTitle) {
			this.origTitle = this.title;
		}
		var title = this.origTitle, v = this.titleField.getValue();

		if(v) {
			title += ": " + Ext.util.Format.htmlEncode(v);
		}

		this.setTitle(title);
	},
	
	createFormPanel : function() {

		let items = this.initFormItems() || [];

		if(this.showCustomfields){
			items = this.addCustomFields(items);
		}

		let count = this.panels.length;

		//if items is defined then a panel will be inserted in createTabPanel()
		if(items.length) {
			count++;
		}
		
		if(count > 1) {
			items = [this.createTabPanel(items)];
		} else{
			items = [this.mainPanel = new Ext.Panel({layout: this.formPanelLayout, autoScroll: true, items: items})];
		}

		return new go.form.EntityPanel({
			loadExternalChanges: this.loadExternalChanges,
			entityStore: this.entityStore,
			items: items,
			layout: 'fit',
			autoScroll: false
		});
	},


	getCustomFieldSets : function() {
		const items = [];
		const fieldsets = go.customfields.CustomFields.getFormFieldSets(this.entityStore);
		fieldsets.forEach(function(fs) {
			if(fs.fieldSet.permissionLevel <= 10) {
				return;
			}
			if(fs.fieldSet.isTab) {
				fs.title = null;
				fs.collapsible = false;
				fs.collapsed = false;
				const pnl = new Ext.Panel({
					autoScroll: true,
					hideMode: 'offsets', //Other wise some form elements like date pickers render incorrectly.
					title: fs.fieldSet.name,
					items: [fs]
				});
				this.addPanel(pnl);
			} else {
				//in case formPanelLayout is set to column
				fs.columnWidth = 1;
				items.push(fs);
			}
		}, this);

		return items;
	},
	
	addCustomFields : function(items) {
		return items.concat(this.getCustomFieldSets());
	},

	movePermissionsPanelToEnd : function() {
		const sharePanelIndex = this.panels.findIndex((el) => {
			return el instanceof go.permissions.SharePanel;
		});

		if(sharePanelIndex === -1) {
			return;
		}

		//move to end
		this.panels.push(this.panels.splice(sharePanelIndex, 1)[0]);

	},

	createTabPanel : function(items) {

		this.movePermissionsPanelToEnd();

		if(items.length) {
			this.panels.unshift(this.mainPanel = new Ext.Panel({
				title: t("General"),
				layout: this.formPanelLayout,
				autoScroll: true,
				items: items
			}));
		}
		
		this.tabPanel = new Ext.TabPanel({
			defaults: {
				autoScroll: true
				// hideMode: "offsets"
			},
			activeTab: 0,
			enableTabScroll:true,
			deferredRender: this.deferredRender,//required for custom fields tabs filtering
			items: this.panels
		});
		
		
		return this.tabPanel;
	},
	
	addPanel: function(panel) {
		this.panels.push(panel);
	},	

	addCreateLinkButton : function() {
		
		this.getFooterToolbar().insert(0, this.createLinkButton = new go.links.CreateLinkButton());	
		
		this.on("load", function() {
			this.createLinkButton.setEntity(this.entityStore.entity.name, this.currentId);
		}, this);

		this.on("close", function() {
			this.createLinkButton.reset();
		}, this);

		this.on("submit", function(dlg, success, serverId) {			
			this.createLinkButton.setEntity(this.entityStore.entity.name, serverId);
			this.createLinkButton.save();
		}, this);
	
	},
	
	setValues : function(v, trackReset) {
		this.formPanel.setValues(v, trackReset);
		
		return this;
	},
	
	getValues : function(dirtyOnly) {
		return this.formPanel.getValues(dirtyOnly);
	},

	load: function (id) {
		var me = this;

		me.loading = true;
		me.currentId = id;

		function innerLoad(){
			me.actionStart();
			me.formPanel.load(id, function(entityValues) {
				me.onLoad(entityValues);
				me.actionComplete();

				me.loading = false;

				me.onReady();
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

	/**
	 * Called on show() if not loading an entity or on load if loading an entity.
	 */
	onReady: function() {

		this.fireEvent("ready", this);
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
//		this.deleteBtn.setDisabled(this.formPanel.entity.permissionLevel < go.permissionLevels.writeAndDelete);
	},

	onSubmit: function (success, serverId) {

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


	/**
	 * Override to do stuff before submitting to server
	 *
	 * for example to send an additional value:
	 *
	 * this.formPanel.values.foo = 'bar';
	 *
	 * @returns {boolean}
	 */
	onBeforeSubmit: function() {
		return true;
	},

	submit: function () {
		//When form is submitted with enter key the validation errors of the field having focus is not disabled if we
		// don't give something else focus.
		if(this.saveButton) {
			this.saveButton.focus();
		}
		
		if(!this.onBeforeSubmit()) {
			console.warn("onBeforeSubmit returned false");
			return Promise.reject({message: t("You have errors in your form. The invalid fields are marked.")});
		}

		if (!this.isValid()) {
			this.showFirstInvalidField();
			return Promise.reject({message: t("You have errors in your form. The invalid fields are marked.")});
		}

		var isNew = !this.currentId;
		
		this.actionStart();

		var me = this;
		return this.formPanel.submit().then(function(serverId) {

			me.currentId = serverId;

			me.onSubmit(true, serverId);
			me.fireEvent("submit", this, true, serverId);

			if(me.redirectOnSave && isNew) {
				me.entityStore.entity.goto(serverId);
			}

			if(me.closeOnSubmit) {
				me.closeWithModifications = true;
				me.close();
			} else {
				me.formPanel.form.trackReset();
			}

			return serverId;

		}).catch(function(error) {
			const firstError = me.showFirstInvalidField();
			return Promise.reject(firstError ? {message: t("You have errors in your form. The invalid fields are marked.")} : error);
		}).finally(function() {
			me.actionComplete();
		})
	},

	showFirstInvalidField : function() {

		var firstFieldWithError = this.formPanel.form.items.find(function(item) {
			//activeError is set when markInvalid() is used. We use it when marking server errors. isValid() does
			// client side validation.
			return item.activeError || (item.isValid && !item.isValid(true));
		});

		console.log("Field with error", firstFieldWithError);

		if(!firstFieldWithError) {
			console.warn('A validation error occurred but no visible field with was error found.');
			// this.formPanel.form.items.each(function(f){
			// 	if(!f.validate()){
			// 		console.warn(f);
			// 	}
			// });
			return false;
		}


		//Check for tab panel to show tab with error.
		var panel = null;
		var tabPanel = firstFieldWithError.findParentBy(function(c){
				if(c.isXType("tabpanel")) {
					return true;
				}
				panel = c;
		});

		if(tabPanel) {			
			panel.show();

			// Not elegant but if a user marked a field as required and it's not visible it will magically appear this way
			tabPanel.unhideTabStripItem(panel);
		}

		var fieldSet = firstFieldWithError.findParentBy(function(c){
			if(c.isXType("fieldset")) {
				return true;
			}
		});
		if(fieldSet) {
			fieldSet.show();
			fieldSet.setDisabled(false);
		}

		// Focus make server side errors dissappear 
		// firstFieldWithError.focus();

		return firstFieldWithError.activeError;
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
	},

	show : function() {
		go.form.Dialog.superclass.show.call(this);

		var me = this;
		setTimeout(function() {
			if(me.loading || me.currentId) {
				//onReady is called after load.
				return;
			}


			// In overrides.js the form panel focuses on the first empty field or first field with an error
			me.formPanel.focus();

			me.onReady();
		});
	}
});

Ext.reg("formdialog", go.form.Dialog);
