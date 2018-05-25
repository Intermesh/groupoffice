/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @author WilmarVB <wilmar@intermesh.nl>
 */

GO.base.QuickEditDialog = function(config) {
	
	config.maximizable = true;
	config.maximized = true;
	config.layout = 'fit';
	config.width = 1024;
	config.height = 640;
	config.title = t("Quick edit");
	
	this.editorGridParams = config.editorGridParams;
	this._buildGrid();
	
	config.items = [this.editGrid];
	
	Ext.apply(this.store.baseParams, config.baseParams);
	
	GO.base.QuickEditDialog.superclass.constructor.call(this,config);
	
	this.addEvents({
		'save' : true
	})
}

Ext.extend(GO.base.QuickEditDialog, GO.Window, {
	
	////////////////////////////////////////////////////////////
	// FIELDS SPECIFIC FOR THIS KIND OF DIALOG
	////////////////////////////////////////////////////////////
	
	editorConfigs : [],
	
	// editorGridParams is an object containing parameters which MUST be externally
	// set in the constructor config parameter on instantiation of a quickEditDialog.
	editorGridParams: {
		moduleName : '', // MANDATORY
		modelName : '', // MANDATORY
		storeFields : [], // MANDATORY
		gridColumns : false, // MANDATORY
		requiredFields : [], // OPTIONAL
		replaceFieldsOnSubmit : {} // OPTIONAL
		// IF replaceFieldsOnSubmit is used, it WILL BE in two ways. See the example
		// from projects module below.
		//				'replaceFieldsOnSubmit' : {
		//					'status_name' : 'status_id',
		//					'type_name' : 'type_id',
		//					'contact' : 'contact',
		//					'customer' : 'customer',
		//					'responsible_user_name' : 'responsible_user_id'
		//				}
		// USAGE (1) When a record is submitted to the server, not the fields 'status_name' 
		//					and 'type_name' are submitted, but 'status_id' and 'type_id'.
		// USAGE (2) For fields with a ComboBox editor, for example 'contact',
		//					setting 'contact' : 'contact' will make sure that (i) the name field of the
		//					ComboBox record is shown on the grid instead of the contact_id, and (ii) that
		//					also the name field of the ComboBox record is submitted.
	},
	
	masterParams : {},
	
	enableOkButton: false,
	enableApplyButton: false,
	
	/////////////////////////////////
	// API-LIKE FUNCTIONS
	/////////////////////////////////
	
	show : function(config) {
		Ext.apply(this.store.baseParams, config.baseParams);
		this.store.load();
		GO.base.QuickEditDialog.superclass.show.call(this);
	},

	// For example, when the grid is actually from a tree, you may want to set
	// something in the lines of params.parent_folder_id to set the folder for
	// the grid before calling show()
	setMasterParams : function(params) {
		this.masterParams = params;
	},
	
	/////////////////////////////////
	// MAIN INTERNAL FUNCTIONS
	/////////////////////////////////
	
	_buildGrid : function(){
		this.store = new GO.data.JsonStore({
			url: GO.url(this.editorGridParams.moduleName+'/'+this.editorGridParams.modelName+'/store'),
			fields: this.editorGridParams.storeFields,
			baseParams:{
				forEditing:true,
				permissionLevel:GO.permissionLevels.write
			},
			remoteSort: true
		});

		this.rowEditor = new GO.grid.RowEditor({
			saveText: t("Apply"),
			height: '40px'
		});

		this.rowEditor.on('afteredit',function(object,changes,record,rowIndex) {			
			this._applyRowChanges(changes,record,rowIndex);
		},this);
		
		this.setRenderers();		

		this.editGrid = new GO.grid.GridPanel({
			height: 'auto',
//			width: 'auto',
			plugins: [ this.rowEditor ],
			store: this.store,
			columns: this.editorGridParams.gridColumns,
			enableColumnMove : false,
			enableDragDrop : false,
			enableHdMenu : false,
			paging: true,
			view: new Ext.grid.GridView({
				autoFill: false,
//				forceFit: true,
				emptyText: t("No items to display")
			}),
			sm:new Ext.grid.RowSelectionModel(),
			loadMask:true,
			clicksToEdit:1
		});
		
//		for (var i=0;i<this.editGrid.getColumnModel().getColumnCount();i++)
//			this.editGrid.getColumnModel().setHidden(i,false);

		GO.request({
			url : this.editorGridParams.moduleName+'/'+this.editorGridParams.modelName+'/attributes',
			success:function(options, response, result)
			{
				this.editorConfigs = this._reformatAttributesResults(result.results,this._getColIds());
				for (var i=0; i<this.editGrid.colModel.config.length; i++) {
					if(!this.editGrid.colModel.config[i].editor)
						this.editGrid.colModel.config[i].setEditor(this.editorConfigs[i]);	
				}
			},
			scope:this
		});

	},
	
	setRenderers : function() {
		for (var i=0; i<this.editorGridParams.gridColumns.length; i++) {
			if (this.editorGridParams.gridColumns[i].datatype=='GO\\Customfields\\Customfieldtype\\Checkbox') {
				this.editorGridParams.gridColumns[i].renderer = function(v) {
					if (v==0 || v==false)
						return t("No");
					return t("Yes");
				}
			}
		}
	},
	
	_applyRowChanges : function(changes,record,rowIndex) {
		var requestParams = record.data;
		for (var c in changes)
			requestParams[c] = changes[c];

		requestParams.id = record.data.id;
		for (var b in this.masterParams)
			requestParams[b] = this.masterParams[b];

		for (var dataIndex in this.editorGridParams.replaceFieldsOnSubmit) {
			if (!GO.util.empty(changes[dataIndex])) {
				delete requestParams[dataIndex];
				var modelFieldname = this.editorGridParams.replaceFieldsOnSubmit[dataIndex];
				requestParams[modelFieldname] = changes[dataIndex];
			}
		}

		Ext.Ajax.request({
			url: GO.url(this.editorGridParams.moduleName+'/'+this.editorGridParams.modelName+'/submit'),
			params: requestParams,
			callback:function(options, success, response)
			{
				var responseParams = Ext.decode(response.responseText);
				if(responseParams.success)
				{
					this._showNameValues(changes,rowIndex);
					this.fireEvent('save',true);
				}else
				{
					Ext.MessageBox.alert(t("Error"),responseParams.feedback);
				}								
			},
			scope:this
		});
	},
	
	/////////////////////////////////
	// HELPER FUNCTIONS
	/////////////////////////////////
	
	_hasKey : function(needle, haystack) {
		for(var currentNeedle in haystack)
			if(currentNeedle == needle) return true;
		return false;
	},
	
	_showNameValues : function(changes,rowIndex) {
		for (var i=0; i<this.editGrid.colModel.config.length; i++) {
			var dataIndex = this.editGrid.colModel.config[i].dataIndex;
			if (this._hasKey(dataIndex,this.editorGridParams.replaceFieldsOnSubmit)) {
				this.editGrid.store.getAt(rowIndex).set(
					dataIndex,
					this.editGrid.colModel.getCellEditor(i,rowIndex).field.lastSelectionText
				);
			}
		}
	},
	
	// The following three methods, _getColIds(), _getResultsData() and _reformatAttributesResults
	// are used to initiate the editors for the rowEditor in _buildGrid()
	
	_getColIds : function() {
		var colIds = [];
		for (var i=0; i<this.editGrid.colModel.config.length; i++) {
			var colConfig = this.editGrid.colModel.config[i];
			colIds.push(colConfig.dataIndex);
		}
		return colIds;
	},
	
	_getResultsData : function(dataIndex,attributesResults) {
		for (var i=0; i<attributesResults.length; i++) {
			var currentNameStringArr = attributesResults[i].name.split('.');
			var currentGoType = attributesResults[i].gotype;
			if (dataIndex==currentNameStringArr[1]) {
				return {
					dataIndex : dataIndex,
					goType : currentGoType
				}
			}
		}
		return false;
	},
	
	_reformatAttributesResults : function(attributesResults,colIds) {
		var editorConfigs = [];
		for (var i=0; i<colIds.length; i++) {
			var resultsData = this._getResultsData(colIds[i],attributesResults);
			if (colIds[i]=='id' || colIds[i]=='ctime' || colIds[i]=='mtime') {
				// These fields MUST NOT be changed by the user.
				config = {
					xtype : 'plainfield',
					readOnly : true,
					hideLabel : true,
					margins: {top:0,bottom:0,left:0,right:1}
				}
			} else if (resultsData===false) {
				// Paranoid little else-branch, in case an attribute is not found on
				// the server or column's dataIndex does not match db field name.
				config = {
					xtype: 'textfield',
					hideLabel : true,
					dataIndex: colIds[i],
					margins: {top:0,bottom:0,left:0,right:1}
				};
			} else  {
				var config = GO.base.form.getFormFieldByType(resultsData.goType,resultsData.dataIndex);
				config.margins = {top:0,bottom:0,left:0,right:1};
			}

			if (this.editorGridParams.requiredFields.indexOf(colIds[i])>=0)
				config.allowBlank = false;
			
			editorConfigs.push(config);
		}
		return editorConfigs;
	}
	
});

//GO.base.QuickEditDialog.inArray = function(needle,haystack) {
//	for(var i=0; i<haystack.length; i++)
//		if (haystack[i]==needle) return i;
//	return false;
//}

GO.base.QuickEditDialog.getValidColDataIds = function (colArray) {
	var validColDataIds = [];
	for (var i=0; i<colArray.length; i++) {
		if (
				colArray[i].datatype!='GO\\Customfields\\Customfieldtype\\Heading' // Doesn't make sense to put a non-editable field in the editorGrid
				&& colArray[i].datatype!='GO\\Customfields\\Customfieldtype\\Treeselect' // Treeselect may be implemented later
				&& colArray[i].datatype!='GO\\Customfields\\Customfieldtype\\TreeselectSlave' // Treeselect may be implemented later
			)
			validColDataIds.push(colArray[i].dataIndex);
	}
	return validColDataIds;
}

/**
 * Clones an array of objects.
 * Param 1: array, the array whose items are to be cloned
 * Param 2: dataIdsToClone, array of ids with which to identify the array's items that are to be cloned
 * Param 3: nameFieldToIdentify, the items' property name with which to identify the items
 */
GO.base.QuickEditDialog.cloneArrayValid = function (array,dataIdsToClone,nameFieldToIdentify) {
	var arrayOfClones = [];
	for (var i=0; i<array.length; i++) {
		if (array[i][nameFieldToIdentify]=='id' || dataIdsToClone.indexOf(array[i][nameFieldToIdentify])>-1) {
			arrayOfClones.push(GO.util.clone(array[i]));
		}
	}
	return arrayOfClones;
}
//
//GO.base.QuickEditDialog.cloneColumns = function (colArray) {
//	var arrayOfClones = [];
//	for (var i=0; i<colArray.length; i++) {
//		arrayOfClones[i] = GO.util.clone(colArray[i]);
//	}
//	return arrayOfClones;
//}
