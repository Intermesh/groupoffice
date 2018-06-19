go.Window = Ext.extend(Ext.Window, {
	
	constrainHeader : true,
	
//	showAnimDuration: 0.16,
//	hideAnimDuration: 0.16,
	
	
	initComponent : function(){
		
		go.Window.superclass.initComponent.call(this);
		
		this.on('move', function(){			
			//to fix combobox autocomplete failure after move or hide window			
			document.activeElement.blur();
		});
		
		this.on('show', this.autoSize, this);
	},	
	
	// private, we don't want to store the window position remote because
	//screens may differ later.
	getState : function(){
		var s = go.Window.superclass.getState.call(this);

		delete s.x;
		delete s.y;

		return s;

	},
	// fix for ext close animation
	close : function(){
		if(this.fireEvent('beforeclose', this) !== false){
			 if(this.hidden){
				  this.doClose();
			 }else{
				  this.hide(undefined, this.doClose, this);
			 }
		}
  },
		
	autoSize : function(){
		if(!this.maximized && GO.viewport){

			var vpH=GO.viewport.getEl().getHeight();
			var vpW=GO.viewport.getEl().getWidth();

			if (this.getHeight() > vpH){
				this.setHeight(vpH * .9);
			}
			if(this.getWidth() > vpW) {
				this.setWidth(vpW * .9);
			}

			var pos = this.getPosition();

			//center window if it's outside the viewport
			if(pos[0]<0 || pos[0]+this.width>vpW || pos[1]<0 || pos[1]+this.height>vpH)
				this.center();
		}
	}
});
