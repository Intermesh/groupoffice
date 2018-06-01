/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: IframeComponent.js 15954 2013-10-17 12:04:36Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
 
GO.panel.IFrameComponent = Ext.extend(Ext.BoxComponent, {
     onRender : function(ct, position){
     	
     		if(!this.name)
     		{
     			this.name='';
     		}
     		
       this.el = ct.createChild({tag: 'iframe', id: this.id, frameBorder: 0, src: this.url, name:this.name});
     },
		 
		 reloadOnShow : false,
		 
		 onShow : function(){
			 
			 if(this.reloadOnShow){
				this.getEl().dom.contentWindow.location=this.url;
			 }
			 
			 GO.panel.IFrameComponent.superclass.onShow.call(this);
		 },
     
     setUrl : function(url){
     	this.el.set({src:url});
     }
});
