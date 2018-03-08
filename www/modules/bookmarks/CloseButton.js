/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: CloseButton.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Twan Verhofstad
 */

GO.bookmarks.CloseButton = function(config){
   
	  config=config||{}
    config.style='cursor:pointer;'; 
	  config.mouseOver=false;
		
		config.listeners={
			render:function(){
				
				var el = this.getEl();
				
				el.on({

					click:function(){
						this.hide();
						this.fireEvent('remove_bookmark', this.record);
					},
						mouseover:function(){
						this.mouseOver=true;
					},
						mouseout:function(){
						this.mouseOver=false;
						this.hide();
					},
					scope:this
				});
			}
		},
    
		config.hideIfNotOver = function(){
			if(!this.mouseOver){
				this.hide();
			}
		}

GO.bookmarks.CloseButton.superclass.constructor.call(this,config);
this.addEvents({'remove_bookmark' : true});
}

Ext.extend(GO.bookmarks.CloseButton, Ext.BoxComponent,{

});
