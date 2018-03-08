GO.ErrorDialog = function(config) {
	config = config || {};

	Ext.apply(config, {
		width : 550,
		height : 220,
		closeAction : 'hide',
		plain : true,
		border : false,
		closable : true,
		title : GO.lang.strError,
		modal : true, 
		
		layout:'fit',
		items : [
		this.messagePanel = new Ext.Panel({							
			cls : 'go-error-dialog',		
			autoScroll:true,
			html : ''
		})],
		buttons : [{
			text : GO.lang.cmdClose,
			handler : function() {
				this.hide();
			},
			scope : this
		}]
	});

	GO.ErrorDialog.superclass.constructor.call(this, config);
}

Ext.extend(GO.ErrorDialog, GO.Window, {

	show : function(error, title) {
		
		if(!title)
			title = GO.lang.strError;
		
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
