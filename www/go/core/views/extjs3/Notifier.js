(function() {
	//User interaction is required for sounds to autoplay
	function setInteracted(e) {

		if(e instanceof KeyboardEvent) {
			var keyCode = e.which ? e.which : e.keyCode;

			if(keyCode == 18 || keyCode == 91 || keyCode == 17|| keyCode == 16|| keyCode == 20) {
				return;
			}
		}
		go.Notifier.userInteracted();

		window.removeEventListener("scroll", setInteracted);
		window.removeEventListener("click", setInteracted);
		window.removeEventListener("keydown", setInteracted);
	}

	window.addEventListener("scroll", setInteracted);
	window.addEventListener("click", setInteracted);
	window.addEventListener("keydown", setInteracted);

	var Notifier = Ext.extend(Ext.util.Observable, {

		constructor: function() {


			this.supr().constructor.call(this);

			this.addEvents({
				"beforeshow" : true
			});
		},

		_userInteracted: false,
		messageCt: null,
		showStatusBar: false,
		notificationArea: null,
		init: function(notificationArea) {

			var me = this;

			this.messageCt = Ext.DomHelper.insertFirst(document.body, {id: 'message-ct'}, true);

			this.notificationArea = notificationArea;

			this.addStatusIcon('upload', 'ic-file-upload');
			this.statusBar = new Ext.Container({applyTo: "status-bar", hidden:!this.showStatusBar});
			for(var key in this._icons) {
				this.statusBar.add(this._icons[key]);
			}
			this.statusBar.doLayout();
			this.statusBar.el.on('click', function(e) {
				if(me.notificationsVisible()) {
					return; // it will hide on any click outside the panel
				}
				me.showNotifications();
				e.stopPropagation();

			}, this);

			this.notifications = new Ext.Container({cls: 'notifications'});
			this.notificationArea.insert(0,this.notifications);

			this.notifiedAlerts = {};
		},

		_messages: {},
		_icons: {},

		userInteracted : function() {
			this._userInteracted = true;
		},

		toggleIcon: function(key, visible) {
			if (this._icons[key]) {
				this._icons[key].setVisible(visible);
			}
			if(visible) {
				if(this.statusBar) {
					this.statusBar.show();
				} else {
					this.showStatusBar = true;
				}
				return;
			}
			for(var icon in this._icons) {
				if(!this._icons[icon].hidden) return;
			}
			this.statusBar.hide();
		},
		addStatusIcon : function(key, iconCls) {
			this._icons[key] = new Ext.BoxComponent({
				hidden:true,
				autoEl: 'i',
				cls: 'icon '+iconCls
			});
			if(this.statusBar) {
				this.statusBar.add(this._icons[key]);
				this.statusBar.doLayout();
			}
		},

		msgByKey: function(key) {
			return this._messages[key] || null;
		},

		updateStatusIcons: function() {
			let active = [];
			for(let id in this._messages) {
				if(this._messages[id].statusIcon && active.indexOf(this._messages[id].statusIcon) == -1) {
					active.push(this._messages[id].statusIcon);
				}
			}

			for(let key in  this._icons) {
				this.toggleIcon(key, active.indexOf(key) > -1);
			}
		},

		/**
		 * Put a message into the notification area
		 *
		 * @param msg {title, description, iconCls, notificationBody}
		 */
		msg: function (msg, itemId) {

			if(!this.notifications) {
				this.flyout({title:msg.title, description: msg.description});
				return; // not initializes (happens after login)
			}

			if(itemId) {
				msg.itemId = itemId;
			}
			if(!msg.itemId) {
				msg.itemId = 'notify-' + Ext.id();
			}

			if(msg.sound) {
				this.playSound(msg.sound, msg.itemId);
			}
			if(msg.handler) {
				msg.listeners = msg.listeners || {};
				msg.listeners.afterrender = function(p){

					onClick = () => {
						//if(GO.util.isMobileOrTablet()) {
						go.Notifier.hideNotifications();
						//}
						msg.handler();
					};
					p.body.on('click', onClick);
					p.header.on('click', onClick);
				}
			}

			//msg.renderTo = this.messageCt;
			msg.html = msg.description || msg.html || msg.body; // backward compat

			var msgPanel = Ext.create(msg, "panel");

			if(!msgPanel.tools || !msgPanel.getTool('close')) {
				msgPanel.addTool({
					id: "close",
					tooltip: t("Close"),
					visible: !msg.persistent,
					handler: function (e, toolEl, panel, tc) {
						panel.destroy();
					}
				});
			}

			if(this._messages[msg.itemId]) {
				this._messages[msg.itemId].destroy();
			}
			this._messages[msg.itemId] = msgPanel;

			msgPanel.on("destroy", this.onMsgDestroy, this);

			msgPanel.setPersistent = function(bool) {

				msgPanel.persistent = bool;

				if(!msgPanel.rendered) {
					msgPanel.on("render", function() {
						msgPanel.setPersistent(bool);
					}, this, {single: true});
					return msgPanel;
				}

				msgPanel.getTool('close').setVisible(!bool);
				return msgPanel;
			};

			if(msgPanel.notificationBody && !this.notifiedAlerts[msgPanel.itemId]) {
				//create desktop notification
				go.Notifier.notify({
						body: msgPanel.notificationBody,
						title: msgPanel.title,
						tag: msgPanel.itemId,
						onclose: function (e) {
							// close group-office notification too.
							msgPanel.destroy();
						}
					}
				).then((notification) => {
					// set Desktop.Notification on Group-Office notification so we can close it when closing it in GO.
					msgPanel.notification = notification
				}).catch((e) => {
					console.warn("Notification failed: " + e);
				});

				this.notifiedAlerts[msgPanel.itemId] = true;
			}

			if(this.fireEvent("beforenotify", this, msgPanel) === false) {
				return false;
			}

			this.notifications.add(msgPanel);
			this.notifications.doLayout();

			this.showNotifications();

			this.updateStatusIcons();

			return msgPanel;
		},

		onMsgDestroy: function(msg) {

			//close the desktop notification if set
			if(msg.notification){
				msg.notification.close();
			}

			delete this._messages[msg.itemId];
			this.updateStatusIcons();
		},

		notificationsVisible : function() {
			return this.notificationArea.ownerCt.getLayout()['east'].isSlid;
		},

		showNotifications : function() {

			//added here to make sure it comes last
			if(!this.notificationArea.tools['close']) {

				this.notificationArea.addTool({
					id:'dismiss',
					qtip: t('Dismiss all'),
					handler: function() {
						Ext.MessageBox.confirm(t("Confirm"), t('Are you sure you want to dismiss all notifications?'), function(btn){
							if(btn=='yes') {
								go.Notifier.removeAll();
							}
						}, this);
					},
					scope:this
				});

				this.notificationArea.addTool({
					id: "close",
					tooltip: t("Close"),
					handler: function () {
						go.Notifier.hideNotifications();
					}
				});
			}

			this.notificationArea.ownerCt.getLayout()['east'].slideOut();
			this.notificationArea.doLayout(true);
		},

		hasMessages: function() {
			for(var id in this._messages) {
				return true;
			}
			return false;
		},

		hideNotifications : function() {
			this.notificationArea.ownerCt.getLayout()['east'].slideIn();
			// this.notificationArea.doLayout();
		},
		/**
		 * For (less obstructive) popup messages from the bottom
		 * @param message
		 */
		toast: function (message) {
			// not implemented: discuss first
		},
		remove: function(msg) {
			if(msg.itemId) {
				delete this._messages[msg.itemId];
			}
			msg.destroy();
		},

		getById : function(msgId) {
			if(!this._messages[msgId]) {
				return false;
			}
			return this._messages[msgId];
		},

		removeById(msgId) {
			if(!this._messages[msgId]) {
				return false;
			}
			this._messages[msgId].destroy();
			delete this._messages[msgId];
		},

		removeAll : function() {
			for(var id in this._messages) {
				if(!this._messages[id].persistent) {
					this.remove(this._messages[id]);
				}
			}
		},
		/**
		 * Create a desktop notification if permitted
		 *
		 * {title,description,icon, tag}
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/API/notification
		 * @param storeData
		 * @return Promise<Notification>
		 */
		notify: function(msg){
			if (!("Notification" in window)) {
				return Promise.reject("Notifications not supported");
			}


			if(!window.isSecureContext) {
				return Promise.reject("Notifications only work in secure context");
			}

			var title = msg.title || t("Reminders");

			msg.icon = msg.icon || GO.settings.config.full_url + 'views/Extjs3/themes/Paper/img/notify/reminder.png';
			msg.body = msg.description || msg.body;
			//delete msg.title;

			try {
				switch(Notification.permission) {
					case 'denied':
						return Promise.reject("Notifications are denied");
						break;

					case 'default':
						return this.requestNotifyPermission().then((permission) => {
							return this.notify(msg);
						});
						break;
					case 'granted':
						var notification = new Notification(title,msg);
				}
			} catch (e) {
				/* ignore failure on mobiles */
				//this.flyout(msg);
			}

			if(notification && msg.onclose) {
				notification.onclose = msg.onclose;
			}

			return Promise.resolve(notification);

		},

		notifyRequest: null,

		/**
		 *
		 * @returns {Promise<NotificationPermission>}
		 */
		requestNotifyPermission : function() {

			if(!this.notifyRequest) {
				this.notifyRequest = new Promise((resolve, reject) => {

					Ext.MessageBox.alert(t("Setup notifications"), t("Please choose if you'd like to allow desktop notifications by Group-Office after pressing 'Ok'."), (btn) => {
						Notification.requestPermission((permission) => {
							resolve(permission);
						});
					})
				});
			}
			return this.notifyRequest;
		},

		/**
		 * Show top-right on-page notification banner
		 * @param msg an Ext.Panel config +
		 *   optional "time" in ms to auto remove
		 *   optional "persistent" boolean to make it none closable on click
		 * @returns The created Ext.Panel
		 */
		flyout: function(msg) {
			if(!this.messageCt) {
				this.messageCt = Ext.DomHelper.insertFirst(document.body, {id: 'message-ct'}, true);
			}
			msg.renderTo = this.messageCt;
			msg.html = msg.description; // backward compat
			var msgCtr = new Ext.Panel(msg);

			var me = this;
			if (msg.time) {
				setTimeout(function () {
					me.remove(msgCtr);
				}, msg.time);
			}
			if(!msg.persistent) {
				msgCtr.el.on('click', function () {
					me.remove(msgCtr);
				});
			}

			return msgCtr;
		},

		playSound: function(filename, type){
			if(!GO.util.empty(go.User.mute_sound) ||
				(type === 'email' && go.User.mute_new_mail_sound) ||
				(type === 'reminders' && go.User.mute_reminder_sound)) {
				return;
			}

			var path = 'views/Extjs3/themes/Paper/sounds/'+(filename || 'dialog-question');

			var audio = new Audio(path + ".mp3");



			if(this._userInteracted) {
				audio.play()
					.catch((e) => {
						console.warn("Could not play notifier sound: " + e.message);
					});
			}else
			{
				this.userInteracted = this.userInteracted.createSequence(function() {

					audio.play()
						.catch((e) => {
							console.warn("Could not play notifier sound: " + e.message);
						});

				});
			}

		}
	});

	go.Notifier = new Notifier();

})();