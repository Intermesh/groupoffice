GO.Checker = Ext.extend(Ext.util.Observable, {
	lastCount : 0,
	params:{
		requests: {
			reminders: {r:"reminder/store"},
			loginstatus:{r:"core/auth/checkclient"}
		}
	},

	initComponent: function() {
		this.addEvents({'alert' : true});


		GO.Checker.superclass.initComponent.call(this);
	},

	callbacks : {},
	
	init : function(){

		const task = Ext.TaskMgr.start({
			run: this.checkForNotifications,
			scope:this,
			interval: GO.settings.config.checker_interval*1000
		});
		this.initReminders();

		this.notifiedReminders = {};

		window.addEventListener('offline', () => {
			console.log("Stopping checker because we're offline")
			Ext.TaskMgr.stop(task);
		});

		window.addEventListener('online', () => {
			console.log("Starting checker because we're online")
			Ext.TaskMgr.start(task);
		})
	},

	initReminders: function() {


		var checkerSnoozeTimes = [
			[300,'5 '+t("Minutes")],
			[600, '10 '+t("Minutes")],
			[1200, '20 '+t("Minutes")],
			[1800, '30 '+t("Minutes")],
			[3600, '1 '+t("Hour")],
			[7200, '2 '+t("Hours")],
			[10800, '3 '+t("Hours")],
			[14400, '4 '+t("Hours")],
			[86400, '1 '+t("Day")],
			[2*86400, '2 '+t("Days")],
			[3*86400, '3 '+t("Days")],
			[4*86400, '4 '+t("Days")],
			[5*86400, '5 '+t("Days")],
			[6*86400, '6 '+t("Days")],
			[7*86400, '7 '+t("Days")]
		];

		this.reminderStore = new Ext.data.GroupingStore({
			reader: new Ext.data.JsonReader({
				totalProperty: "count",
				root: "results",
				fields:['id','name','model_id','model_name','model_type_id',
					'type','local_time', 'iconCls','time','snooze_time','text', 'entity']
			}),
			groupField: 'type',
			remoteSort: true,
			remoteGroup: true
		});


		this.reminderStore.on('load',function(store, records) {

			// The store is loaded on every check, so only notify the reminders that aren't shown yet
			const newRecords = records.filter(record => !this.notifiedReminders[record.data.id]);

			newRecords.forEach(function(record) {

				const id = record.data.id, notifier = window.groupofficeCore.main.notifier;
				this.notifiedReminders[id] = true;

				const notification = {
					tag:'reminder_'+id,
					title:record.data.type,text:  record.data.local_time + ": " + record.data.name,
					icon: {name:'reminder',color:'orange',link:'views/Extjs3/themes/Paper/img/notify/reminder.png'},
					category:'alarm',variant:'info',
					actions:{
						primary: {text:t("Snooze"), icon: 'snooze', run: (button) => {
							// a menu is removed when it closes, so create a new one each time
							window.GOUI.menu({isDropdown: true}, ...checkerSnoozeTimes.map(([seconds, text]) => window.GOUI.btn({
								text,
								handler: () => {
									this.doTask("snooze_reminders", seconds, [id]);
									// silent, because closing dismisses the reminder
									notifier.close(notification, true);
								}
							}))).showFor(button.el);
						}},
						secondary: {text:t("Dismiss"), icon: 'delete', run: () => notifier.close(notification)}
					},
					onClose: () => { this.doTask("dismiss_reminders", 0, [id]); }
				};

				window.GOUI.Notifier.notify(notification);

			}, this);

			if(newRecords.length) {
				window.groupofficeCore.main.notifier.playSound('reminder');
			}

		},this);


	},

	doTask : function(task, seconds, reminderIds) {
		Ext.Ajax.request({
			url: seconds ? GO.url('reminder/snooze') : GO.url('reminder/dismiss'),
			params: {
				task:task,
				snooze_time: seconds,
				reminders: Ext.encode(reminderIds)
			},
			callback: function(){
				for (var i = 0; i < reminderIds.length;  i++) {
					this.reminderStore.remove(this.reminderStore.getById(reminderIds[i]));
					// notify again when a snoozed reminder returns
					delete this.notifiedReminders[reminderIds[i]];
				}

				GO.checker.lastCount = this.reminderStore.getCount();

			}, scope: this
		});
	},
  
	// See modules/email/EmailClient.js and search for "GO.checker.registerRequest" for an usage example
	registerRequest : function(url, params, callback, scope){
		params.r = url;
		const requestId = Ext.id();

		this.params.requests[requestId] = params;	
		this.callbacks[requestId] = {
			callback: callback,
			scope: scope
		};
	},
  
	// Function to check for reminders in the database
	checkForNotifications : function(){

		Ext.Ajax.request({
			url: GO.url('core/multiRequest'),	  
			params: {
				requests: Ext.encode(this.params.requests)
			},
			success: function(response) {
				var result = Ext.decode(response.responseText);

				for(var id in result){
					switch(id) {
						case 'reminders':
							this.handleReminderResponse(result[id]);
							break;
						case 'loginstatus':
							this.handleLoginstatusResponse(result[id]);
							break;
					}
					if (id!='success' && id!='feedback' && this.callbacks[id]) {
						this.callbacks[id].callback.call(this.callbacks[id].scope, this, result[id]);
					}
				}
			},
			failure: function(response, opts) {
				//silently ignore
				console.error('server-side failure with status code ' + response.status);
				console.error(response);

			},
			scope:this
		});
	},

	handleReminderResponse : function(storeData){

		var hasReminders = (storeData.total && storeData.total > 0);
		var me = this;
		// go.Notifier.toggleIcon('reminder',hasReminders);

		if(!hasReminders) return;

		// notifies new reminders, see initReminders()
		this.reminderStore.loadData(storeData);

	},
	
	handleLoginstatusResponse : function(data){
		// If the login is not valid anymore, then the user is logged out and the browser will be redirected to the login screen
		if(!data.loginValid) {
			go.Router.login();
		}
	}
});