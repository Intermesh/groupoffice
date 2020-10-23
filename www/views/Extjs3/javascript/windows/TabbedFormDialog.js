/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: TabbedFormDialog.js 22300 2018-01-30 13:40:04Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 *
 * If you extend this class, you MUST use the addPanel method to add at least
 * one panel to this dialog. A tabPanel is automatically created if and only if
 * more than one panel is added to the dialog in this way.
 */

//GO.dialog.TabbedFormDialog = function(config) {
//	
//	config = config | {};
//	
//	if (config.title)
//		this.baseTitle = config.title;
//	
//	GO.dialog.TabbedFormDialog.superclass.constructor(this,config);
//}
GO.dialog.TabbedFormDialog = Ext.extend(GO.Window, {
	
	/**
	 * Set to false if you don't want to load the form on show when creating a new
	 * model.
	 */
	loadOnNewModel : true,
	
	
	/**
	 * Use this parameter to create a separate controller action for update and create
	 * Warning: When setting this parameter you also need to set the "createAction" parameter.
	 * 
	 * Example value: 'update'
	 * 
	 */
	updateAction : false,
	
	/**
	 * Use this parameter to create a separate controller action for create and update
	 * Warning: When setting this parameter you also need to set the "updateAction" parameter.
	 * 
	 * Example value: 'create'
	 * 
	 */
	createAction	: false,
	
	remoteModelId : 0,
	
	/**
	 * a value of one of the forms fields that will be used for the title of the dialog
	 */
	titleField : false,
	
	submitAction : 'submit',
	
	loadAction : 'load',
	
	forceTabs : false,
	/**
	 * When set to false the emptyText value will not be posted to the server
	 */
	submitEmptyText: true,
	/**
	 * This option can be set to create a helplink in the toolbar of this dialog.
	 * When you want to enable this then pass a string of the correct helplink id.
	 * 
	 * Example 'zpushadmin_settings'
	 *
	 */
	helppage : false,

	/**
	 * The controller will be called with this post parameter.
	 *
	 * $_POST['id'];
	 *
	 * This should not be changed.
	 */
	remoteModelIdName : 'id',

	/**
	 * This variable must point to a Form controller on the remoteModel that will be
	 * called with /load and /submit.
	 *
	 * eg. GO.url('notes/note');
	 */
	formControllerUrl : 'undefined',

	/**
	 * Set this if your item supports custom fields.
	 */
	customFieldType : 0,
	
	/**
	 * The modelname that is used to search for customfields and comments
	 * 
	 * Example: "GO\\Notes\\Model\\Note"
	 * 
	 */
	modelName : false,
	

	
	/**
	 * Enable the customfields tab when the customfieldsmodule is installed
	 */
//	enableCustomfields : false,	 // NOT YET USED BUT NEEDS TO REPLACE THE customFieldType FIELD
	
	/**
	 * If set this panel will automatically listen to an acl_id field in the model.
	 */
	permissionsPanel : false,
	
	/**
	 * Config variable that is passed on the show function of this dialog.
	 */
	showConfig : false,
	
	/**
	 * Set to true when files needed to be uploaded
	 */
	fileUpload : false,
	

	_panels : false,
	
	_relatedGrids : false,
	
	enableOkButton : true,
	
	enableApplyButton : true,
	
	enableCloseButton : false,
	
	/**
	 * Indicates if the form is currenty loading
	 */
	loading : false,
	/**
	 * The data array in the JSON response after the loadAction is called
	 */
	loadData : false,
	
	
	jsonPost : false,	
	
	closeAction: 'hide',
	
	initComponent : function(){
		
		Ext.applyIf(this, {
			collapsible:true,
			layout:'fit',
			modal:false,
			resizable:true,
			maximizable:true,
			width:600,
			height:400
		});
		
		if(this.jsonPost){
			this.createAction = this.createAction || 'create';
			this.updateAction = this.updateAction || 'update';
		}
		
		if(this.helppage !== false){
			if(!this.tools){
				this.tools=[];

				this.tools.push({
					id:'help',
					qtip: t("Help"),
					handler: function(event, toolEl, panel){
						GO.openHelp(this.helppage);
					},
					scope:this
				});
			}
		}
		
		
		var buttons = [];
		
		// These three buttons are enabled by default.
		if (this.enableCloseButton)
			buttons.push(this.buttonClose = new Ext.Button({
				text: t("Close"),
				handler: function(){
					this.hide();
				},
				scope:this
			}));
		
		if (this.enableApplyButton)
			buttons.push(this.buttonApply = new Ext.Button({
				text: t("Apply"),
				handler: function(){
					this.submitForm();
				},
				scope:this
			}));
		if (this.enableOkButton)
			buttons.push(this.buttonOk = new Ext.Button({
				text: t("Save"),
				handler: function(){
					this.submitForm(true);
				},
				primary: true,
				scope: this
			}));
		
		if(!Ext.isEmpty(buttons)) {
			Ext.applyIf(this, {
				buttons: buttons
			});
		}


		
		this._panels=[];
		
		this._relatedGrids=[];

		this.buildForm();

		

		this.addCustomFields();
	
		this.formPanelConfig=this.formPanelConfig || {};
		this.formPanelConfig = Ext.apply(this.formPanelConfig, {
			waitMsgTarget:true,			
			border: false,
			fileUpload: this.fileUpload,
			baseParams : {},
			layout:'fit'
		});
		
		this.formPanel = new Ext.form.FormPanel(this.formPanelConfig);

		if(this._panels.length > 1 || this.forceTabs) {		    
			this._tabPanel = new Ext.TabPanel({
				activeTab: 0,
				enableTabScroll:true,
				deferredRender: false,
				border: false,
				anchor: '100% 100%',
				items: this._panels
			});
		    
			this.formPanel.add(this._tabPanel);
		} else if (this._panels.length==1) {			
//			this._panels[0].items.each(function(item){
//				this.formPanel.add(item);
//			}, this);
//			
//			if(this._panels[0].cls)
//				this.formPanel.cls=this._panels[0].cls;
//			
//			if(this._panels[0].bodyStyle)
//				this.formPanel.bodyStyle=this._panels[0].bodyStyle;
//			
//			delete this._panels[0];

			delete this._panels[0].title;
			this._panels[0].header=false;
			if(this._panels[0].elements)
				this._panels[0].elements=this._panels[0].elements.replace(',header','');

			this.formPanel.add(this._panels[0]);
		}
		
		this.items=this.formPanel;

		//Add a hidden submit button so the form will submit on enter

		//problem with submit when searching

		// this.formPanel.add(new Ext.Button({
		// 	hidden: true,
		// 	hideMode: "offsets",
		// 	type: "submit",
		// 	handler: function() {
		// 		this.submitForm(true);
		// 	},
		// 	scope: this
		// }));
		
		GO.dialog.TabbedFormDialog.superclass.initComponent.call(this); 
		
		this.addEvents({
			'submit' : true
		});
	},
	focus : function(){		
		var firstField = this.formPanel.form.items.find(function(item){
			if(!item.disabled && item.isVisible())
				return true;
		});
		if(firstField)
			firstField.focus();		
	},
	
	refreshActiveDisplayPanels : function(){
		var activeTab = GO.mainLayout.tabPanel.getActiveTab();			
		var dp = activeTab.findBy(function(comp){
			if(comp.isDisplayPanel)
				return true;
		});
					
		//TODO inefficient? Contact display panel reloaded when company is saved.
		for(var i=0;i<dp.length;i++)
			dp[i].reload();				
		
		Ext.WindowMgr.each(function(win){
			if(win.isVisible()){
				var dp = win.findBy(function(comp){
					if(comp.isDisplayPanel)
						return true;
				});
				
				if(dp.length)
					dp[0].reload();	
			}
		});
		
	},
	
	addButton : function(button){
		var tb = this.getFooterToolbar();
		tb.addButton(button);
	},

	/**
	 * Change where custom field sets are added
	 */
	customFieldsContainer : null,

	addCustomFields : function(){
		if(!this.customFieldType) {
			return;
		}

		if(!this.customFieldsContainer) {
			this.customFieldsContainer = this._panels[0];
		}
	
		if(go.Entities.get(this.customFieldType).customFields) {
			var fieldsets = go.customfields.CustomFields.getFormFieldSets(this.customFieldType);
			fieldsets.forEach(function(fs) {
				//console.log(fs);
				if(fs.fieldSet.isTab) {
					fs.title = null;
					fs.collapsible = false;
					var pnl = new Ext.Panel({
						autoScroll: true,
						hideMode: 'offsets', //Other wise some form elements like date pickers render incorrectly.
						title: fs.fieldSet.name,
						items: [fs]
					});
					this.addPanel(pnl);
				}else
				{			
					this.customFieldsContainer.add(fs);
				}
			}, this);
		}

	},
	


	getSubmitParams : function(){
		return {};
	},
	
	beforeSubmit : function(params){
		
	},
	
	/*
	 * Check for updateAction and createAction parameter
	 */
	checkSubmitMethod : function(){
		if(this.createAction != false && this.updateAction !=false){
			if(this.isNew()){
				this.submitAction = this.loadAction = this.createAction;
			} else {
				this.submitAction = this.loadAction = this.updateAction;
			}
		}
	},	
	
	/*
	 * Return true when the dialogs data is not loaded from the database
	 */
	isNew : function() {
	  return (this.remoteModelId == 0);
	},
	
	
	createJSON : function(params){
		
		this.formPanel.form.baseParams = this.formPanel.form.baseParams || {};
		
		var p = Ext.apply(this.formPanel.form.baseParams, params);
//		var values = Ext.apply(p,this.formPanel.form.getValues()); // BROKEN
	//	var values = Ext.applyIf(this.formPanel.form.getFieldValues(),p); // APPLYIF NEEDED????
		var values = Ext.apply(this.formPanel.form.getFieldValues(),p);
		values = Ext.apply(this.formPanel.form.getValues(),values);

		var keys, JSON={}, currentJSONlevel;
		
		for(var key in values){
			
			keys = key.split('.');
			
			currentJSONlevel = JSON;
			
			for(var i=0;i<keys.length;i++){
				if(i===(keys.length-1)){
					currentJSONlevel[keys[i]]= values[key];
				}else
				{
					currentJSONlevel[keys[i]]=currentJSONlevel[keys[i]] || {};
					currentJSONlevel=currentJSONlevel[keys[i]];
				}				
			}
			
			currentJSONlevel = JSON;
			
		}

		return JSON;
	},
	
	//find value in json object with dotted path. eg. category.acl_id
	findJsonValue : function(field, data){
		var keys = field.split('.');
		
		var currentJSONlevel = data;

		for(var i=0, c=keys.length-1;i<c;i++){
			currentJSONlevel=currentJSONlevel[keys[i]];

			
			if(!currentJSONlevel){
				return null;
			}
		}
		
		
		return currentJSONlevel[keys[i]];
	},
	
	jsonSubmit: function(params,hide, config) {

		GO.request({
			method:'POST',
			url: this.formControllerUrl + '/' + this.submitAction,
			params: Ext.apply(params,{
				id:this.remoteModelId
			}),
			jsonData: this.createJSON(params),
			waitMsg: t("Saving..."),
			scope: this,
			success: function(response, options, result) {
				this.getFooterToolbar().setDisabled(false);
				if (result[this.remoteModelIdName])
					this.setRemoteModelId(result[this.remoteModelIdName]);

				if (result.data && result.data[this.remoteModelIdName])
					this.setRemoteModelId(result.data[this.remoteModelIdName]);

				
				if (this.permissionsPanel){						
					var acl_id = this.findJsonValue(this.permissionsPanel.fieldName, result.data);
					this.permissionsPanel.setAcl(acl_id);
				}						

				this.afterSubmit({result: result, response: response, options:options});

				if (result.summarylog) {

					if (!this.summaryDialog) {
						this.summaryDialog = new GO.dialog.SummaryDialog();
					}
					this.summaryDialog.setSummaryLog(result.summarylog);
					this.summaryDialog.show();
				}

				this.fireEvent('submit', this, this.remoteModelId);
				this.fireEvent('save', this, this.remoteModelId);

				if (hide)
				{
					this.hide();
				}

				this.refreshActiveDisplayPanels();

				if (this.link_config && this.link_config.callback)
				{
					if (!this.link_config.scope)
						this.link_config.scope = this;

					this.link_config.callback.call(this.link_config.scope);
				}
				this.updateTitle();
				
				if(config && config.callback) {
					config.callback.call(config.scope || this, this);
				}
			},
			fail: function(response, options, result) {
				this.getFooterToolbar().setDisabled(false);

				Ext.MessageBox.alert(t("Error"), result.feedback);

				if (result.validationErrors) {
					for (var modelName in result.validationErrors) {
						
						for(var attr in result.validationErrors[modelName]){
						
							var fieldName = modelName+"."+attr;
							this.formPanel.form.findField(fieldName).markInvalid(result.validationErrors[modelName][attr]);
						}
					}
				}
			}
			
		});
	},
	
	submitForm : function(hide, config){
		
		//for the fast double clickers
		if(this.getFooterToolbar().disabled)
			return;
		
		var params=this.getSubmitParams();

		if(this.beforeSubmit(params)===false)
			return false;
		
		if(!this.formPanel.form.standardSubmit)
			this.getFooterToolbar().setDisabled(true);
		
		
		if(this.jsonPost){
			if(this.formPanel.form.isValid()) {
				this.jsonSubmit(params, hide, config);
			} else {
				this.getFooterToolbar().setDisabled(false);
			}
		}else
		{


			this.formPanel.form.submit(
			{
				url:GO.url(this.formControllerUrl+'/'+this.submitAction),
				params: params,
				submitEmptyText: this.submitEmptyText,
				waitMsg:t("Saving..."),
				success:function(form, action){		
					this.getFooterToolbar().setDisabled(false);
					if(action.result[this.remoteModelIdName])
						this.setRemoteModelId(action.result[this.remoteModelIdName]);

					if(action.result.data && action.result.data[this.remoteModelIdName])
						this.setRemoteModelId(action.result.data[this.remoteModelIdName]);

					if(this.permissionsPanel && action.result[this.permissionsPanel.fieldName])
						this.permissionsPanel.setAcl(action.result[this.permissionsPanel.fieldName]);

					this.afterSubmit(action);

					if(action.result.summarylog){

						if(!this.summaryDialog){
							this.summaryDialog = new GO.dialog.SummaryDialog();
						}
						this.summaryDialog.setSummaryLog(action.result.summarylog);
						this.summaryDialog.show();
					}

					this.fireEvent('submit', this, this.remoteModelId);
					this.fireEvent('save', this, this.remoteModelId);

					if(hide)
					{
						this.hide();
					}

					this.refreshActiveDisplayPanels();

					if(this.link_config && this.link_config.callback)
					{	
						if(!this.link_config.scope)
							this.link_config.scope = this;

						this.link_config.callback.call(this.link_config.scope);						
					}
					this.updateTitle();
					
					
					if(config && config.callback) {
						config.callback.call(config.scope || this, this);
					}
				},		
				failure: function(form, action) {
					this.getFooterToolbar().setDisabled(false);
					if(action.failureType == 'client')
					{					
						Ext.MessageBox.alert(t("Error"), t("You have errors in your form. The invalid fields are marked."));			
					} else {					

						if(this.fileUpload){
							action.result.feedback=Ext.util.Format.nl2br(action.result.feedback);
						}

						Ext.MessageBox.alert(t("Error"), action.result.feedback);

						if(action.result.validationErrors){
							for(var field in action.result.validationErrors){
								form.findField(field).markInvalid(action.result.validationErrors[field]);
							}
						}
					}

					go.form.Dialog.prototype.showFirstInvalidField.call(this);
				},
				scope: this
			});	
		}
	},
  
	beforeLoad : function(remoteModelId, config){},
	afterLoad : function(remoteModelId, config, action){},
	afterSubmit : function(action){},	
	
	
	jsonLoad : function(remoteModelId, config){
		
		Ext.applyIf(config.loadParams,this.loadParams);
		
		GO.request({
			method:'GET',
			url: this.formControllerUrl + '/' + this.submitAction,
			params:Ext.apply(config.loadParams,{
				id:this.remoteModelId
			}),
			jsonData: this.createJSON(),
			waitMsg: t("Saving..."),
			scope: this,
			success: function(response, options, result) {
			
				
				
				
				//apply values
				for(var modelName in result['data']){
					if(typeof result==="object"){
						
						//Only one supported atm.
						if(this.permissionsPanel)
							this.permissionsPanel.setAcl(result['data'][modelName]['attributes']['acl_id']);
						
						
						for(var attr in result['data'][modelName]['attributes']){
							
							if(attr!=='relatedLabels'){
								var fieldName = modelName+"."+attr;

								var f = this.formPanel.form.findField(fieldName);

								if(f){								
									f.setValue(result['data'][modelName]['attributes'][attr]);

									if(result['data'][modelName]['relatedLabels'] && result['data'][modelName]['relatedLabels'][attr]){
										f.setRemoteText(result['data'][modelName]['relatedLabels'][attr]);
									}
								}
							}
						}
					}
				}

				if(config && config.values)
					this.formPanel.form.setValues(config.values);

				this.loadData = result.data;
				
				this.afterLoad(remoteModelId, config, {response:response, options:options, result:result});
	
								
				GO.dialog.TabbedFormDialog.superclass.show.call(this);
				this.afterShowAndLoad(remoteModelId, config, result);

				this.formPanel.form.clearInvalid();

				this.updateTitle();

				this.loading=false;
			}			
		});
	},
	
	
	setLabels : function(result){
		if(result.remoteComboTexts){
			var t = loadAction.result.remoteComboTexts;
			for(var fieldName in t){
				var f = this.formPanel.form.findField(fieldName);				
				if(f)
					f.setRemoteText(t[fieldName]);
			}
		}
	},
	
	show : function (remoteModelId, config) {
		
		
		this.loading=true;
		
		config = config || {};
				
		if(!config.loadParams)
			config.loadParams={};
		
		this.showConfig = config;
		
		this.beforeLoad(remoteModelId, config);

		//tmpfiles on the remoteModel ({name:'Name',tmp_file:/tmp/name.ext} will be attached)
		this.formPanel.baseParams.tmp_files = config.tmp_files ? Ext.encode(config.tmp_files) : '';
				
		if(!this.rendered)
			this.render(Ext.getBody());
		
		if(!remoteModelId)
		{
			remoteModelId=0;
		}
		
		delete this.link_config;
		this.formPanel.form.reset();	

		if(this._tabPanel)
			this._tabPanel.items.items[0].show();
			
		this.setRemoteModelId(remoteModelId);
		
//		//set dialog in new or edit mode
//		this.checkSubmitMethod();
		
		if(remoteModelId || this.loadOnNewModel)
		{
			
			if(this.jsonPost){
				this.jsonLoad(remoteModelId, config);
			}else
			{
				this.formPanel.load({
					params:config.loadParams,
					url:GO.url(this.formControllerUrl+'/'+this.loadAction),
					method: 'GET',
					success:function(form, action)
					{										
						this.setRemoteComboTexts(action);

						if(this.permissionsPanel)
							this.permissionsPanel.setAcl(action.result.data[this.permissionsPanel.fieldName]);

						if(config && config.values)
							this.formPanel.form.setValues(config.values);

						this.loadData = action.result.data;
						
						this.afterLoad(remoteModelId, config, action);

						
						GO.dialog.TabbedFormDialog.superclass.show.call(this);
						this.afterShowAndLoad(remoteModelId, config, action.result);

						this.formPanel.form.clearInvalid();

						this.updateTitle();

						this.loading=false;
					},
					failure:function(form, action)
					{
						this.loading=false;
						GO.errorDialog.show(action.result.feedback);
					},				
					scope: this				
				});
			}
		} else {
			if(config && config.values)
				this.formPanel.form.setValues(config.values);
			
			this.updateTitle();
			
			GO.dialog.TabbedFormDialog.superclass.show.call(this);
			
			this.afterShowAndLoad(remoteModelId, config);
			
			this.loading=false;
		}
		
		//if the newMenuButton from another passed a linkTypeId then set this value in the select link field
		if(config && config.link_config)
		{	
			this.link_config=config.link_config;
			if(this.selectLinkField){
				this.selectLinkField.container.up('div.x-form-item').setDisplayed(remoteModelId==0);
				if(config.link_config.modelNameAndId)
				{
					this.selectLinkField.setValue(config.link_config.modelNameAndId);
					this.selectLinkField.setRemoteText(config.link_config.text);
				}
			}
		}
	
	},
	
	setRemoteComboTexts : function(loadAction){
		if(loadAction.result.remoteComboTexts){
			var t = loadAction.result.remoteComboTexts;
			for(var fieldName in t){
				var f = this.formPanel.form.findField(fieldName);				
				if(f)
					f.setRemoteText(t[fieldName]);
			}
		}
	},
	
	
	afterShowAndLoad : function (remoteModelId, config, result){
		
	},

	updateTitle : function() {
		
		if(this.titleField)
		{
			var f=this.formPanel.form.findField(this.titleField);
			if(f){
				if(!this.origTitle)
					this.origTitle=this.title;

				var titleSuffix = this.remoteModelId > 0 ? f.getValue() : t("New");

				this.setTitle(Ext.util.Format.htmlEncode(this.origTitle+": "+titleSuffix));
			}
		}
	},

	setRemoteModelId : function(remoteModelId)
	{
		this.formPanel.form.baseParams[this.remoteModelIdName]=remoteModelId;
		this.remoteModelId=remoteModelId;		
		this.checkSubmitMethod();
		this.setRelatedGridParams(remoteModelId);
	},
	
	setRelatedGridParams : function(remoteModelId){
		for(var i=0;i<this._relatedGrids.length;i++){
			var relGrid = this._relatedGrids[i];
			relGrid.gridPanel.setDisabled(GO.util.empty(remoteModelId));
			relGrid.gridPanel.store.baseParams[relGrid.paramName]=remoteModelId;
			if(remoteModelId<1)				
				relGrid.gridPanel.store.removeAll();
		}
	},

	/**
	 * Use this function to add panels to the window.
	 * 
	 * @var relatedGridParamName Set to the field name of the has_many relation. 
	 * eg. Addressbook dialog showing contacts would have this value set to addressbook_id
	 */
	addPanel : function(panel, relatedGridParamName){
		
		panel.tabbedFormDialog=this;
		
		this._panels.push(panel);
		
		if(relatedGridParamName){		
			panel.relatedGridParamName=relatedGridParamName;
			this._relatedGrids.push({gridPanel: panel, paramName:relatedGridParamName});
			panel.on('show', function(grid){
				if(grid.store.baseParams[grid.relatedGridParamName]>0)
					grid.store.load();
			}, this);
		}
	},
	
	/**
	 * Use this function add an GO.grid.PermissionsPanel to the form.
	 * 
	 * @var GO.grid.PermissionsPanel panel
	 */
	addPermissionsPanel : function(panel){
		this.permissionsPanel = panel;
		this.addPanel(panel);
	},


	/**
	 * Override this function to build your form. Call addPanel to add panels.
	 */
	buildForm : function () {

	},
	addBaseParam : function(param,value){
		this.formPanel.baseParams[param] = value;
	}
});
