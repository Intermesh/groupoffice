GO.Window = Ext.extend(Ext.Window,{

	border: true,
	constrainHeader : true,
	closeAction:'hide',
//	renderTo: Ext.get('dialogs'), // render before all script tags
//this breaks some functionality that do stuff on render.
	temporaryListeners : [],
	
	resizable : !GO.util.isMobileOrTablet(),
	draggable: !GO.util.isMobileOrTablet(),
	maximized: GO.util.isMobileOrTablet(),
	animCollapse: false, //htmleditor doesn't work with animCollapse
	
	initComponent : function(){

		//make sure window fits screen
		if(this.width && this.width > window.innerWidth) {
			this.width = window.innerWidth - dp(32);
		}		
		if(this.height && this.height > window.innerHeight) {
			this.height = window.innerHeight	- dp(32);

		}
		
		GO.Window.superclass.initComponent.call(this);
		
		this.on('move', function(){			
			//to fix combobox autocomplete failure after move or hide window			
			document.activeElement.blur();
		});
	
	},
	
	addListenerTillHide : function(eventName, fn, scope){
		this.on(eventName, fn, scope);		
		this.temporaryListeners.push({
			eventName:eventName,
			fn:fn,
			scope:scope
		});
	},

	beforeShow : function() {
		GO.Window.superclass.beforeShow.call(this);

		this.autoSize();	
	},

	// private, we don't want to store the window position remote because
	//screens may differ later.
	getState : function(){
		var s = GO.Window.superclass.getState.call(this);

		delete s.x;
		delete s.y;

		//when collapsed the state contains the collapsed height. this.height contains the correct height.
		if(s.collapsed) {
			s.height = this.height;
		}

		return s;

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
	},

	show : function(animateTarget, cb, scope){
	
		GO.dialogListeners.apply(this);
		
		GO.Window.superclass.show.call(this, animateTarget, cb, scope);
	},
	
	removeTempListeners : function() {
		for(var i=0;i<this.temporaryListeners.length;i++)
		{
			this.un(this.temporaryListeners[i].eventName, this.temporaryListeners[i].fn, this.temporaryListeners[i].scope);
		}
		this.temporaryListeners=[];		
		
		if(document.activeElement){
			document.activeElement.blur();
		}
	},
	
	close: function() {
		this.removeTempListeners();		
		GO.Window.superclass.close.call(this);
	},

	hide : function(animateTarget, cb, scope) {		
		this.removeTempListeners();		
		
		//Fix for ticket #201817154. Unclosable window remained when window was 
		//hidden after submit while being dragged.
		if (this.activeGhost) {
		 this.unghost();
		}
		
		GO.Window.superclass.hide.call(this, animateTarget, cb, scope);
	}		
});
