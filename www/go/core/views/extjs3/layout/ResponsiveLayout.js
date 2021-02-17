go.layout.ResponsiveLayout = Ext.extend(Ext.layout.BorderLayout, {
	type: 'responsive',

	monitorResize: !GO.util.isMobileOrTablet(),

/**
 * Defaults to the first added item. Only used in narrow mode
 */
	activeItem: 0,

	initialized: false,

	/**
	 * Window size when layout switches from  "wide" to "narrow"
	 */
	triggerWidth: 1200,

	// wideWidth: null,
	
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
			
			//make sure border layout is initialized
			go.layout.ResponsiveLayout.superclass.onLayout.call(this, ct, target);

			ct.on('resize', function() { // when mobile orientation changes
				this.setItemSize(this.activeItem, this.getLayoutTargetSize());
			}, this)

			//Needed to make modification in Ext.layout.BorderLayout.SplitRegion.onSplitMove too.
			ct.items.each(function (i) {
				i.on('beforeshow', this.onBeforeShow, this);
				i.on('show', this.onPanelShow, this);
				i.wideWidth = i.width;		
			}, this);
		}
		
		var willBeWide = window.innerWidth > this.triggerWidth;
		this.setChildWidths(ct);
		
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
	
	getItemWidth : function(i) {
		
		if(i.getLayout && typeof i.getLayout().isNarrow == "function" && i.getLayout().isNarrow()) {
			return i.initialConfig.narrowWidth || i.wideWith;
		} else
		{
			return i.wideWidth;
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
				// i.stateful = true;
			}, this);		

			ct.cascade(function(i) {	
				
				if(i.origStateFul) {
					i.stateful = i.origStateful;
				}	
				return true;
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

			ct.cascade(function(i) {
				i.origStateFul = i.stateful;
				i.stateful = false;
				return true;
			}, this);

			ct.addClass('go-narrow');
			
			ct.items.each(function (i) {
				if(!i.hidden) {
					i.hide();
				}
			}, this);					
			
		}
		
		this.activeItem.show();
		
	},
	
	setChildWidths : function(ct) {
		ct.items.each(function (i) {			
			var w = this.getItemWidth(i);							
			i.setWidth(w);
			
			i.setHeight(ct.getHeight());
			
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
		
	},

	onPanelShow: function(panel) {
		if(panel.doLayout) {
			panel.doLayout();
		}
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
