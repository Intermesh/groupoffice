go.notifier = {
	messageCt: Ext.DomHelper.insertFirst(document.body, {id:'message-ct'}, true),
	/**
	 * message {title, description, icon, type, showTime, buttons}
	 */
	msg: function(msg) {
		function remove(ct) {
			ct.el.animate({opacity:{to:0}},0.2,function(){
				ct.destroy();
			});
		}
		
		var msgCtr = new Ext.Container({
			title:msg.title, 
			html:'<i class="icon '+msg.iconCls+'"></i><h4>'+msg.title+'</h4><p>'+msg.description+'</p>', 
			renderTo: this.messageCt
		});
		
		if(msg.time) {
			setTimeout(function(){ remove(msgCtr); }, msg.time);
		} else {
			msgCtr.el.on('click', function() {remove(msgCtr); });
		}
	},
	toast: function(message) {
		
	}
};