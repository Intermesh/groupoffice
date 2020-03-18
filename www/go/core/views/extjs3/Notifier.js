go.Notifier = {
	messageCt: Ext.DomHelper.insertFirst(document.body, {id: 'message-ct'}, true),
	/**
	 * @param msg {title, description, iconCls, time (ms)}
	 */
	msg: function (msg) {

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
	toast: function (message) {

	},
	remove: function(msg) {
		if(msg.destroying || msg.isDestroyed) {
			return;
		}
		msg.destroying = true;
		msg.el.animate({opacity: {to: 0}}, 0.2, function () {
			msg.destroy();
		});
	}
};
