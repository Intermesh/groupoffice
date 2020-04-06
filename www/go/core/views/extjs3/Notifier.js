go.Notifier = {

	messageCt: Ext.DomHelper.insertFirst(document.body, {id: 'message-ct'}, true),

	notificationArea: null,
	init: function(notificationArea) {
		this.notificationArea = notificationArea;

		this.addStatusIcon('upload', 'ic-file-upload');
		this.statusBar = new Ext.Container({applyTo: "status-bar"});
		for(var key in this.icons) {
			this.statusBar.add(this.icons[key]);
		}
		this.statusBar.doLayout();
		this.statusBar.el.on('click', function(){
			notificationArea.toggleCollapse();
		}, this);

		this.notifications = new Ext.Container({cls: 'notifications'});
		this.notificationArea.add(this.notifications);
	},

	icons: {},
	toggleIcon: function(key, visible) {
		if (this.icons[key]) {
			this.icons[key].setVisible(visible);
		}
	},
	addStatusIcon : function(key, iconCls) {
		this.icons[key] = new Ext.BoxComponent({
			hidden:true,
			autoEl: 'i',
			cls: 'icon '+iconCls
		});
		if(this.statusBar) {
			this.statusBar.add(this.icons[key]);
			this.statusBar.doLayout();
		}
	},

	/**
	 * @param msg {title, description, iconCls, time (ms)}
	 */
	msg: function (msg, key) {

		if(msg.sound) {
			this.playSound(msg.sound, key);
		}
		if(msg.handler) {
			msg.listeners = msg.listeners || {};
			msg.listeners.afterrender = function(p){
				p.el.on('click', msg.handler);
			}
		}

		//msg.renderTo = this.messageCt;
		msg.html = msg.description || msg.html; // backward compat
		if (!msg.persistent) {
			msg.tools = [{
				id: 'close', handler: function (e, toolEl, panel) {
					me.remove(panel);
				}
			}];
		}
		var msgCtr = new Ext.Panel(msg);

		this.notifications.add(msgCtr);
		this.notifications.doLayout();
		var me = this;

		if(msg.removeAfter) {
			setTimeout(function () {
				me.remove(msgCtr);
			}, msg.removeAfter);
		}
		msgCtr.setPersistent = function(bool) {
			if(!bool) {
				msgCtr.addTool({
					id: 'close',
					handler: function (e, toolEl, panel) {
						me.remove(panel);
					}
				});
			}
			return msgCtr;
		};
		
		return msgCtr;
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
	 * more obstructive flyout message
	 * will be a desktop notification if permission if granted
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

		try {
			switch(Notification.permission) {
				case 'denied':
					break;
				case 'default':
					Notification.requestPermission(function (permission) { // ask first
						if (permission === "granted") {
							new Notification(title, {body: msg.text, icon: icon});
						}
					});
					break;
				case 'granted':
					new Notification(title,{body: msg.text, icon: icon});
			}
		} catch (e) { /* ignore failure on mobiles */ }

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