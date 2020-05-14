go.Window = Ext.extend(Ext.Window, {
	
	constrainHeader : true,	
	resizable : !GO.util.isMobileOrTablet(),
	draggable: !GO.util.isMobileOrTablet(),
	maximized: GO.util.isMobileOrTablet(),
	closeAction: "close",
	border: true,
	animCollapse: false, //htmleditor doesn't work with animCollapse
	
	initComponent : function(){
		
		
		if(this.animTarget) {
			console.warn("animTarget on window causes wierd render issues");
		}
		
		//make sure window fits screen
		if(!this.maximized) {
			if(this.width && this.width > window.innerWidth) {
				this.width = window.innerWidth - dp(32);
			}		
			if(this.height && this.height > window.innerHeight) {
				this.height = window.innerHeight	- dp(32);
			}
		}
		
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
//	// fix for ext close animation
//	close : function(){
//		if(this.fireEvent('beforeclose', this) !== false){
//			 if(this.hidden){
//				  this.doClose();
//			 }else{
//				  this.hide(undefined, this.doClose, this);
//			 }
//		}
//  },
	
	hide : function(animateTarget, cb, scope) {				
		//Fix for ticket #201817154. Unclosable window remained when window was 
		//hidden after submit while being dragged.
		if (this.activeGhost) {
		 this.unghost();
		}
		
		go.Window.superclass.hide.call(this, animateTarget, cb, scope);
	},
		
	autoSize : function(){
		if(!this.maximized){

			var vpW = window.innerWidth;
			var vpH = window.innerHeight;

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
