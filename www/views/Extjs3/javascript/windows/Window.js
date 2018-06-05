GO.Window = function(config)
	{
		if(!config)
		{
			config={};
		}
	
		Ext.applyIf(config,{
			keys:[],
			maximizable:true,
			minimizable:true
		});
	
		GO.Window.superclass.constructor.call(this, config);
	};

GO.Window = Ext.extend(Ext.Window,{

	constrainHeader : true,
//	renderTo: Ext.get('dialogs'), // render before all script tags
//this breaks some functionality that do stuff on render.
	temporaryListeners : [],
	
	afterRender : function(){
		
		GO.Window.superclass.afterRender.call(this);
		
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

		return s;

	},
		
	autoSize : function(){
		if(go.Modules.isAvailable("community", "viewport")){

			var vpH=GO.viewport.getEl().getHeight();
			var vpW=GO.viewport.getEl().getWidth();

			if (this.getHeight() > vpH){
				this.setHeight(vpH);
			}
			if(this.getWidth() > vpW) {
				this.setWidth(vpW);
			}

			var pos = this.getPosition();

			//center window if it's outside the viewport
			if(pos[0]<0 || pos[0]+this.width>vpW || pos[1]<0 || pos[1]+this.height>vpH)
				this.center();
		}
	},

	show : function(){		
		GO.dialogListeners.apply(this);
		
		GO.Window.superclass.show.call(this);
	},
	
	removeTempListeners : function() {
		for(var i=0;i<this.temporaryListeners.length;i++)
		{
			this.un(this.temporaryListeners[i].eventName, this.temporaryListeners[i].fn, this.temporaryListeners[i].scope);
		}
		this.temporaryListeners=[];		
		
		document.activeElement.blur();
	},
	
	close: function() {
		this.removeTempListeners();		
		GO.Window.superclass.close.call(this);
	},

	hide : function() {		
		this.removeTempListeners();		
		GO.Window.superclass.hide.call(this);
	}		
});
