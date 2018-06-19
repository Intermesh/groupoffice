go.notifier = {
	messageCt: Ext.DomHelper.insertFirst(document.body, {id: 'message-ct'}, true),
	/**
	 * message {title, description, icon, type, showTime, buttons}
	 */
	msg: function (msg) {
	

		html = "";

		if (msg.iconCls) {
			html += '<i class="icon ' + msg.iconCls + '"></i>';
		}

		if (msg.title) {
			html += '<h4>' + msg.title + '</h4>';
		}


		var msgCtr = new Ext.Container({
			title: !GO.util.empty(msg.title) ? msg.title : "",
			html: html + '<p>' + msg.description + '</p>',
			renderTo: this.messageCt
		});

		var me = this;
		if (msg.time) {
			setTimeout(function () {
				me.remove(msgCtr);
			}, msg.time);
		} else {
			msgCtr.el.on('click', function () {
				me.remove(msgCtr);
			});
		}
		
		return msgCtr;
	},
	toast: function (message) {

	},
	remove: function(msg) {
		msg.el.animate({opacity: {to: 0}}, 0.2, function () {
			msg.destroy();
		});
	}
};
