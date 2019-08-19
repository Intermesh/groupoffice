go.layout.ResponsiveLayout = Ext.extend(Ext.layout.BorderLayout, {
	type: 'responsive',

	monitorResize: true,

/**
 * Defaults to the first added item. Only used in narrow mode
 */
	activeItem: 0,

	initialized: false,

	/**
	 * Window size when layout switches from  "wide" to "narrow"
	 */
	triggerWidth: 1200,

	wideWidth: null,
	
	/**
	 * If narrow width is supplied the container will be resized to it when switching to narrow mode
	 */
	narrowWidth: null,
	
	/**
	 * Mode is "wide" or "narrow"
	 */
	mode: "wide",
	

	onLayout: function (ct, target) {
		if (!this.initialized)	{			
			ct.addClass('go-layout-responsive');			
			this.initialized = true;

			//make sure activeitem is normalized to a component
			this.activeItem = this.container.getComponent(this.activeItem);
			
			
			ct.items.each(function (i) {
				i.on('beforeshow', this.onBeforeShow, this);
			}, this);
			//make sure border layout is initialized
			go.layout.ResponsiveLayout.superclass.onLayout.call(this, ct, target);
		}
		
		var willBeWide =window.innerWidth > this.triggerWidth;
		var isWide = this.mode == 'wide';
		
		this.setChildWidths(ct, willBeWide != isWide);

		if (willBeWide) {
			this.setWideLayout(ct, target);
		} else
		{
			this.setNarrowLayout(ct, target);
		}
		
		

	},
	
	isNarrow : function() {
		return window.innerWidth <= this.triggerWidth;
	},
	
	getItemWidth : function(i, modeSwitched) {
		if(typeof i.getLayout().isNarrow == "function" && i.getLayout().isNarrow()) {
			return i.initialConfig.narrowWidth || i.initialConfig.width;
		} else
		{			
			if(!i.rendered) {
				return i.initialConfig.width;
			}	
			return modeSwitched ? i.wideWidth : i.getWidth();
		}
			
	},

	setWideLayout: function (ct, target) {
		
		if (this.mode != 'wide') {
			this.mode = 'wide';
			ct.removeClass('go-narrow');
			ct.items.each(function (i) {				
				if (i.hidden) {
					i.show();
				}
				i.stateful = true;
			}, this);		
			ct.stateful = true;
		}		
		
		go.layout.ResponsiveLayout.superclass.onLayout.call(this, ct, target);

	},

	setNarrowLayout: function (ct, target) {
		
//		console.log(ct.getId(), "narrow");
		//turn into cards
		ct.stateful = false;
	
		if (this.mode != 'narrow') {
			this.mode = 'narrow';
			//ct.setWidth(this.narrowWidth);
			ct.addClass('go-narrow');
			
			ct.items.each(function (i) {			
				//disable state
				i.stateful = false;
			//	i.hideMode = "offsets";					
				
				if(!i.hidden) {
					i.hide();
				}
			}, this);			
		}
		
		this.activeItem.show();
		
	},
	
	setChildWidths : function(ct, modeSwitched) {
		ct.items.each(function (i) {			
			if(i.rendered && typeof i.getLayout().isNarrow == "function" && i.getLayout().mode == "wide") {
				i.wideWidth = i.getWidth();
			} else if(!i.wideWidth)
			{
				i.wideWidth = i.initialConfig.width;
			}
			i.setWidth(this.getItemWidth(i, modeSwitched));
		}, this);
	},

	onBeforeShow : function (panel) {
					
		if(this.mode != 'narrow') {
			return true;
		}

		if(this.activeItem && this.activeItem != panel) {
			this.activeItem.hide();
		}
		this.setItemSize(panel, this.getLayoutTargetSize());

		this.setActiveItem(panel);
		panel.doLayout();
	},
	
	
	//private. Use panel.show()
	setActiveItem: function (item) {
		item = this.container.getComponent(item);
		this.activeItem = item;
	},

	// private
	setItemSize: function (item, size) {
		
//		console.log(item, item);

		if (item && size.height > 0) { // display none?
			item.setSize(size);
			if (item.rendered) {
//				item.wideLeft = item.getEl().getLeft();
				item.getEl().setLeft(0);
			}
		}
	}
});


Ext.Container.LAYOUTS['responsive'] = go.layout.ResponsiveLayout;
