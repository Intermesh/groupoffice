/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 */
 
GO.customfields.ManageBlockDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	initComponent : function(){
		
		Ext.apply(this, {
			titleField:'id',
			goDialogId:'block',
			title:t("Block", "customfields"),
			formControllerUrl: 'customfields/block',
			height:200
		});
		
		GO.customfields.ManageBlockDialog.superclass.initComponent.call(this);	
	},
	buildForm : function () {

		this.propertiesPanel = new Ext.Panel({
			border: false,
			title:t("Properties"),			
			cls:'go-form-panel',waitMsgTarget:true,			
			layout:'form',
			autoScroll:true,
			items:[{
				xtype: 'textfield',
			  name: 'name',
				anchor: '100%',
			  allowBlank:false,
			  fieldLabel: t("Name")
			}, this.selectCustomfieldBox = new GO.form.ComboBox({
				fieldLabel: t("Custom field", "customfields"),
				hiddenName:'field_id',
				anchor:'-20',
				store: new GO.data.JsonStore({
					url:GO.url('customfields/blockField/selectStore'),
					totalProperty: "total",
					root: "results",
					id: "id",
					fields:[
						'id',
						'full_info',
						'name',
						'datatype',
						'extendsModel'
					]
				}),
				valueField:'id',
				displayField:'full_info',
				mode: 'remote',
				triggerAction: 'all',
				editable: false,
				selectOnFocus:true,
				forceSelection: true
			}),this.datatypeField = new GO.form.PlainField({
				name: 'datatype',
				fieldLabel: t("Custom field's data type", "customfields")
			}),this.extendsModelField = new GO.form.PlainField({
				name: 'extendsModel',
				fieldLabel: t("Custom field used in", "customfields")
			})]
		});

		this.selectCustomfieldBox.on('change',function(combo,newValue,oldValue){
			var record = combo.store.getById(newValue);
			this._updateModelNames(record);
		}, this);

		this.selectCustomfieldBox.store.on('load', function(){
			var fieldId = this.selectCustomfieldBox.getValue();
			if (fieldId > 0)
				var record = this.selectCustomfieldBox.store.getById(fieldId);
			if (!GO.util.empty(record))
				this._updateModelNames(record);
		}, this);

		this.addPanel(this.propertiesPanel);	
 
	},
	
	afterLoad : function(remoteModelId, config, action){
		this.selectCustomfieldBox.store.load();
	},

	_updateModelNames : function(customFieldRecord) {
		this.datatypeField.setValue(GO.customfields.lang[customFieldRecord.data['datatype']]);
		this.extendsModelField.setValue(GO.customfields.lang[customFieldRecord.data['extendsModel']]);
	}
});
