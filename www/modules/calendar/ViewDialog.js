/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ViewDialog.js 17814 2014-07-22 13:15:18Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.calendar.ViewDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'view',
			titleField:'name',
			title:GO.calendar.lang.view,
			formControllerUrl: 'calendar/view',
			width: 440,
			height: 600
		});
		
		GO.calendar.ViewDialog.superclass.initComponent.call(this);	
	},
	
	setRemoteModelId : function(remoteModelId)
	{
		GO.calendar.ViewDialog.superclass.setRemoteModelId.call(this,remoteModelId);
		this.calendarsGrid.setModelId(remoteModelId, true);
        this.groups.setModelId(remoteModelId);
	},
	
	buildForm : function () {
		
		this.calendarsGrid = new GO.base.model.multiselect.panel({
      title:'',
			region:'center',
      url:'calendar/viewCalendar',
      columns:[
				{header: GO.lang.strTitle, dataIndex: 'name'},
				{header:GO.lang.strUsername,dataIndex: 'username'}
			],
      fields:['id','name','username'],
      model_id:0
    });

		this.propertiesPanel = new Ext.Panel({
			title:GO.lang['strProperties'],			
			//cls:'go-form-panel',
			layout:'border',
			items:[
				new Ext.Panel({
				layout:'form',
				region:'north',
				height:110,
				defaultType: 'textfield',
				defaults: {
					anchor: '100%'
				},
				cls:'go-form-panel',
				waitMsgTarget:true,
				labelWidth: 75,
				border:false,
				items: [
					{
						fieldLabel: GO.lang.strName,
						name: 'name',
						allowBlank:false		
					},this.merge = new Ext.ux.form.XCheckbox({
						name:'merge',
						boxLabel: GO.calendar.lang.merge,
						hideLabel : true
					}),GO.calendar.ownColor = new Ext.ux.form.XCheckbox({
						name:'owncolor',
						boxLabel: GO.calendar.lang.ownColor,
						hideLabel : true,
						disabled : true,
						checked : true
					}),{
						xtype:'plainfield',
						fieldLabel:GO.calendar.lang.directUrl,
						name:'url',
						anchor:'100%'
					}
					]
				}),
				this.calendarsGrid
			]				
		});
		
		this.merge.on('check',function(checkbox,value) {
			if (value)
				GO.calendar.ownColor.setDisabled(false);
			else
				GO.calendar.ownColor.setDisabled(true);
		});

		this.addPanel(this.propertiesPanel);
        
        var groupColumns = [{
			header : GO.lang['strName'],
			dataIndex : 'name',
			menuDisabled:true,
			sortable: true
		}];
        
        this.addPanel (this.groups = new GO.base.model.multiselect.panel({
            title: GO.lang.strSelectGroups,
				anchor: '100% 50%',
				forceLayout:true,
				autoExpandColumn:'name',
				url:'calendar/viewGroup',
				columns: [{
                  header : GO.lang['strName'],
                  dataIndex : 'name',
                  menuDisabled:true,
                  sortable: true
              }],
				/* selectColumns:[{
					header : GO.lang['strName'],
					dataIndex : 'name',
					menuDisabled:true,
					sortable: true
				}], */
				fields:['id','name']
				//model_id: this.view_id //GO.settings.user_id
			})
          );
		this.addPermissionsPanel(new GO.grid.PermissionsPanel());
	}
});