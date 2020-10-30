go.Notifier = {

	messageCt: Ext.DomHelper.insertFirst(document.body, {id: 'message-ct'}, true),
	showStatusBar: false,
	notificationArea: null,
	init: function(notificationArea) {

		var me = this;

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
	},

	_messages: {},
	_icons: {},
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
		for(var icon in this._icons[key]) {
			if(!icon.hidden) return;
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

	/**
	 * Put a message into the notification area (fallback to notify())
	 * @param msg {title, description, iconCls, removeAfter (ms)}
	 */
	msg: function (msg, key) {

		if(!this.notifications) {
			this.notify({title:msg.title, text: msg.description});
			return; // not initializes (happens after login)
		}

		if(msg.sound) {
			this.playSound(msg.sound, key);
		}
		if(msg.handler) {
			msg.listeners = msg.listeners || {};
			msg.listeners.afterrender = function(p){
				p.el.on('click', function() {
					//if(GO.util.isMobileOrTablet()) {
						go.Notifier.hideNotifications();
					//}
					msg.handler();
				});
			}
		}

		//msg.renderTo = this.messageCt;
		msg.html = msg.description || msg.html; // backward compat
		var me = this;
		msg.tools = [{
			id: 'close',
			handler: function (e, toolEl, panel) {
				if(key) {
					delete me._messages[key];
				}
				me.remove(panel);
			},
			hidden: msg.persistent
		}];
		if(key) {
			msg.itemId = key;
		}

		//makes it fly out
		// msg.renderTo = this.messageCt;

		var msgPanel = new Ext.Panel(msg);


		this.notifications.add(msgPanel);
		this.notifications.doLayout();



		if(msg.removeAfter) {
			setTimeout(function () {
				if(msg.itemId) {
					delete me._messages[msg.itemId];
				}
				me.remove(msgPanel);
			}, msg.removeAfter);
		}
		msgPanel.setPersistent = function(bool) {

			if(!msgPanel.rendered) {
				msgPanel.on("render", function() {
					msgPanel.setPersistent(bool);
				}, this, {single: true});
				return msgPanel;
			}

			msgPanel.getTool('close').setVisible(!bool);
			return msgPanel;
		};

		if(msg.itemId) {
			if(this._messages[msg.itemId]) {
				this._messages[msg.itemId].destroy();
			}
			this._messages[msg.itemId] = msgPanel;
		}

		//this.showNotifications();
		
		return msgPanel;
	},

	notificationsVisible : function() {
		return this.notificationArea.ownerCt.getLayout()['east'].isSlid;
	},

	showNotifications : function() {
		this.notificationArea.ownerCt.getLayout()['east'].slideOut();
	},

	hideNotifications : function() {
		this.notificationArea.ownerCt.getLayout()['east'].slideIn();
	},
	/**
	 * For (less obstructive) popup messages from the bottom
	 * @param message
	 */
	toast: function (message) {
		// not implemented: discuss first
	},
	remove: function(msg) {
		if(msg.destroying || msg.isDestroyed) {
			return;
		}
		if(msg.itemId) {
			delete this._messages[msg.itemId];
		}
		msg.destroying = true;
		if(!msg.el) {
			msg.destroy();
		} else {
			msg.el.animate({opacity: {to: 0}}, 0.2, function () {
				msg.destroy();
			});
		}
	},
	/**
	 * A more obstructive flyout message
	 * Will use a desktop notification if permission if granted
	 * {title,body,icon}
	 * @param storeData
	 */
	notify: function(msg){
		if (!("Notification" in window)) {
			return;
		}

		var title = msg.title || t("Reminders");
		var icon = msg.icon || 'views/Extjs3/themes/Paper/img/notify/reminder.png';
		icon = window.location.pathname + icon;

		msg.text = msg.text || msg.description;

		try {
			switch(Notification.permission) {
				case 'denied':
					this.flyout(msg);
					break;

				case 'default':
					var me = this;
					Notification.requestPermission(function (permission) { // ask first
						if (permission === "granted") {
							new Notification(title, {body: msg.text, icon: icon});
						} else {
							me.flyout(msg);
						}
					});
					break;
				case 'granted':
					new Notification(title,{body: msg.text, icon: icon});
			}
		} catch (e) {
			/* ignore failure on mobiles */
			this.flyout(msg);
		}

	},

	/**
	 * Show top-right on-page notification banner
	 * @param msg an Ext.Panel config +
	 *   optional "time" in ms to auto remove
	 *   optional "persistent" boolean to make it none closable on click
	 * @returns The created Ext.Panel
	 */
	flyout: function(msg) {
		msg.renderTo = this.messageCt;
		msg.html = msg.description || msg.html; // backward compat
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

		document.getElementById("sound").innerHTML = '<audio autoplay="autoplay">'+
			'<source src="' + path + '.mp3" type="audio/mpeg">'+
			'<source src="' + path + '.ogg" type="audio/ogg">'+
			'<embed hidden="true" autostart="true" loop="false" src="' + path +'.mp3">'+
		'</audio>';
	}
};