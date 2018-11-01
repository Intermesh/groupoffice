/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ExportGridDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.ExportGridDialog = Ext.extend(GO.Window , {
	
	documentTitle : '',
	name : '',
	url : '',
	colModel : '',	
	exportClassPath : "",

	initComponent : function(){

		this.hiddenDocumentTitle = new Ext.form.Hidden({
			name:'documentTitle'
		});
		this.hiddenName = new Ext.form.Hidden({
			name:'name'
		});
		this.hiddenUrl = new Ext.form.Hidden({
			name:'url'
		});
		this.hiddenColumns = new Ext.form.Hidden({
			name:'columns'
		});
		this.hiddenHeaders = new Ext.form.Hidden({
			name:'headers'
		});
	
		this.radioGroup = new Ext.form.RadioGroup({
			fieldLabel : t("Type"),
			name       : 'exportFormat',
			columns: 1,
			items: []
		});
		
		this.radioGroup.on('change', function(){
			this.checkOrientation(this.radioGroup.getValue());
		}, this);
		
		this.includeHidden = new Ext.ux.form.XCheckbox({
			fieldLabel : t("Export hidden columns too"),
			name       : 'includeHidden'
		});
		
		this.humanHeaders = new Ext.ux.form.XCheckbox({
			fieldLabel : t("Use DB column names"),
			name       : 'humanHeaders'
		});
		
		this.includeHeaders = new Ext.ux.form.XCheckbox({
			fieldLabel  : t("Export headers too"),
			name				: 'includeHeaders'
		});
		
		this.exportOrientation = new Ext.form.ComboBox({
			fieldLabel : t("Orientation"),
			hiddenName: 'exportOrientation',
			name: 'exportOrientation',
			mode: 'local',
			editable:false,
			triggerAction:'all',
			lazyRender:true,
			width: 120,
			value:"V",
			store: new Ext.data.SimpleStore({
				fields: [
						'myId',
						'displayText'
				],
				data: [['H', t("Landscape")], ['V', t("Portrait")]]
			}),
			valueField: 'myId',
			displayField: 'displayText'
		});
		
//		this.includeHeaders.setValue(true);
		
		this.hiddenParamsField = new Ext.form.Hidden({
			name:'params'
		});
		
		this.formPanel = new Ext.form.FormPanel({
			url:GO.url(this.url),
			standardSubmit:true,
			waitMsgTarget:true,			
			border: false,
			margin: 10,
			baseParams:{},
			padding: 10,
			labelWidth: 160,
			autoHeight:true,
			items: [
				this.radioGroup,
				this.includeHidden,
				this.includeHeaders,
				this.humanHeaders,
				this.exportOrientation,
				this.hiddenDocumentTitle,
				this.hiddenName,
				this.hiddenUrl,
				this.hiddenColumns,
				this.hiddenHeaders,
				this.hiddenParamsField
			]
		});
		
		
		Ext.apply(this, {
			goDialogId:'export',
			title:t("Export Dialog"),
			autoHeight:true,
			width:400,
			items: [this.formPanel],
			buttons:[
			{ 
				text: t("Export"), 
				handler: function(){ 
					this.submitForm(true);
				}, 
				scope: this 
			},{ 
				text: t("Close"), 
				handler: function(){ 
					this.hide(); 
				}, 
				scope: this 
			}]
		});		
				
		GO.ExportGridDialog.superclass.initComponent.call(this);	
	},
	
	show : function(){
		
		this.hiddenParamsField.setValue(Ext.encode(this.params));
		this.hiddenDocumentTitle.setValue(this.documentTitle);
		this.hiddenName.setValue(this.name);
		this.hiddenUrl.setValue(this.url);
		
	
		if(!this.rendered){
				// Get the available export types for the form
			GO.request({
				url: 'export/load',
				params:{
					exportClassPath:this.exportClassPath
				},
				success: function(response, options, result)
				{
					
					if(result.data.includeHeaders)
						this.includeHeaders.setValue(result.data.includeHeaders);
					
					if(result.data.humanHeaders)
						this.humanHeaders.setValue(result.data.humanHeaders);
					
					if(result.data.includeHidden)
						this.includeHidden.setValue(result.data.includeHidden);
					
					var name;
					var useOrientation;
					var checked=true;
					for(var clsName in result.outputTypes) {
						name = result.outputTypes[clsName].name;
						useOrientation = result.outputTypes[clsName].useOrientation;
						this.createExportTypeRadio(name, clsName, checked, useOrientation);
						checked=false;
					}
					GO.ExportGridDialog.superclass.show.call(this);	
				},
				scope:this
			});
		}else
		{
			GO.ExportGridDialog.superclass.show.call(this);
		}		
	},	
	createExportTypeRadio : function(name,clsName, checked, useOrientation) {
		var radioButton = new Ext.form.Radio({
			  fieldLabel : "",
        boxLabel   : name,
        name       : 'type',
        inputValue : clsName,
				value : clsName,
				checked: checked,
				orientation: useOrientation
		});
		
		this.radioGroup.items.push(radioButton);		
		if(checked && !useOrientation)
			this.exportOrientation.hide();
	},
	checkOrientation : function(selectedRadio){

		if(!selectedRadio.orientation)
			this.exportOrientation.hide();
		else
			this.exportOrientation.show();
		
		this.syncShadow();
		
	},
	addFormElement : function(elementToAdd){
		this.formPanel.add(elementToAdd);
	},
	insertFormElement : function(targetIndex, elementToAdd){
		this.formPanel.insert(targetIndex, elementToAdd);
	},
	
	beforeSubmit : function(columns, headers){
		
	},
	
	submitForm : function(hide) {
		this.formPanel.form.getEl().dom.target='_blank';
		this.formPanel.form.el.dom.target='_blank';
		
		// Get the columns that needs to be exported from the grid.
		var columns = [];
		var headers = [];
			
		var exportHidden = this.includeHidden.getValue();

		if (this.colModel) {
			for (var i = 0; i < this.colModel.getColumnCount(); i++) {
				var c = this.colModel.config[i];

				if ((exportHidden || !c.hidden) && !c.hideInExport)
					columns.push(c.dataIndex);
					headers.push(c.header);
			}
		}
		
		this.beforeSubmit(columns, headers);
		
		this.hiddenColumns.setValue(columns.join(','));
		this.hiddenHeaders.setValue(headers.join(','));
		
		

		this.formPanel.form.submit(
		{
			url:GO.url(this.url),
			params: {
				'name': this.name,
				'documentTitle' : this.documentTitle	
			},
			waitMsg:t("Saving..."),
			success:function(form, action) {		
				//console.log("SUCCESSFULL");
			},		
			failure: function(form, action) {
				if(action.failureType == 'client')			
					Ext.MessageBox.alert(t("Error"), t("You have errors in your form. The invalid fields are marked."));			
			 else
					Ext.MessageBox.alert(t("Error"), action.result.feedback);
			},
			scope: this
		});
		
		if(hide)
			this.hide();	
	}	
});



//TODO: CHANGE TO A TABBEDFORM DIALOG. THIS IS NOT WORKING AND NEED TO BE CHECKED

//GO.ExportGridDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
//
//	initComponent : function(){
//		
//		Ext.apply(this, {
//			goDialogId:'export',
//			title:t("Export Dialog"),
//			//autoHeight:true,
//			height:400,
//			width:400,
//			formControllerUrl : 'export',
//			enableOkButton : false,
//			enableApplyButton : false,
//			buttons:[{ 
//				text: t("Export"), 
//				handler: function(){ 
//					this.submitForm();
//				}, 
//				scope: this 
//			},{ 
//				text: t("Close"), 
//				handler: function(){ 
//					this.hide(); 
//				}, 
//				scope: this 
//			}]
//		});
//		
//		GO.ExportGridDialog.superclass.initComponent.call(this);	
//	},
//	  
//	buildForm : function () {
//		
//		this.hiddenDocumentTitle = new Ext.form.Hidden({name:'documentTitle'});
//		this.hiddenName = new Ext.form.Hidden({name:'name'});
//		this.hiddenUrl = new Ext.form.Hidden({name:'url'});
//		this.hiddenColumns = new Ext.form.Hidden({name:'columns'});
//		this.hiddenHeaders = new Ext.form.Hidden({name:'headers'});
//	
//		this.radioGroup = new Ext.form.RadioGroup({
//			fieldLabel : t("Type"),
//			name       : 'exportFormat',
//			columns: 1,
//			items: []
//		});
////		
////		this.radioGroup.on('change', function(){
////			this.checkOrientation(this.radioGroup.getValue());
////		}, this);
//		
//		this.includeHidden = new Ext.form.Checkbox({
//			fieldLabel : t("Export hidden columns too"),
//			name       : 'includeHidden'
//		});
//		
//		this.humanHeaders = {
//			xtype				: 'xcheckbox',
//			fieldLabel	: t("Use DB column names"),
//			name				: 'humanHeaders',
//			checked			: true
//		};
//		
//		this.includeHeaders = {
//			xtype				: 'xcheckbox',
//			fieldLabel  : t("Export headers too"),
//			name				: 'includeHeaders'
//		};
//		
//		this.exportOrientation = new Ext.form.ComboBox({
//			fieldLabel : t("Orientation"),
//			hiddenName: 'exportOrientation',
//			name: 'exportOrientation',
//			mode: 'local',
//			editable:false,
//			triggerAction:'all',
//			lazyRender:true,
//			width: 120,
//			value:"V",
//			store: new Ext.data.SimpleStore({
//				fields: [
//						'myId',
//						'displayText'
//				],
//				data: [['H', t("Landscape")], ['V', t("Portrait")]]
//			}),
//			valueField: 'myId',
//			displayField: 'displayText'
//		});
//		
//	//	this.includeHeaders.setValue(true);
//		
//		this.hiddenParamsField = new Ext.form.Hidden({
//			name:'params'
//		});
//		
//		this.propertiesPanel = new Ext.Panel({
//			title:t("Properties"),			
//			cls:'go-form-panel',
//			layout:'form',
//			items:[
//				this.radioGroup,
//				this.includeHidden,
//				this.includeHeaders,
//				this.humanHeaders,
//				this.exportOrientation,
//				this.hiddenDocumentTitle,
//				this.hiddenName,
//				this.hiddenUrl,
//				this.hiddenColumns,
//				this.hiddenHeaders,
//				this.hiddenParamsField
//      ]				
//		});
//
//    this.addPanel(this.propertiesPanel);
//	},
//	afterLoad : function(remoteModelId, config, action){
//		
//		this.hiddenParamsField.setValue(Ext.encode(this.params));
//		this.hiddenDocumentTitle.setValue(this.documentTitle);
//		this.hiddenName.setValue(this.name);
//		this.hiddenUrl.setValue(this.url);
//
//		var name;
//		var useOrientation;
//		var checked=true;
//		for(var clsName in action.result.outputTypes) {
//			name = action.result.outputTypes[clsName].name;
//			useOrientation = action.result.outputTypes[clsName].useOrientation;
//			this.createExportTypeRadio(name, clsName, checked, useOrientation);
//			checked=false;
//		}
//
//	},
//	createExportTypeRadio : function(name,clsName, checked, useOrientation) {
//		var radioButton = new Ext.form.Radio({
//			  fieldLabel : "",
//        boxLabel   : name,
//        name       : 'type',
//        inputValue : clsName,
//				value : clsName,
//				checked: checked,
//				orientation: useOrientation
//		});
//		
//		this.radioGroup.items.push(radioButton);		
//		if(checked && !useOrientation)
//			this.exportOrientation.hide();
//	},
//	checkOrientation : function(selectedRadio){
//
//		if(!selectedRadio.orientation)
//			this.exportOrientation.hide();
//		else
//			this.exportOrientation.show();
//		
//		this.syncShadow();
//		
//	}
//});
