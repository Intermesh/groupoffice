GO.ErrorDialog = function(config) {
	config = config || {};

	Ext.apply(config, {
		width: dp(872),
		closeAction : 'hide',
		plain : true,
		height: dp(424),
		layout: 'fit',
		border : false,
		closable : true,
		title : t("Error"),
		modal : true, 
		items : [
		this.messagePanel = new Ext.Panel({							
			cls : 'go-error-dialog',		
			autoScroll:true,
			html : ''
		})]
	});

	GO.ErrorDialog.superclass.constructor.call(this, config);
}

Ext.extend(GO.ErrorDialog, GO.Window, {

	show : function(error, title) {

		console.trace('errordialog');
		
		if(!title)
			title = t("Error");
		
		var now = new Date();
		
		title += ' - '+now.format("Y-m-d G:i");
		
		this.setTitle(title);

		if (!this.rendered)
			this.render(Ext.getBody());

		//				this.detailPanel.collapse();
				
		if(!error)
			error = "No error message given";
		
		
		this.setHeight(130);
		
//		if(details)
//			error += "<br /><br />"+details;

		this.messagePanel.body.update(error);
				
		//				if(GO.util.empty(details))
		//				{
		//					this.detailPanel.hide();
		//				}else
		//				{
		//					this.detailPanel.show();
		//					this.detailPanel.body.update('<pre>'+details+'</pre>');
		//				}

		GO.ErrorDialog.superclass.show.call(this);
		
		if(this.messagePanel.body.isScrollable()){
			var newHeight = this.messagePanel.body.dom.scrollHeight+80;
							
			if(newHeight>130){
				this.setHeight(newHeight);
				this.autoSize();	
			}
		}
		this.center();
	}
});
GO.errorDialog = new GO.ErrorDialog();
