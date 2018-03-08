/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PresidentDialog.js 17133 2014-03-20 08:25:24Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.presidents.PresidentDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	customFieldType : "GO\\Presidents\\Model\\President",

	initComponent : function(){
		
		Ext.apply(this, {
			titleField:'lastname',
			goDialogId:'president',
			title:GO.presidents.lang.president,
			width: 300,
			height: 280,
			formControllerUrl: 'presidents/president'
		});
		
		GO.presidents.PresidentDialog.superclass.initComponent.call(this);	
	},
	
	buildForm : function () {

		this.propertiesPanel = new Ext.Panel({
			title:GO.lang.strProperties,			
			cls:'go-form-panel',
			layout:'form',
			items:[{
				xtype: 'textfield',
				name: 'firstname',
				width:300,
				anchor: '100%',
				maxLength: 100,
				allowBlank:false,
				fieldLabel: GO.presidents.lang.firstname
			},
			{
				xtype: 'textfield',
				name: 'lastname',
				fieldLabel: GO.presidents.lang.lastname,
				anchor: '100%'
			},
			{
				xtype:'combo',
				fieldLabel: GO.presidents.lang.party_id,
				hiddenName:'party_id',
				anchor:'100%',
				emptyText:GO.lang.strPleaseSelect,
				store: new GO.data.JsonStore({
					url: GO.url('presidents/party/store'),
					baseParams: {
						permissionLevel:GO.permissionLevels.write
					},	
					fields: ['id', 'name']	
				}),
				valueField:'id',
				displayField:'name',
				triggerAction: 'all',
				editable: true,
				forceSelection: true,
				allowBlank: false
			},
			{
				xtype: 'datefield',
				name: 'tookoffice',
				fieldLabel: GO.presidents.lang.tookoffice,
				allowBlank: false,
				anchor: '100%'
			},
			{
				xtype: 'datefield',
				name: 'leftoffice',
				fieldLabel: GO.presidents.lang.leftoffice,
				//format: GO.settings['date_format'],			    
				allowBlank: false,
				anchor: '100%'
			},
			{
				xtype: 'numberfield',
				name: 'income',
				value: GO.util.numberFormat(0),
				fieldLabel: GO.presidents.lang.income,   
				allowBlank: false,
				anchor: '100%'
			},
			new GO.form.SelectLink({
				anchor:'100%'
			})
			]				
		});

		this.addPanel(this.propertiesPanel);
	}
	
});