/**
 * Use like this:
 * 
 * Ext.applyIf(this, go.panels.ScrollLoader);
 *	this.initScrollLoader();
 */
go.panels.ScrollLoader = {
		
	pageSize: 30,
	
	scrollUp: false,  // set to true when you need to loadMore when scrolling up
	
	allRecordsLoaded : false,
	
	initScrollLoader :function() {
		//setup auto load more for go.data.Store's only
		if((this.store instanceof go.data.Store || this.store instanceof go.data.GroupingStore) && this.store.entityStore) {
			
			this.on('afterrender', this.onRenderScrollLoader, this);

			this.store.baseParams.limit = this.pageSize;
			
			
			this.on("afterrender", function() {
				if(this.store.loaded) {
					this.loadMore();
				}
			}, this, {single: true});

			this.store.on("load", function(store, records, o){
				this.allRecordsLoaded = records.length < this.pageSize;
				//If this element or any parent is hidden then  this.el.dom.offsetParent == null
				if(this.rendered && this.el.dom.offsetParent) {
					var me = this;
					setTimeout(function() {
						me.loadMore();	
					})
						
				}
			}, this);
		}
	},
	
	onRenderScrollLoader : function() {
		if(this.isGridPanel()) {	
			
			this.slScroller = this.getView().scroller.dom;
			this.slBody = this.getView().mainBody.dom;

			this.on('viewready', function(){
				var me = this;
				this.slScroller.addEventListener('scroll', function() {
					me.loadMore();
				}, {passive: true});
			}, this);

		} else {
			// this.el.on('scroll', this.loadMore, this);
			this.slScroller = this.el.dom;
			
			this.slScroller.addEventListener('scroll', function() {
				me.loadMore();
			}, {passive: true});

			this.slBody = this.el.dom;
		}
	},
	
	isGridPanel : function() {
		return this.getView && this.getView().scroller;
	},

	toggleLoadMask : function() {
		// only show loadmask when 		
		var pixelsLeft = this.slScroller.scrollHeight - this.slScroller.scrollTop - this.slScroller.offsetHeight;
		if(this.loadMask) {
			// if(Ext.isObject(this.loadMask))
			if(pixelsLeft > 100) {
				this.loadMask.disable();
			}else{
				this.loadMask.enable();
			}
		} 
	},
	
	/**
	 * Loads more data if the end off the scroll area is reached
	 * @returns {undefined}
	 */
	loadMore: function () {
		this.toggleLoadMask();
		var store = this.store;
		if (this.allRecordsLoaded || this.store.loading){
			return;
		}	

		var me = this;
		
		var scrollBoundary = (this.slScroller.offsetHeight * 4) + 600;

		if(this.scrollUp) {
			
			if(this.slScroller.scrollTop  < scrollBoundary) {
				var o = store.lastOptions ? GO.util.clone(store.lastOptions) : {};
				o.add = true;
				o.params = o.params || {};
				o.params.position = o.params.position || 0;
				o.params.position += this.pageSize;
				o.paging = true;

				if(this.isGridPanel()) {
					this.getView().scrollToTopOnLoad = false;
					//this.toggleLoadMask();
				}
				store.load(o).then(function() {
					// if(me.loadMask) {
					// 	me.loadMask.enable();
					// }
				});
			}
		} else {			

			var pixelsLeft = this.slScroller.scrollHeight - this.slScroller.scrollTop - this.slScroller.offsetHeight;
		
			var shouldLoad = (pixelsLeft < scrollBoundary);
		
			if (shouldLoad) {
				var o = store.lastOptions ? GO.util.clone(store.lastOptions) : {};
				o.add = true;
				o.params = o.params || {};

				o.params.position = o.params.position || 0;
				o.params.position += this.pageSize;
				o.paging = true;
				o.keepScrollPosition = true;

				
				store.load(o).then(function() {
					if(me.loadMask) {
						me.loadMask.enable();
					}
				});

			}
		}
	}
};