GO.base.CurrentGridExportDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	jsonPost: true,
	className : null,
	
	initComponent : function(){
		
		Ext.apply(this, {
			title:t("Currently on screen"),
			goDialogId:'current-grid-export-dialog',
			formControllerUrl: 'core/export',
			height:600,
			width:800,
			enableOkButton : true,
			enableApplyButton : false,
			enableCloseButton : true
		});
		
		GO.base.CurrentGridExportDialog.superclass.initComponent.call(this);
	},
	buildForm : function () {

		this.columnsPanel = new GO.base.ColumnSelectPanel({
			region:'center'
		});

		this.viewCombo = new Ext.form.ComboBox({
			fieldLabel : t("Type"),
			hiddenName: 'view',
			name: 'view',
			mode: 'local',
			editable:false,
			triggerAction:'all',
			lazyRender:true,
			width: 120,
			value:"Csv",
			store: new Ext.data.JsonStore({fields: ['view']}),
			valueField: 'view',
			displayField: 'view'
		});
		
//		this.nameField = new Ext.form.TextField({
//			name: 'savedExport.name',
//			width:300,
//			anchor: '100%',
//			maxLength: 100,
//			allowBlank:false,
//			fieldLabel: t("Name")
//		});
		
		this.exportOrientation = new Ext.form.ComboBox({
			fieldLabel : t("Orientation"),
			hiddenName: 'orientation',
			name: 'orientation',
			mode: 'local',
			editable:false,
			triggerAction:'all',
			lazyRender:true,
			width: 120,
			value:"V",
			store: new Ext.data.SimpleStore({
				fields: [
						'id',
						'label'
				],
				data: [['H', t("Landscape")], ['V', t("Portrait")]]
			}),
			valueField: 'id',
			displayField: 'label'
		});
		
		this.useDbColumnNames = new Ext.ux.form.XCheckbox({
			fieldLabel : t("Use DB column names"),
			name       : 'use_db_column_names'
		});
		
		this.includeColumnNames = new Ext.ux.form.XCheckbox({
			fieldLabel : t("Include column names"),
			name       : 'include_column_names'
		});

		this.hiddenColumns = new Ext.form.Hidden({
			name       : 'export_columns'
		});

		this.propertiesPanel = new Ext.Panel({
			title:t("Properties"),
			cls:'go-form-panel',
			layout:'form',
			labelWidth:160,
			items:[
//				this.nameField,
				this.viewCombo,
				this.exportOrientation,
				this.includeColumnNames,
				this.useDbColumnNames,
				this.hiddenColumns
			]
		});
		
		this.addPanel(this.propertiesPanel);
		this.addPanel(this.columnsPanel);
	},
	beforeSubmit : function(params){
		var selected = this.columnsPanel.getSelected();
		this.hiddenColumns.setValue(selected.toString());
		
		this.formPanel.form.baseParams['class_name'] = this.className;
	},
	
	submitForm : function(hide){
		
		//for the fast double clickers
		if(this.getFooterToolbar().disabled)
			return;
		
		var params=this.getSubmitParams();

		if(this.beforeSubmit(params)===false)
			return false;
		
		if(!this.formPanel.form.standardSubmit)
			this.getFooterToolbar().setDisabled(true);

		window.open(GO.url("core/export/export",this.createJSON(params)));
		
		this.getFooterToolbar().setDisabled(false);
		if(hide)
			this.hide();
		
	},
	
	afterLoad : function(remoteModelId, config, action){
		
		this.viewCombo.store.loadData(action.result.supportedViews);
		
		if(action.result.data.savedExport.attributes.view)
			this.viewCombo.setValue(action.result.data.savedExport.attributes.view);
		
		if(action.result.data.savedExport.attributes.include_column_names)
			this.includeColumnNames.setValue(action.result.data.savedExport.attributes.include_column_names);
		
		if(action.result.data.savedExport.attributes.use_db_column_names)
			this.useDbColumnNames.setValue(action.result.data.savedExport.attributes.use_db_column_names);

		this.columnsPanel.reset();
		this.columnsPanel.loadData(action.result.columns);
		if(action.result.data.savedExport.attributes.export_columns)
			this.columnsPanel.setSelected(action.result.data.savedExport.attributes.export_columns,true);
	},
		
	setClass : function(className){
		this.className = className;
	},
	getSubmitParams : function(){
		return {className : this.className};
	},
	checkOrientation : function(selectedRadio){

		if(!selectedRadio.orientation)
			this.exportOrientation.hide();
		else
			this.exportOrientation.show();
		
		this.syncShadow();

	}
});
