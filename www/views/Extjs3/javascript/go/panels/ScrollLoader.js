/**
 * Use like this:
 * 
 * Ext.applyIf(this, go.panels.ScrollLoader);
 *	this.initScrollLoader();
 */
go.panels.ScrollLoader = {
	
	scrollBoundary: 300,
	
	pageSize: 30,
	
	scrollUp: false,  // set to true when you need to loadMore when scrolling up
	
	allRecordsLoaded : false,
	
	initScrollLoader :function() {
		//setup auto load more for go.data.Store's only
		if(this.store instanceof go.data.Store && this.store.entityStore) {
			
			if(this instanceof Ext.grid.GridPanel) {
				this.on("bodyscroll", this.loadMore, this, {buffer: 100});
			} else {
				this.on('render', function(p){
					p.el.on('scroll', this.loadMore, this, {buffer: 100});
				});
			}

			this.store.baseParams.limit = this.pageSize;

			this.store.on("load", function(store, records, o){
				this.allRecordsLoaded = records.length < this.pageSize;
				
				if(this.rendered) {
					this.loadMore();			
				} else
				{
					this.on("afterrender", function() {
						this.loadMore();
					}, this, {single: true});
				}
			}, this);
		}
	},
	
	/**
	 * Loads more data if the end off the scroll area is reached
	 * @returns {undefined}
	 */
	loadMore: function () {

		var store = this.store;

		if (this.allRecordsLoaded){
			return;
		}

		if(this instanceof Ext.grid.GridPanel) {
			var	scroller = this.getView().scroller.dom,
				body = this.getView().mainBody.dom;
		} else {
		  var scroller = this.el.dom, body = this.el.dom;
		}

		if(scroller.offsetHeight < body.offsetHeight) {
			return; // no scroll bar
		}
		if(this.scrollUp) {
			
			if(scroller.scrollTop  < this.scrollBoundary) {
				var o = store.lastOptions ? GO.util.clone(store.lastOptions) : {};
				o.add = true;
				o.params = o.params || {};
				o.params.position = o.params.position || 0;
				o.params.position += this.pageSize;
				o.paging = true;
				store.load(o);
			}
		} else {
			if ((scroller.offsetHeight + scroller.scrollTop + this.scrollBoundary) >= body.offsetHeight) {
				var o = store.lastOptions ? GO.util.clone(store.lastOptions) : {};
				o.add = true;
				o.params = o.params || {};

				o.params.position = o.params.position || 0;
				o.params.position += this.pageSize;
				o.paging = true;

				store.load(o);

			}
		}
	}
};