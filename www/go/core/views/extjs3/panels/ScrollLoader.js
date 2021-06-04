/**
 * Use like this:
 * 
 * Ext.applyIf(this, go.panels.ScrollLoader);
 *	this.initScrollLoader();
 */
go.panels.ScrollLoader = {
		
	pageSize: 40,

	scrollBoundary: 200,
	
	scrollUp: false,  // set to true when you need to loadMore when scrolling up
	
	allRecordsLoaded : false,
	
	initScrollLoader :function() {
		//setup auto load more for go.data.Store's only
		if((this.store instanceof go.data.Store || this.store instanceof go.data.GroupingStore) && this.store.entityStore) {
			
			this.on('afterrender', this.onRenderScrollLoader, this);

			this.store.baseParams.limit = this.pageSize;
			
			// tests
			this.on("afterrender", function() {
				if(this.store.loaded) {
					this.loadMore();
				}
			}, this, {single: true});

			this.store.on("load", function(store, records, o){
				// Set page size to limit parameter
				var limit = o.params && o.params.limit ? o.params.limit: this.pageSize;
				if(limit !== this.pageSize) {
					this.pageSize = limit;
				}
				this.allRecordsLoaded = (records.length < limit);
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


	wasReloaded : function(o) {
		console.warn(o);
		if(!o || !o.params || !o.params.limit) {
			return false;
		}

		return o.params.position == 0 && o.params.limit > this.pageSize;
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
			var me = this;
			this.slScroller.addEventListener('scroll', function() {
				me.loadMore();
			}, {passive: true});

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

		const store = this.store;
		if (this.allRecordsLoaded || this.store.loading){
			return;
		}	


		if(this.scrollUp) {
			
			if(this.slScroller.scrollTop  < this.scrollBoundary) {
				var o = store.lastOptions ? GO.util.clone(store.lastOptions) : {};
				o.add = true;
				o.params = o.params || {};
				o.params.position = o.params.position || 0;
				o.params.position += (o.params.limit || this.pageSize);
				o.params.limit = this.pageSize;
				o.paging = true;

				if(this.isGridPanel()) {
					this.getView().scrollToTopOnLoad = false;
				}

				store.load(o)
			}
		} else {			

			const pixelsLeft = this.slScroller.scrollHeight - this.slScroller.scrollTop - this.slScroller.offsetHeight;
			const shouldLoad = (pixelsLeft < this.scrollBoundary);

			if (shouldLoad) {
				let o = store.lastOptions ? GO.util.clone(store.lastOptions) : {};
				o.add = true;
				o.params = o.params || {};

				o.params.position = o.params.position || 0;
				o.params.position += (o.params.limit || this.pageSize);
				o.params.limit = this.pageSize;
				o.paging = true;
				o.keepScrollPosition = true;
				
				store.load(o);

			}
		}
	}
};