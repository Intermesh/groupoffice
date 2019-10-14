/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CommentsBrowser.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.comments.CommentsBrowser = function(config){
	
	Ext.apply(this, config);


	this.commentsGrid = new GO.comments.CommentsGrid();

	
	GO.comments.CommentsBrowser.superclass.constructor.call(this, {
   	layout: 'fit',
		modal:false,
		minWidth:300,
		minHeight:300,
		height:500,
		width:700,
		plain:true,
		maximizable:true,
		closeAction:'hide',
		title:t("Browse comments", "comments"),
		items: this.commentsGrid,
		buttons: [			
			{				
				text: t("Close"),
				handler: function(){this.hide();},
				scope: this
			}
		]
    });
    
   this.addEvents({'link' : true});
	 
	 this.on('hide', function(){
		GO.dialog.TabbedFormDialog.prototype.refreshActiveDisplayPanels.call(this);
	}, this);
};

Ext.extend(GO.comments.CommentsBrowser, GO.Window, {
	
	show : function(config)
	{
		if (!GO.util.empty(config['action_date'])) {
			this.commentsGrid.actionDate = config['action_date'];
		} else {
			this.commentsGrid.actionDate = false;
		}
		this.commentsGrid.setLinkId(config.model_id, config.model_name);
		this.commentsGrid.store.load();
		
		GO.comments.CommentsBrowser.superclass.show.call(this);
	}
});
