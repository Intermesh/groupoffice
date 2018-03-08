GO.tasks.AddTaskPanel = function(config){

	config = config || {};
	
	this.ntName = new Ext.form.TextField({
		emptyText: GO.tasks.lang.addTask,
		fieldLabel:GO.lang['strName'],
		flex:1

	});

	this.ntTasklist = new GO.form.ComboBox({
		fieldLabel:GO.tasks.lang.tasklist,
		valueField:'id',
		displayField:'name',
		store: new Ext.data.ArrayStore({
			fields: ['id', 'name']
		}),
		mode:'local',
		triggerAction:'all',
		editable:false,
		selectOnFocus:true,
		forceSelection:true
	});
	this.ntTasklist.on('select', function(combo, record){
		this.store.baseParams.tasklist_id = record.data.id;
	})

	this.ntDue = new Ext.form.DateField({
		value: new Date(),
		fieldLabel:GO.tasks.lang.dueDate,
		disabled:true,
		format : GO.settings.date_format
	});

	this.btnNewTask = new Ext.Button({
		text: '<b>+</b>',
		cls:'btn-new-task',
		width:20,
		handler:function()
		{
			this.userTriggered = true;
			this.doBlur();
		},
		disabled:true,
		scope: this
	})

	config = Ext.apply(config, {
		border:false,
		//baseCls:'x-border-layout-ct',
		cls:'ta-add-task-panel',
		height:40,
		items:[{
			anchor:'100%',
			xtype:'compositefield',			
			hideLabel:true,
			items:[this.ntName, this.ntTasklist, this.ntDue, this.btnNewTask]
		}]
	});

	

	GO.tasks.AddTaskPanel.superclass.constructor.call(this, config);
}

Ext.extend(GO.tasks.AddTaskPanel, Ext.FormPanel,{
	populateComboBox : function(records)
	{
		var data = [];
		if(records.length){
			for(var i=0; i<records.length; i++)
			{
				var tasklist = []
				tasklist.push(records[i].data.id);
				tasklist.push(records[i].data.name);

				data.push(tasklist);
			}
		}else
		{
			//data = [[GO.tasks.defaultTasklist.id, GO.tasks.defaultTasklist.name]]
		}

		this.ntTasklist.store.loadData(data);
		var record = this.ntTasklist.store.getAt(0);
		if(record)
			this.ntTasklist.setValue(record.data.id);
	},
	afterRender : function()
	{
		GO.tasks.AddTaskPanel.superclass.afterRender.call(this);


		this.editing = false;
		this.focused = false;
		this.userTriggered = false;

		var handlers = {
			focus: function(){
				this.focused = true;
			},
			blur: function(){
				this.focused = false;
				this.doBlur.defer(250, this);
				if(Ext.isEmpty(this.ntName.getValue())){
					this.btnNewTask.disable();
				}
			},
			specialkey: function(f, e){
				if(e.getKey()==e.ENTER){
					this.userTriggered = true;
					e.stopEvent();
					f.el.blur();
					if(f.triggerBlur){
						f.triggerBlur();
					}
				}
			},
			scope:this
		}
		this.ntName.on(handlers, this);
		this.ntDue.on(handlers, this);

		this.ntName.on('focus', function(){
			this.focused = true;
			this.btnNewTask.enable();
			if(!this.editing){
				this.ntDue.enable();
				this.editing = true;
			}
		}, this);
	},

	syncFields : function(){

		var cm = this.getColumnModel();
		//this.ntSelectLink.setSize(cm.getColumnWidth(1)-204);
		this.ntName.setSize(cm.getColumnWidth(1)-4);
		this.ntDue.setSize(cm.getColumnWidth(2)-4);

	},

	// when a field in the add bar is blurred, this determines
	// whether a new task should be created
	doBlur : function(){
		if(this.userTriggered && this.editing && !this.focused){
			var taskname = this.ntName.getValue();
			var due = this.ntDue.getValue();
			var tasklist_id = this.ntTasklist.getValue();
			// var link = this.ntSelectLink.getValue();
			if(!Ext.isEmpty(taskname) && due){

				Ext.Ajax.request({
					//url: GO.settings.modules.tasks.url+'action.php',
					url:GO.url('tasks/task/submit'),
					params: {
						//task: 'save_task',
						tasklist_id: tasklist_id,
						name: taskname,
					//	link: link,
						start_time: due.format(GO.settings.date_format), //new Date().format(GO.settings.date_format),
						due_time: due.format(GO.settings.date_format)
					},
					callback: function(options, success, response)
					{
						var reponseParams = Ext.decode(response.responseText);
						if(!reponseParams.success)
						{
							GO.errorDialog.show(reponseParams.feedback);
						}else
						{

							GO.tasks.tasksObservable.fireEvent('save', this, this.task_id);
							//this.store.reload();
						}

					},
					scope:this
				});


				this.ntName.setValue('');
				if(this.userTriggered){ // if the entered to add the task, then go to a new add automatically
					this.userTriggered = false;
					this.ntName.focus.defer(100, this.ntName);
				}
			}
			if(due)
			{
				this.ntDue.disable();
				this.btnNewTask.disable();
				this.editing = false;
			}
		}
	}
});
