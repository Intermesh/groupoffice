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
				this.allRecordsLoaded = records.length != this.pageSize;
				//If this element or any parent is hidden then  this.el.dom.offsetParent == null
				if(this.rendered && this.el.dom.offsetParent) {
					this.loadMore();			
				}
			}, this);
		}
	},
	
	onRenderScrollLoader : function() {
		if(this.isGridPanel()) {
			this.on("bodyscroll", this.loadMore, this);
			
			this.slScroller = this.getView().scroller.dom;
			this.slBody = this.getView().mainBody.dom;

		} else {
			this.el.on('scroll', this.loadMore, this);
			this.slScroller = this.el.dom;
			this.slBody = this.el.dom;
		}
	},
	
	isGridPanel : function() {
		return this.getView && this.getView().scroller;
	},
	
	/**
	 * Loads more data if the end off the scroll area is reached
	 * @returns {undefined}
	 */
	loadMore: function () {
		
		var store = this.store;
		if (this.allRecordsLoaded || this.store.loading){
			return;
		}		

		var scrollBoundary = this.slScroller.offsetHeight + 300;

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
				}
				store.load(o);
			}
		} else {

		

			var shouldLoad = (this.slScroller.offsetHeight + this.slScroller.scrollTop + scrollBoundary) >= this.slBody.offsetHeight;
		
			if (shouldLoad) {
				var o = store.lastOptions ? GO.util.clone(store.lastOptions) : {};
				o.add = true;
				o.params = o.params || {};

				o.params.position = o.params.position || 0;
				o.params.position += this.pageSize;
				o.paging = true;

				if(this.isGridPanel()) {
					this.getView().scrollToTopOnLoad = false;
				}
				store.load(o);

			}
		}
	}
};