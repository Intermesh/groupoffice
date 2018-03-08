/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: blinkTitle.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.blinkTitle={
	blinkTitle : false,
	originalDocTitle : document.title,
	task : {
		run: function(){
			document.title=document.title!=this.blinkTitle ? this.blinkTitle : this.originalDocTitle;
		},
		interval: 2000
	},
	running : false,

	blink : function(title){
		if(!this.task.scope)
			this.task.scope=this;

		if(GO.util.empty(title)){
			if(this.running){
				Ext.TaskMgr.stop(this.task);
				document.title=this.originalDocTitle;
				this.running = false;
			}
		}else
		{
			this.blinkTitle=title;
			if(!this.running){
				Ext.TaskMgr.start(this.task);
				this.running=true;
			}
		}		
	}
}