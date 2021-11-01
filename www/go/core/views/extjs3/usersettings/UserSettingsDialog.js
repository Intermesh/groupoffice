/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: UserSettingsDialog.js 19225 2015-06-22 15:07:34Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
go.usersettings.UserSettingsDialog = Ext.extend(go.Window, {
	modal:true,
	resizable: !GO.util.isMobileOrTablet(),
	maximizable: !GO.util.isMobileOrTablet(),
	iconCls: 'ic-settings',
	cls: 'go-user-settings-dlg',
	title: t("My account"),
	width: dp(1000),
	currentUserId:null,
	user: null,

	initComponent: function () {		
		
		this.saveButton = new Ext.Button({
			text: t('Save'),
			handler: this.submit,
			scope:this,
			cls: "primary"
		});
				
		this.formPanel = new Ext.form.FormPanel({
			waitMsgTarget:true,
			region:'center',
			hideMode: "offsets",
			fileUpload: true,
			baseParams : {}			
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
		
		this.formPanel.bodyCfg.autocomplete = "off";
		
		this.tabPanel = new Ext.TabPanel({
			headerCfg: {cls:'x-hide-display'},
			anchor: '100% 100%',
			items: []
		});
		
		this.formPanel.add(this.tabPanel);
		
		
		this.tabStore = new Ext.data.ArrayStore({
			fields: ['name', 'icon'],
			data: []
		});

		this.navMenu = new go.NavMenu({
			region:'west',
			width:dp(300),
			store:this.tabStore,
			listeners: {				
				
				selectionchange: function(view, nodes) {					
					if(nodes.length) {
						this.tabPanel.setActiveTab(nodes[0].viewIndex);
					} else {
						//restore selection if user clicked outside of view
						view.select(this.tabPanel.items.indexOf(this.tabPanel.getActiveTab()));
					}

					this.formPanel.show();
				},
				scope: this
			}
		}); 
		
		Ext.apply(this,{
			width:dp(1100),
			height:dp(800),
			layout:'responsive',
			closeAction:'hide',
			items: [
				this.navMenu,
				this.formPanel
			],
			buttons:[
				this.saveButton
			]
		});
		
		this.addEvents({
			'loadStart' : true,
			'loadComplete' : true,
			'submitStart' : true,
			'submitComplete' : true
		});
		
		
		this.addPanel(go.usersettings.AccountSettingsPanel);
		this.addPanel(go.usersettings.LookAndFeelPanel);

		if(go.User.isAdmin) {
			this.addPanel(go.usersettings.VisibleToPanel);
		}

		// this.loadModulePanels();
		
		var customFieldSets = go.customfields.CustomFields.getFormFieldSets("User").filter(function(fs){return fs.fieldSet.isTab;})
		customFieldSets.forEach(function(fs){
			fs.title = null;
			fs.collapsible = false;
			var pnl = new Ext.Panel({
				autoScroll: true,
				hideMode: 'offsets', //Other wise some form elements like date pickers render incorrectly.
				title: fs.fieldSet.name,
				items: [fs],
				iconCls: 'ic-description'
			});
			this._addPanelCmp(pnl);
		}, this);


		this.tools = [{
			id: "left",
			cls: 'go-show-tablet',
			handler: function () {
				this.navMenu.show();
			},
			scope: this
		}];

		this.navMenu.on("show", function() {
			var tool = this.getTool("left");
			tool.dom.classList.add('go-hide')
		},this);

		this.formPanel.on("show", function() {
			var tool = this.getTool("left");
			tool.dom.classList.remove('go-hide')
		}, this);
		
		go.usersettings.UserSettingsDialog.superclass.initComponent.call(this);
		
		// When the form is loaded, reset the 'modified' state to NOT modified.
		this.formPanel.getForm().trackResetOnLoad  = true;
	},
	
	loadModulePanels : function() {
    
		var available = go.Modules.getAvailable(), pnl,pnlCls, config, i, i1, l, l2;
		for(i = 0, l = available.length; i < l; i++) {
			config = go.Modules.getConfig(available[i].package, available[i].name);
			
			if(!config.userSettingsPanels) {
				continue;			
			}
			
			for(i1 = 0, l2 = config.userSettingsPanels.length; i1 < l2; i1++) {
				pnlCls = eval(config.userSettingsPanels[i1]);
				pnl = new pnlCls({header: false, loaded: false, submitted: false});
				if((pnl.adminOnly && go.User.isAdmin) || (!pnl.adminOnly && go.Modules.isAvailable(available[i].package, available[i].name, go.permissionLevels.read, this.user))) {
					this._addPanelCmp(pnl);
				}
			}
		}

		this.doLayout();
	},
	
	/**
	 * The show function of this dialog.
	 * This immediately starts loading all tabpanels in this dialog.
	 */
	show: function(){
		go.usersettings.UserSettingsDialog.superclass.show.call(this);

		if(!GO.util.isTabletScreenSize()) {
			this.navMenu.select(this.tabStore.getAt(0));
		}
	},
	
	/**
	 * Initiates the submit function of all tabpanels.
	 * 
	 */
	submit : function(){
		// loop through child panels and call onSubmitStart function if available
		var valid = true;
		this.tabPanel.items.each(function(tab) {
			if(tab.onValidate){
				if(!tab.onValidate()) {
					valid = false;					
				}
			}
		},this);
		
		if (!valid || !this.formPanel.getForm().isValid()) {
			return;
		}
		
		if(this.needCurrentPassword()){
			
			var passwordPrompt = new go.PasswordPrompt({
				text: t('Provide your current password to save your user settings.'),
				title: t('Current password required'),
				listeners:{
					'ok': function(value){
						this.internalSubmit(value);
					},
					scope:this
				}
			});

			passwordPrompt.show();
			
		}	else {
			this.internalSubmit();
		}
	},
	
	internalSubmit : function(currentPassword){
		this.actionStart();
		this.fireEvent('submitStart',this);
		
		var id, params = {}, values =  this.formPanel.getForm().getFieldValues(true);
		
		// Check if the password fields are filled in.
		// If not, then remove them from the values array
		
		if(Ext.isEmpty(values.password)){
			delete values.password;
		}
		if(Ext.isEmpty(values.passwordConfirm)){
			delete values.passwordConfirm;
		}
		
		
		// If the currentPassword is set, then add it to the posted values
		if(!Ext.isEmpty(currentPassword)){
			if(!values.password){
				delete values.password;
			}
			values.currentPassword = currentPassword;
		}
		
		// loop through child panels and call onSubmitStart function if available
		this.tabPanel.items.each(function(tab) {
			if(tab.onSubmitStart){
				tab.onSubmitStart(values);
			}
		},this);

		// this.id is null when new
		if(this.currentUserId) {
			id = this.currentUserId;
			params.update = {};
			params.update[this.currentUserId] = values;
		} else {			
			id = Ext.id();
			params.create = {};
			params.create[id] = values;
		}

		go.Db.store("User").set(params, function(options, success, response){
			if(response.notUpdated && response.notUpdated[id] && response.notUpdated[id].validationErrors && response.notUpdated[id].validationErrors.currentPassword){
				// Current password is incorrect.
				this.submit();
				return;
			}
						
			if(response.updated && id in response.updated){
				this.submitComplete(response);
			} else
			{
				if(response.notUpdated && id in response.notUpdated) {
					for (var name in response.notUpdated[id].validationErrors) {
						var field = this.formPanel.getForm().findField(name);
						if (field) {
							field.markInvalid(response.notUpdated[id].validationErrors[name].description);
						}
					}
				}
				
				this.actionComplete();
			}

		},this);
	},
	
	
	/**
	 * Check if  password protected tab fields are changed
	 * 
	 * @return boolean
	 */
	needCurrentPassword : function(){
		
		if(go.User.isAdmin) {
			return false;
		}
		
		var needed = false,
			accountPanel = this.tabPanel.getItem('pnl-account-settings');
		accountPanel.findByType('field').forEach(function(item) {
			if(item.needPasswordForChange) {
				item.validate();
				if(item.isDirty()){
					needed = true;
				}
			}
		});
		return needed ? !this.checkCurrentPasswordSet() : false;
	},
	
	checkCurrentPasswordSet : function(){
		return !Ext.isEmpty(this.formPanel.getForm().baseParams.currentPassword);
	},
	
	
	/**
	 * call this function when submitting of all tabs is completed
	 */
	submitComplete: function(result){
		this.fireEvent('submitcomplete',this, result);
		
		// loop through child panels and call onSubmitComplete function if available
		this.tabPanel.items.each(function(tab) {
			if(tab.onSubmitComplete){
				tab.onSubmitComplete(result);
			}
		},this);
		
		this.actionComplete();
		
		//reload group-office
		if(this.currentUserId == go.User.id) {
			document.location = BaseHref + "?SET_LANGUAGE=" + this.formPanel.getForm().findField('language').getValue();
		} else
		{
			this.close();
		}
	},
		
	/**
	 * Initiates the load of all tabpanels.
	 * 
	 * @param int userId (Optional)
	 */
	load : function(userId){
		var me = this;
		
		function innerLoad(){
			me.currentUserId = userId ? userId : me.currentUserId;
		
			me.actionStart();
			me.fireEvent('loadstart',me, me.currentUserId);

			go.Db.store("User").single(me.currentUserId).then(function(user){
				me.user = user;
				me.loadModulePanels();

				// loop through child panels and call onLoadComplete function if available
				me.tabPanel.items.each(function(tab) {
					if(tab.onLoadStart) {
						tab.onLoadStart(me.currentUserId);
					}
				},me);

				me.formPanel.getForm().setValues(user);

				if(user.id != go.User.id) {
					me.setTitle(t("User") + ": " + Ext.util.Format.htmlEncode(user.username));
				}
				
				me.findBy(function(cmp,cont){
					if(typeof cmp.onLoad === 'function') {
						cmp.onLoad(user);
					}
				},me);

				me.loadComplete(user);
			});
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
	 * call this function when loading of all tabs is completed
	 */
	loadComplete: function(data){
		this.fireEvent('loadcomplete', this, data);
		
		// loop through child panels and call onLoadComplete function if available
		this.tabPanel.items.each(function(tab) {

			if(tab.onLoadComplete){
				tab.onLoadComplete(data);
			}
			
		},this);
		
		this.actionComplete();
	},
	
	/**
	 * Check if the form is valid
	 * 
	 * @return boolean
	 */
	validate: function(){
		return this.formPanel.getForm().isValid();
	},
	
	/**
	 * Get the form fields from the given panel.
	 * When no panelID is given, all form fields are returned.
	 * 
	 * @param string panelID
	 * 
	 * @return [] formfields
	 */
	getFields : function(panelID){
		
		if(!panelID){
			// Return all fields
			return this.formPanel.findByType('field');
		}
		
		// Only return the fields of the given panelID
		return this.tabPanel.getItem(panelID).findByType('field');
	},

	/**
	 * Check if an form item on this panel is marked as dirty
	 * 
	 * @param string panelID
	 * 
	 * @return {Boolean}
	 */
	checkDirty : function(panelID){
		
		var items = this.getFields(panelID);
		var dirty = false;
		
		items.forEach(function(item) {
			item.validate();
			if(item.isDirty()){
				dirty = true;
			}
		});
		
		return dirty;
	},
	
	/**
	 * Add a panel to the tabpanel of this dialog
	 * 
	 * @param string panelClass
	 * @param object panelConfig
	 * @param int position
	 * @param boolean passwordProtected
	 */
	addPanel : function(panelClass, position){
		this._addPanelCmp(new panelClass({
			header: false,
			loaded:false,
			submitted:false
		}), position);
	},
	
	_addPanelCmp : function(pnl, position) {
		var menuRec = new Ext.data.Record({
			name :pnl.title,
			iconCls: pnl.iconCls //.substr(3).replace(/-/g,'_')
		});
		
		if(pnl.isFormField) {
			this.formPanel.getForm().add(pnl);
		}
		
		if(pnl.index) {
			position = pnl.index;
		}
		
		if(Ext.isEmpty(position)){
			this.tabPanel.add(pnl);
			this.tabStore.add(menuRec);
		}else{
			this.tabPanel.insert(position,pnl);
			this.tabStore.insert(position,menuRec);
		}
	},
	
	/**
	 * Call this function when an action is started.
	 * This will disable all action buttons of this window.
	 */
	actionStart : function() {
		if(this.getBottomToolbar()) {
			this.getBottomToolbar().setDisabled(true);
		}
		if(this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(true);
		}
		
		if(this.getFooterToolbar()) {
			this.getFooterToolbar().setDisabled(true);
		}
	},
	
	/**
	 * Call this function when an action is completed.
	 * This will enable all action buttons of this window again.
	 */
	actionComplete : function() {
		if(this.getBottomToolbar()) {
			this.getBottomToolbar().setDisabled(false);
		}
		
		if(this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(false);
		}
		if(this.getFooterToolbar()) {
			this.getFooterToolbar().setDisabled(false);
		}
	}

});



