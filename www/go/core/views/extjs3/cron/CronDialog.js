go.cron.CronDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'cronjob',
			title:t("Job", "cron"),
			formControllerUrl: 'core/cron',
			updateAction : 'update',
			createAction : 'create',
			height:dp(600),
			autoScroll: true,
			width:dp(500),
			tools: [{
				id:'help',
				qtip: t("Please use one of these formats (eg. hour, no spaces):", "cron")+
					'<table>'+
					'<tr><td>*</td><td>'+t("(all)", "cron")+'</td></tr>'+
					'<tr><td>1</td><td>'+t("(only the first)", "cron")+'</td></tr>'+
					'<tr><td>1-5</td><td>'+t("(All between 1 and 5)", "cron")+'</td></tr>'+
					'<tr><td>0-23/2</td><td>'+t("(Every 2nd between 0 and 23)", "cron")+'</td></tr>'+
					'<tr><td>1,2,3,13,22</td><td>'+t("(Only on the given numbers)", "cron")+'</td></tr>'+
					'<tr><td>0-4,8-12</td><td>'+t("(Between 0 and 4 and between 8 and 12)", "cron")+'</td></tr>'+
					'<table>'
			}],
			select: false
		});
		
		go.cron.CronDialog.superclass.initComponent.call(this);	
	},
	 	
	buildForm : function () {
			
		this.usersPanel = new GO.base.model.multiselect.panel({
      title:t("Users", "cron"),	
      url:'cron/cronUser',
      columns:[{header: t("user", "cron"), dataIndex: 'name', sortable: true}],
      fields:['id','name'],
      model_id:this.remoteModelId
    });
		
		this.groupsPanel = new GO.base.model.multiselect.panel({
      title:t("Groups", "cron"),	
      url:'cron/cronGroup',
      columns:[{header: t("Group", "cron"), dataIndex: 'name', sortable: true}],
      fields:['id','name'],
      model_id:this.remoteModelId
    });
			
		this.nameField = new Ext.form.TextField({
			name: 'name',
			width:300,
			anchor: '100%',
			maxLength: 100,
			allowBlank:false,
			fieldLabel: t("Name", "cron")
		});
		
		this.minutesField = new Ext.form.TextField({
			name: 'minutes',
			width:300,
			anchor: '99%',
			maxLength: 100,
			allowBlank:false,
			fieldLabel: t("Minutes", "cron") +' '+ t("(0-59)", "cron")
		});
		
		this.hoursField = new Ext.form.TextField({
			name: 'hours',
			width:300,
			anchor: '99%',
			maxLength: 100,
			allowBlank:false,
			fieldLabel: t("Hours", "cron") +' '+ t("(0-23)", "cron")
		});
		
		this.monthDaysField = new Ext.form.TextField({
			name: 'monthdays',
			width:300,
			anchor: '99%',
			maxLength: 100,
			allowBlank:false,
			fieldLabel: t("Month days", "cron") +' '+ t("(1-31)", "cron")
		});
		
		this.monthsField = new Ext.form.TextField({
			name: 'months',
			width:300,
			anchor: '99%',
			maxLength: 100,
			allowBlank:false,
			fieldLabel: t("Months", "cron") +' '+ t("(1-12)", "cron")
		});
		
		this.weekdaysField = new Ext.form.TextField({
			name: 'weekdays',
			width:300,
			anchor: '99%',
			maxLength: 100,
			allowBlank:false,
			fieldLabel: t("Week days", "cron") +' '+ t("(0-6)", "cron")
		});
		
//		this.yearsField = new Ext.form.TextField({
//			name: 'years',
//			width:300,
//			anchor: '99%',
//			maxLength: 100,
//			allowBlank:false,
//			fieldLabel: t("Years", "cron") +' '+ t("(2013-2015)", "cron")
//		});
//		
		this.activeCheckbox = new Ext.ux.form.XCheckbox({
			name: 'active',
			width:300,
			anchor: '100%',
			maxLength: 100,
//			allowBlank:false,
			boxLabel: t("Enabled", "cron"),
			hideLabel: true
		});
		
		this.runOnceCheckbox = new Ext.ux.form.XCheckbox({
			name: 'runonce',
			width:300,
			anchor: '100%',
			maxLength: 100,
//			allowBlank:false,
			boxLabel: t("Run only once", "cron"),
			hideLabel: true
		});
		
		this.jobCombo = new GO.form.ComboBox({
			hiddenName: 'job',
			fieldLabel: t("Job", "cron"),
			store: go.cron.jobStore,
			valueField:'class',
			displayField:'name',
			mode:'remote',
			anchor: '100%%',
			allowBlank: false,
			triggerAction: 'all',
			reloadOnExpand : true
		});
		
		this.jobCombo.on('select',function(combo, record, index ){
			if(record.data.selection && this.remoteModelId > 0){
				this.select = true;
				this.usersPanel.setDisabled(false);
				this.groupsPanel.setDisabled(false);
			}else{
				this.select = false;
				this.usersPanel.setDisabled(true);
				this.groupsPanel.setDisabled(true);
			}
		},this);
		
		
		this.timeFieldSet = new Ext.form.FieldSet({
			title: t("Time", "cron"),
			labelWidth: 140,
			autoHeight: true,
			border: true,
			collapsed: false,
			items:[
				this.minutesField,
				this.hoursField,
				this.monthDaysField,
				this.monthsField,
				this.weekdaysField
//				,
//				this.yearsField
			]
		});
			
		this.propertiesPanel = new Ext.Panel({
			title:t("Properties"),			
			cls:'go-form-panel',
			layout:'form',
			labelWidth: 90,
			items:[
				this.nameField,
				this.jobCombo,
				this.timeFieldSet,
				this.activeCheckbox,
				this.runOnceCheckbox
      ]				
		});

		this.parameterPanel = new go.cron.ParametersPanel();
	
    this.addPanel(this.propertiesPanel);
		this.addPanel(this.usersPanel);
		this.addPanel(this.groupsPanel);
		this.addPanel(this.parameterPanel);
	},
	
	afterLoad : function(remoteModelId, config, action){
		this.usersPanel.setModelId(remoteModelId);
    this.groupsPanel.setModelId(remoteModelId);

		this.select = action.result.data.select || false;
		this.usersPanel.setDisabled(!this.select);
		this.groupsPanel.setDisabled(!this.select);

		this.parameterPanel.buildForm(action.result.data.paramsToSet);
		
	},
  afterSubmit: function(action){
    var noUserSelection = this.select; //this.usersPanel.disabled;
		var comboValue = this.jobCombo.getValue();
		var store = this.jobCombo.getStore();
		if(!store.loaded){
			store.load(function() {
				var record = store.getById(comboValue);
				noUserSelection = record.data.selection;
			});
		} else {
			var record = store.getById(comboValue);
			noUserSelection = record.data.selection;
		}
		
		this.usersPanel.setModelId(action.result.id);
		this.groupsPanel.setModelId(action.result.id);

		if(noUserSelection){
			this.select = true;
			this.usersPanel.setDisabled(false);
			this.groupsPanel.setDisabled(false);
		} else {
			this.select = false;
			this.usersPanel.setDisabled(true);
			this.groupsPanel.setDisabled(true);
		}
  }
});
