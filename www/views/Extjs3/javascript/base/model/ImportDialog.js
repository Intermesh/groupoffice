/**
 * This script is made up of two dialogs: one ImportDialog, and one dialog
 * to enable the user to map CSV columns to model fields.
 * @author WilmarVB <wilmar@intermesh.nl>
 */

GO.base.model.ImportDialog = function(config) {
	
	this._initDialog(config); // config MUST have parameters 'controllerName' and 'fileType'
	this._buildForm();
	
	config.title = t("Import");
	config.layout = 'form';
	config.defaults = {anchor:'100%'};
	config.border = false;
	config.labelWidth = 150;
	config.toolbars = [];
	config.cls = 'go-form-panel';
	config.width = 400;
	config.items = [
		this.formPanel
	];
	
	GO.base.model.ImportDialog.superclass.constructor.call(this,config);
	
	this._createAttributesStore();
	
	this.addEvents({
		'import' : true
	});
}

Ext.extend( GO.base.model.ImportDialog, GO.Window, {
	
	/****************************************************************************
	 ****************************************************************************
	 *
	 * Internal Fields
	 *
	 ****************************************************************************
	 *****************************************************************************
	 */
	
	// Fields that MUST be initiated at construction by passing:
	// 'excludedCustomFieldDataTypes', 'modelContainerIdName', 'controllerName' and 'fileType'
	// in the constructor config parameter.
	_importBaseParams : '', // Predefined attributes to set. MUST be object e.g., {addressbook_id : 3, foo: 'bar'}. As an extra effect, model attributes that are set this way will not be imported in this use case.
	_moduleName : '', // e.g., addressbook
	_modelName : '', // e.g., contact
	_fileType : '', // e.g., CSV, VCard
	_excludedCustomFieldDataTypes : ['GO\\Customfields\\Customfieldtype\\Heading','GO\\Customfields\\Customfieldtype\\Function'], // Default setting. These are the custom field types that are excluded from import.
	_excludedAttributes : [], // fields named here are excluded from import.
	
	// Fields that are set while the dialog is being used.
	_colHeaders : {}, // This is a buffer associative array for all the cell values of the uploaded CSV file's first row.
	_attributesStore : null, // Also a buffer, an ArrayStore containing all the current model's attributes.
	_userSelectFieldMappings : {}, // An element of this object is, e.g., this._userSelectFieldMappings[33] = 'first_name';, which says that the 33rd column of the CSV/XLS goes to the t.first_name field of the models.
	_nColumns : 0, // The number of columns in the CSV / XLS(X) file	
	
	_fieldsDialog : null, // The second dialog in the use case.
	_inputIdPrefix : '', // Prefix for the input field id's in the _fieldsDialog.
	
	/****************************************************************************
	 ****************************************************************************
	 *
	 * Methods for the first dialog.
	 *
	 ****************************************************************************
	 *****************************************************************************
	 */
	
	show : function(modelContainerId) {
		this.modelContainerIdField.setValue(modelContainerId);
		this._importBaseParams[this._modelContainerIdName] = modelContainerId;
		GO.base.model.ImportDialog.superclass.show.call(this);
		this.fileSelector.clearQueue();
	},
	
	// Config MUST have parameters
	// 'excludedCustomFieldDataTypes', 'importBaseParams', 'controllerName' and 'fileType'
	_initDialog : function(config) {
		this._importBaseParams = config.importBaseParams;
		var controllerNameArr = config['controllerName'].split('\\');
		this._moduleName = controllerNameArr[1].toLowerCase();
		this._modelName = controllerNameArr[3].toLowerCase();
		this._modelContainerIdName = config['modelContainerIdName'];
		this._fileType = config['fileType'];
		this._excludedAttributes = config['excludedAttributes'] || new Array();
		this._possibleUpdateFindAttributes = config['possibleUpdateFindAttributes'] || new Array();
		for (var attrName in this._importBaseParams) {
			this._excludedAttributes.push(attrName);
		}
	},
	
	// Submit form to import the file.
	_submitForm : function() {
		if (!this._loadMask)
			this._loadMask = new Ext.LoadMask(Ext.getBody(), {msg: t("Importing", "addressbook")+'...'});
		this._loadMask.show();

		if (this._fieldsDialog)
			this._fieldsDialog.hide();

		var updateExisting = false;
		var updateFindAttributes = new Array();
		for (var i=0; i<this._possibleUpdateFindAttributes.length; i++) {
			if (this.formPanel.getForm().findField('updateFindAttributes_'+this._possibleUpdateFindAttributes[i]).getValue()) {
				updateExisting = true;
				updateFindAttributes.push(this._possibleUpdateFindAttributes[i]);
			}
		}


		this.formPanel.form.submit({
			url : GO.url(this._moduleName + '/' + this._modelName + '/import' + this._fileType),
			params : {
				attributeIndexMap : Ext.encode(this._userSelectFieldMappings),
				importBaseParams : Ext.encode(this._importBaseParams),
				maxColumnNr : this._nColumns,
				updateExisting: updateExisting,
				updateFindAttributes: updateFindAttributes
			},
			success : function( success, response, result ) {
				var errorsText = '';
				if (!GO.util.empty(response.result.summarylog)) {
					for (var i=0; i<response.result.summarylog.errors.length; i++) {
						if (i==0)
							errorsText = '<br />' + t("Failed import items") + ':<br />';
						errorsText = errorsText + t("item") + ' ' + response.result.summarylog.errors[i].name + ': ' +
													response.result.summarylog.errors[i].message + '<br />';
					}
					//Ext.MessageBox.alert(t("Error"),errorsText);
				}

				if (!response.result.success) {
					Ext.MessageBox.alert(t("Error"),result.feedback);
				} else {
					if (response.result.totalCount){
						if(response.result.totalCount != response.result.successCount){
							GO.errorDialog.show(
								errorsText,
								t("Records imported successfully", "addressbook")+': '+response.result.successCount+'/'+response.result.totalCount
							);
						} else {
							Ext.MessageBox.alert(
								'',
								t("Records imported successfully", "addressbook")+': '+response.result.successCount+'/'+response.result.totalCount
								+ errorsText
							);
						}
					}else{
						Ext.MessageBox.alert(
							'',
							t("Records imported successfully", "addressbook")
							+ errorsText
						);
					}
						
					this.fireEvent('import');
						
					this.hide();
					if (!GO.util.empty(this._fieldsDialog))
						this._fieldsDialog.hide();
				}
				this._loadMask.hide();
			},
			failure : function ( form, action ) {
				if (!GO.util.empty(action.result.summarylog)) {
					var messageText = '';
					for (var i=0; i<action.result.summarylog.errors.length; i++)
						messageText = messageText + action.result.summarylog.errors[i].message + '<br />';
					Ext.MessageBox.alert(t("Error"),messageText);
				} else if (!GO.util.empty(action.result.feedback)) {
					Ext.MessageBox.alert(t("Error"),action.result.feedback);
				}
				this._loadMask.hide();
			},
			scope: this
		});
	},
	
	// Build form in constructor.
	_buildForm : function() {

		var formItems = new Array();
		
		if (!GO.util.empty(this._possibleUpdateFindAttributes)) {
			formItems.push({
				xtype: 'plainfield',
				value: t("Update items (instead of create new items) with the following matching attributes")+':',
				hideLabel: true
			});
						
			for (var i=0; i<this._possibleUpdateFindAttributes.length;i++) {
				formItems.push(new Ext.form.Checkbox({
					boxLabel: this._possibleUpdateFindAttributes[i],
					name: 'updateFindAttributes_'+this._possibleUpdateFindAttributes[i],
					id: 'updateFindAttributes_'+this._possibleUpdateFindAttributes[i],
					checked: false,
					hideLabel: true
				}));
			}
			
			formItems.push({
				xtype: 'plainfield',
				value: '<hr />',
				hideLabel: true
			});
			
		}

		this.txtDelimiter = new Ext.form.TextField({
			name: 'delimiter',
			fieldLabel: t("Values separated by", "addressbook"),
			allowBlank: false,
			value: GO.settings.list_separator,
			disabled: this._fileType!='CSV',
			hidden: this._fileType!='CSV'
		});
		
		this.txtEnclosure = new Ext.form.TextField({
			name: 'enclosure',
			fieldLabel: t("Values encapsulated by", "addressbook"),
			allowBlank: false,
			value: GO.settings.text_separator,
			disabled: this._fileType!='CSV',
			hidden: this._fileType!='CSV'
		});
		
		this.fileSelector = new GO.form.UploadFile({
			inputName: 'files',
			fieldLabel: t("Upload"),
			max:1
		});
				
		if (this._fileType=='CSV' || this._fileType=='XLS')
			this.fileSelector.on('fileAdded',function(file){
//				this.formPanel.form.submit({
//					url: GO.url(this._moduleName + '/' + this._modelName + '/readCSVHeaders'),
//					success: function(form, action) {
//						
//					},
//					scope: this
//				})
				this.showImportDataSelectionWindow();
			},this);
		
		this.fileTypeField = new Ext.form.TextField({
			hidden: true,
			name: 'fileType',
			value: this._fileType
		});
		
		this.modelContainerIdField = new Ext.form.TextField({
			hidden: true,
			name: this._modelContainerIdName
		});
		
		formItems.push(this.txtDelimiter);
		formItems.push(this.txtEnclosure);
		formItems.push(this.fileSelector);
		formItems.push(this.fileTypeField);
		formItems.push(this.modelContainerIdField);
		
		this.formPanel = new Ext.form.FormPanel({
			fileUpload : true,
			items: formItems,
			buttons: [{
				text: t("Import"),
				width: '20%',
				disabled: this._fileType=='CSV' || this._fileType=='XLS',
				hidden: this._fileType=='CSV' || this._fileType=='XLS',
				handler: function(){
					this._submitForm();
				},
				scope: this
			},{
				text: t("Close"),
				width: '20%',
				handler: function(){
					this.hide();
				},
				scope: this
			}]
		});
		
	},
	
	/****************************************************************************
	 ****************************************************************************
	 *
	 * Methods for the second dialog.
	 *
	 ****************************************************************************
	 *****************************************************************************
	 */
	
	showImportDataSelectionWindow: function()
	{
		this.formPanel.form.submit({
			url: GO.url(this._moduleName + '/' + this._modelName + '/read'+this._fileType+'Headers'),
			success: function(form, action) {
				this._buildImportForm(action.result.results);
				this.el.mask();
				this._fieldsDialog.show();
			},
			failure: function(form, action) {
				this.fileSelector.clearQueue();
				Ext.Msg.alert('UTF-8',t("Error trying to read the data"));
			},
			scope: this
		});
	},
	
	_createAttributesStore : function() {
		var data = [];
		data.push(['-','-','< < '+t("Unused")+' > >']);
		
		if (!(this._attributesStore)) {
			this._attributesStore = new Ext.data.ArrayStore({
				storeId: 'attributesStore',
				idIndex: 0,
				fields:['dbShortFieldName','dbFieldFullName','label']
			});
		}
		
		this._attributesStore.removeAll();
		
		GO.request({
			url: this._moduleName+'/'+this._modelName+'/attributes',
			params: {
				exclude_cf_datatypes: Ext.encode(this._excludedCustomFieldDataTypes),
				exclude_attributes: Ext.encode(this._excludedAttributes)
			},
			success: function(options, response, attributeResult)
			{
				for (var i=0; i<attributeResult.results.length; i++) {
					var nameArray = attributeResult.results[i]['name'].split('.');
					var nameOnly = nameArray[1];
					if (attributeResult.results[i]['gotype']=='customfield') {
						if(go.Modules.isAvailable("core", "customfields"))
							data.push(["customFields."+nameOnly,attributeResult.results[i]['name'],attributeResult.results[i]['label']]);
					} else {
						data.push([nameOnly,attributeResult.results[i]['name'],attributeResult.results[i]['label']]);
					}
				}
				this._attributesStore.loadData(data);
			},
			scope:this
		});	
	},
	
	// Create the second dialog, should be done after every new uploaded file
	// in showImportDataSelectionWindow()
	_buildImportForm : function(colHeaders) {

		this._colHeaders = colHeaders;
		
		if (!this.importFieldsFormPanel) {
			
			this._inputIdPrefix = this._moduleName+'_'+this._modelName+'_import_combo_'+this._fileType+'_';
			
			this.importFieldsFormPanel = new Ext.form.FormPanel({
				waitMsgTarget:true,

				//id: 'addressbook-default-import-data-window',
				labelWidth: 125,
				border: false,
				defaults: { 
					anchor:'-20'
				},
				cls: 'go-form-panel',
				autoHeight:true
			});

			this.importFieldsFormPanel.form.timeout=300;
		} else {
			// This destroys all the form's components for every new uploaded file.
			this.importFieldsFormPanel.removeAll(true);
		}
		
		// Create and add new fields for every column for every new uploaded file.
		for(var colNr=0; colNr<this._colHeaders.length; colNr++)
		{
			var combo =  new Ext.form.ComboBox({
				fieldLabel: this._colHeaders[colNr],
				id: this._inputIdPrefix+colNr,
				store: this._attributesStore,
				displayField:'label',
				valueField:	'dbShortFieldName',
				hiddenName: colNr,
				mode: 'local',
				triggerAction: 'all',
				editable:false
			});

			this.importFieldsFormPanel.add(combo);
		}
		
		/**
		 * This presets the comboboxes of the second dialog, such that any
		 * recognized column field in the form has the matching model attribute
		 * value in its combobox.
		 */
		for(var colNr=0; colNr<this._colHeaders.length; colNr++)
		{
			var colName = this._colHeaders[colNr];
			var matchingRecordId = this._attributesStore.findBy( function findByDisplayField(attributeRecord,id) {
				return !GO.util.empty(colName) && attributeRecord.data.dbShortFieldName.toLowerCase()==colName.toLowerCase();
			}, this);

			if (!GO.util.empty(this._attributesStore.getAt(matchingRecordId)))
				var presetMatchingValue = this._attributesStore.getAt(matchingRecordId).data.dbShortFieldName;
			else
				var presetMatchingValue = '-';

			var component = this.importFieldsFormPanel.getForm().findField(this._inputIdPrefix+colNr);

			component.setValue(presetMatchingValue);
		}

		if (!this._fieldsDialog) {
			this._fieldsDialog = new GO.Window({
				autoScroll:true,
				height: 400,
				width: 400,
				modal:true,
				title: t("Match the fields", "addressbook"),
				items: [
				this.importFieldsFormPanel
				],
				buttons: [{
					text: t("Import"),
					handler: function() {
						this._rememberFieldMappings();
						this._submitForm();
						this.hide();
						this.el.unmask();
					},
					scope: this
				},{
					text: t("Cancel"),
					handler: function(){
						this._fieldsDialog.hide();
						this.hide();
						this.el.unmask();
					},
					scope: this
				}]
			});
		}
	},
	
	/**
	 * Last bit before the import paramaters are submitted: make ready the array
	 * this._userSelectFieldMappings as set by the user. That is basically an array
	 * whose keys are the column number in the uploaded CSV/XLS file (starting from 0),
	 * and whose values are the database field names such as used in the GO
	 * framework queries (e.g. in the case of contact import: t.address_no,
	 * companies.name)
	 */
	_rememberFieldMappings : function() {
		this._userSelectFieldMappings = {};
		this._nColumns = 0;
		Ext.each(this.importFieldsFormPanel.items.items,function(item,index,allItems){
			this._nColumns++;
			if (item.value!='-') {
				var colNr = item.id.replace(this._inputIdPrefix,"");
				this._userSelectFieldMappings[colNr] = item.value;
			}
		},this);
	}
	
});
