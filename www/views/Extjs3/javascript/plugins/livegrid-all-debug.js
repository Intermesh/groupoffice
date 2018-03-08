/**
 * Ext.ux.grid.livegrid.GridPanel
 * Copyright (c) 2007-2012, http://www.siteartwork.de
 *
 * Ext.ux.grid.livegrid.GridPanel is licensed under the terms of the
 *                  GNU Open Source GPL 3.0
 * license.
 *
 * Commercial use is prohibited. Visit <http://ext-livegrid.com>
 * if you need to obtain a commercial license.
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 */

Ext.namespace('Ext.ux.grid.livegrid');

/**
 * @class Ext.ux.grid.livegrid.GridPanel
 * @extends Ext.grid.GridPanel
 * @constructor
 * @param {Object} config
 *
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 */
Ext.ux.grid.livegrid.GridPanel = Ext.extend(Ext.grid.GridPanel, {

    initComponent : function()
    {
        if (this.cls) {
            this.cls += ' ext-ux-livegrid';
        } else {
            this.cls = 'ext-ux-livegrid';
        }

        Ext.ux.grid.livegrid.GridPanel.superclass.initComponent.call(this);
    },

    /**
     * Overriden to make sure the attached store loads only when the
     * grid has been fully rendered if, and only if the store's
     * "autoLoad" property is set to true.
     *
     */
    onRender : function(ct, position)
    {
        Ext.ux.grid.livegrid.GridPanel.superclass.onRender.call(this, ct, position);

        var ds = this.getStore();

        if (ds._autoLoad === true) {
            delete ds._autoLoad;
            ds.load();
        }
    },

    /**
     * Overriden since the original implementation checks for
     * getCount() of the store, not getTotalCount().
     *
     */
    walkCells : function(row, col, step, fn, scope)
    {
        var ds  = this.store;
        var _oF = ds.getCount;

        ds.getCount = ds.getTotalCount;

        var ret = Ext.ux.grid.livegrid.GridPanel.superclass.walkCells.call(this, row, col, step, fn, scope);

        ds.getCount = _oF;

        return ret;
    }

});/**
 * Ext.ux.grid.livegrid.GridView
 * Copyright (c) 2007-2012, http://www.siteartwork.de
 *
 * Ext.ux.grid.livegrid.GridView is licensed under the terms of the
 *                  GNU Open Source GPL 3.0
 * license.
 *
 * Commercial use is prohibited. Visit <http://ext-livegrid.com>
 * if you need to obtain a commercial license.
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 */

Ext.namespace('Ext.ux.grid.livegrid');

/**
 * @class Ext.ux.grid.livegrid.GridView
 * @extends Ext.grid.GridView
 * @constructor
 * @param {Object} config
 *
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 */
Ext.ux.grid.livegrid.GridView = function(config) {

    this.addEvents({
        /**
         * @event reset
         * Fires when the grid resets.
         * @param {Ext.ux.grid.livegrid.GridView} this
         * @param {Boolean} forceReload
         * @param {Object} params Additional params which get passed to the
         * store if forceReload was set to true
         */
        'reset' : true,
        /**
         * @event beforebuffer
         * Fires when the store is about to buffer new data.
         * @param {Ext.ux.BufferedGridView} this
         * @param {Ext.data.Store} store The store
         * @param {Number} rowIndex
         * @param {Number} visibleRows
         * @param {Number} totalCount
         * @param {Number} options The options with which the buffer request was called
         */
        'beforebuffer' : true,
        /**
         * @event buffer
         * Fires when the store is finsihed buffering new data.
         * @param {Ext.ux.BufferedGridView} this
         * @param {Ext.data.Store} store The store
         * @param {Number} rowIndex
         * @param {Number} visibleRows
         * @param {Number} totalCount
         * @param {Object} options
         */
        'buffer' : true,
        /**
         * @event bufferfailure
         * Fires when buffering failed.
         * @param {Ext.ux.BufferedGridView} this
         * @param {Ext.data.Store} store The store
         * @param {Object} options The options the buffer-request was initiated with
         */
        'bufferfailure' : true,
        /**
         * @event cursormove
         * Fires when the the user scrolls through the data.
         * @param {Ext.ux.BufferedGridView} this
         * @param {Number} rowIndex The index of the first visible row in the
         *                          grid absolute to it's position in the model.
         * @param {Number} visibleRows The number of rows visible in the grid.
         * @param {Number} totalCount
         */
        'cursormove' : true,
        /**
         * @event abortrequest
         * Fires when the store is about to reload (this does NOT mean buffering).
         * If you are using a custom proxy in your store, you should listen to this event
         * and abort any ongoing server request established in your custom proxy.
         * @param {Ext.data.Store} store
         * @param {Object} options
         */
        'abortrequest' : true

    });

    /**
     * @cfg {Number} scrollDelay The number of microseconds a call to the
     * onLiveScroll-lisener should be delayed when the scroll event fires
     */

    /**
     * @cfg {Number} bufferSize The number of records that will at least always
     * be available in the store for rendering. This value will be send to the
     * server as the <tt>limit</tt> parameter and should not change during the
     * lifetime of a grid component. Note: In a paging grid, this number would
     * indicate the page size.
     * The value should be set high enough to make a userfirendly scrolling
     * possible and should be greater than the sum of {nearLimit} and
     * {visibleRows}. Usually, a value in between 150 and 200 is good enough.
     * A lesser value will more often make the store re-request new data, while
     * a larger number will make loading times higher.
     */

    /**
     * @cfg {Number} nearLimit This value represents a near value that is responsible
     * for deciding if a request for new data is needed. The lesser the number, the
     * more often new data will be requested. The number should be set to a value
     * that lies in between 1/4 to 1/2 of the {bufferSize}.
     */

    /**
     * @cfg {Number} horizontalScrollOffset The height of a horizontal aligned
     * scrollbar.  The scrollbar is shown if the total width of all visible
     * columns exceeds the width of the grid component.
     * On Windows XP (IE7, FF2), this value defaults to 17.
     */
    this.horizontalScrollOffset = 17;

    /**
     * @type {Boolean} _checkEmptyBody Since Ext 3.0, &nbsp; would initially added to the mainBody
     * as the first child if there are no rows to render. This element has to be removed when
     * the first rows get added so the UI does not crash. This property is here to determine if
     * this element was already removed, so we don't have to query innerHTML all the time.
     */
    this._checkEmptyBody = true;

    Ext.apply(this, config);

    this.templates = {};
    /**
     * The master template adds an addiiotnal scrollbar to make cursoring in the
     * data possible.
     */
    this.templates.master = new Ext.Template(
        '<div class="x-grid3" hidefocus="true"><div class="liveScroller"><div></div><div></div><div></div></div>',
            '<div class="x-grid3-viewport"">',
                '<div class="x-grid3-header"><div class="x-grid3-header-inner"><div class="x-grid3-header-offset" style="{ostyle}">{header}</div></div><div class="x-clear"></div></div>',
                '<div class="x-grid3-scroller" style="overflow-y:hidden !important;"><div class="x-grid3-body" style="{bstyle}">{body}</div><a href="#" class="x-grid3-focus" tabIndex="-1"></a></div>',
            "</div>",
            '<div class="x-grid3-resize-marker">&#160;</div>',
            '<div class="x-grid3-resize-proxy">&#160;</div>',
        "</div>"
    );

    // shorthands for often used parent classes
    this._gridViewSuperclass = Ext.ux.grid.livegrid.GridView.superclass;

    this._gridViewSuperclass.constructor.call(this);


};


Ext.extend(Ext.ux.grid.livegrid.GridView, Ext.grid.GridView, {

// {{{ --------------------------properties-------------------------------------

    /**
     * Stores the height of the header. Needed for recalculating scroller inset height.
     * @param {Number}
     */
    hdHeight : 0,

    /**
     * Indicates wether the last row in the grid is clipped and thus not fully display.
     * 1 if clipped, otherwise 0.
     * @param {Number}
     */
    rowClipped : 0,


    /**
     * This is the actual y-scroller that does control sending request to the server
     * based upon the position of the scrolling cursor.
     * @param {Ext.Element}
     */
    liveScroller : null,

    /**
     * This array holds the divs that represent the amount of data in a given repository.
     * The sum of heights of this divs gets computed via the total amount of records
     * multiplied with the fixed(!) row height.
     * There is a total of 3 divs responsible for the scroll amount to prevent issues
     * with the max number of pxiels a div alone can grow in height.
     * @param {native HTMLObject}
     */
    liveScrollerInsets : null,

    /**
     * The <b>fixed</b> row height for <b>every</b> row in the grid. The value is
     * computed once the store has been loaded for the first time and used for
     * various calculations during the lifetime of the grid component, such as
     * the height of the scroller and the number of visible rows.
     * @param {Number}
     */
    rowHeight : -1,

    /**
     * Stores the number of visible rows that have to be rendered.
     * @param {Number}
     */
    visibleRows : 1,

    /**
     * Stores the last offset relative to a previously scroll action. This is
     * needed for deciding wether the user scrolls up or down.
     * @param {Number}
     */
    lastIndex : -1,

    /**
     * Stores the last visible row at position "0" in the table view before
     * a new scroll event was created and fired.
     * @param {Number}
     */
    lastRowIndex : 0,

    /**
     * Stores the value of the <tt>liveScroller</tt>'s <tt>scrollTop</tt> DOM
     * property.
     * @param {Number}
     */
    lastScrollPos : 0,

    /**
     * The current index of the row in the model that is displayed as the first
     * visible row in the view.
     * @param {Number}
     */
    rowIndex : 0,

    /**
    * Set to <tt>true</tt> if the store is busy with loading new data.
    * @param {Boolean}
    */
    isBuffering : false,

	/**
	 * If a request for new data was made and the user scrolls to a new position
	 * that lays not within the requested range of the new data, the queue will
	 * hold the latest requested position. If the buffering succeeds and the value
	 * of requestQueue is not within the range of the current buffer, data may be
	 * re-requested.
	 *
	 * @param {Number}
	 */
    requestQueue : -1,

    /**
     * An {@Ext.LoadMask} config that will be shown when a request to data was made
     * and there are no rows in the buffer left to render.
     * @param {Object}
     */
    loadMask : false,

    /**
     * A shortcut to indicate whether the loadMask is currently being displayed.
     * @type {Boolean}
     * @private
     */
    loadMaskDisplayed : false,

    /**
     * Set to <tt>true</tt> if a request for new data has been made while there
     * are still rows in the buffer that can be rendered before the request
     * finishes.
     * @param {Boolean}
     */
    isPrebuffering : false,

    /**
     * The dom node for which the node mask will be rendered.
     * @type {Ext.Element}
     * @private
     */
    _loadMaskAnchor : null,

// }}}

// {{{ --------------------------public API methods-----------------------------

    /**
     * Resets the view to display the first row in the data model. This will
     * change the scrollTop property of the scroller and may trigger a request
     * to buffer new data, if the row index "0" is not within the buffer range and
     * forceReload is set to true.
     *
     * @param {Boolean} forceReload <tt>true</tt> to reload the buffers contents,
     *                              othwerwise <tt>false</tt>
     * @params {Object} addParams optional object of properties which get treated as
     *                            params added to the "options" argument for the
     *                            load() method if forceReload invokes a call
     *                            call to this method
     *
     * @return {Boolean} Whether the store loads after reset(true); returns false
     * if any of the attached beforeload listeners cancels the load-event
     */
    reset : function(forceReload, addParams)
    {
        if (forceReload === false) {
            this.ds.modified = [];
            //this.grid.selModel.clearSelections(true);
            this.rowIndex      = 0;
            this.lastScrollPos = 0;
            this.lastRowIndex = 0;
            this.lastIndex    = 0;
            this.adjustVisibleRows();
            this.adjustScrollerPos(-this.liveScroller.dom.scrollTop, true);
            this.showLoadMask(false);

            var _ofn = this.processRows;
            this.processRows = Ext.emptyFn;
            this.suspendEvents();
            this.refresh(true);
            this.resumeEvents();
            this.processRows = _ofn;
            this.processRows(0);

            this.fireEvent('cursormove', this, 0,
                           Math.min(this.ds.totalLength, this.visibleRows-this.rowClipped),
                           this.ds.totalLength);
            this.fireEvent('reset', this, forceReload);
            return false;
        } else {
            var params = Ext.apply({}, addParams),
                sInfo  = this.ds.sortInfo;

            var pn = this.ds.paramNames,
                cp = {};

                cp[pn.dir]  = sInfo.direction;
                cp[pn.sort] = sInfo.field;

            if (sInfo) {
                params = Ext.apply(params, cp);
            }

            this.fireEvent('reset', this, forceReload, params);
            return this.ds.load({params : params});
        }
    },

// {{{ ------------adjusted methods for applying custom behavior----------------

    afterRenderUI : function()
    {
        var g = this.grid;
        var dEnabled = g.enableDragDrop || g.enableDrag;

        g.enableDragDrop = false;
        g.enableDrag     = false;

        this._gridViewSuperclass.afterRenderUI.call(this);

        var g = this.grid;

        g.enableDragDrop = dEnabled;
        g.enableDrag     = dEnabled;

        if(dEnabled){
            this.dragZone = new Ext.ux.grid.livegrid.DragZone(g, {
                ddGroup : g.ddGroup || 'GridDD'
            });
        }

        if (this.loadMask) {
            this._loadMaskAnchor = Ext.get(this.mainBody.dom.parentNode.parentNode);
            Ext.apply(this.loadMask,{
                msgCls : 'x-mask-loading'
            });
            this._loadMaskAnchor.mask(
                this.loadMask.msg, this.loadMask.msgCls
            );
            var dom  = this._loadMaskAnchor.dom;
            var data = Ext.Element.data;
            data(dom, 'mask').addClass('ext-ux-livegrid');
            data(dom, 'mask').setDisplayed(false);
            data(dom, 'maskMsg').setDisplayed(false);
        }
    },

    /**
     * The extended implementation attaches an listener to the beforeload
     * event of the store of the grid. It is guaranteed that the listener will
     * only be executed upon reloading of the store, sorting and initial loading
     * of data. When the store does "buffer", all events are suspended and the
     * beforeload event will not be triggered.
     *
     * @param {Ext.grid.GridPanel} grid The grid panel this view is attached to
     */
    init: function(grid)
    {
        this._gridViewSuperclass.init.call(this, grid);

        grid.on('expand', this._onExpand, this);
    },

    initData : function(ds, cm)
    {
        if(this.ds){
            this.ds.un('bulkremove', this.onBulkRemove, this);
            this.ds.un('beforeload', this.onBeforeLoad, this);
        }
        if(ds){
            ds.on('bulkremove', this.onBulkRemove, this);
            ds.on('beforeload', this.onBeforeLoad, this);
        }

        this._gridViewSuperclass.initData.call(this, ds, cm);
    },

    /**
     * Only render the viewable rect of the table. The number of rows visible to
     * the user is defined in <tt>visibleRows</tt>.
     * This implementation does completely overwrite the parent's implementation.
     */
    // private
    renderBody : function()
    {
        var markup = this.renderRows(0, this.visibleRows-1);
        return this.templates.body.apply({rows: markup});
    },

    /**
     * Overriden so the renderer of the specific cells gets the index of the
     * row as available in the view passed (row's rowIndex property)-
     *
     */
    doRender : function(cs, rs, ds, startRow, colCount, stripe)
    {
        return this._gridViewSuperclass.doRender.call(
            this, cs, rs, ds, startRow + this.ds.bufferRange[0], colCount, stripe
        );

    },

    /**
     * Inits the DOM native elements for this component.
     * The properties <tt>liveScroller</tt> and <tt>liveScrollerInsets</tt> will
     * be respected as provided by the master template.
     * The <tt>scroll</tt> listener for the <tt>liverScroller</tt> will also be
     * added here as the <tt>mousewheel</tt> listener.
     * This method overwrites the parents implementation.
     */
    // private
    initElements : function()
    {
        var E = Ext.Element;

        var el = this.grid.getGridEl().dom.firstChild;
	    var cs = el.childNodes;

	    this.el = new E(el);

        this.mainWrap = new E(cs[1]);

        // liveScroller and liveScrollerInsets
        this.liveScroller       = new E(cs[0]);
        var f = this.liveScroller.dom.firstChild;
        this.liveScrollerInsets  = [
            f,
            f.nextSibling,
            f.nextSibling.nextSibling
        ];
        this.liveScroller.on('scroll', this.onLiveScroll,  this, {buffer : this.scrollDelay});

        var thd = this.mainWrap.dom.firstChild;
	    this.mainHd = new E(thd);

	    this.hdHeight = thd.offsetHeight;

	    this.innerHd = this.mainHd.dom.firstChild;
        this.scroller = new E(this.mainWrap.dom.childNodes[1]);
        if(this.forceFit){
            this.scroller.setStyle('overflow-x', 'hidden');
        }
        this.mainBody = new E(this.scroller.dom.firstChild);

        // addd the mousewheel event to the table's body
        this.mainBody.on('mousewheel', this.handleWheel,  this);

	    this.focusEl = new E(this.scroller.dom.childNodes[1]);
        this.focusEl.swallowEvent("click", true);

        this.resizeMarker = new E(cs[2]);
        this.resizeProxy = new E(cs[3]);

    },

	/**
	 * Layouts the grid's view taking the scroller into account. The height
	 * of the scroller gets adjusted depending on the total width of the columns.
	 * The width of the grid view will be adjusted so the header and the rows do
	 * not overlap the scroller.
	 * This method will also compute the row-height based on the first row this
	 * grid displays and will adjust the number of visible rows if a resize
	 * of the grid component happened.
	 * This method overwrites the parents implementation.
	 */
	//private
    layout : function()
    {
        if(!this.mainBody){
            return; // not rendered
        }
        var g = this.grid;
        var c = g.getGridEl(), cm = this.cm,
                expandCol = g.autoExpandColumn,
                gv = this;

        var csize = c.getSize(true);

        // set vw to 19 to take scrollbar width into account!
        var vw = csize.width;

        if(!g.hideHeaders && vw < 20 || csize.height < 20){ // display: none?
            return;
        }

        if(g.autoHeight){
            this.scroller.dom.style.overflow = 'visible';
            if(Ext.isWebKit){
                this.scroller.dom.style.position = 'static';
            }
        }else{
            this.el.setSize(csize.width, csize.height);

            var hdHeight = this.mainHd.getHeight();
            var vh = csize.height - (hdHeight);

            this.scroller.setSize(vw, vh);
            if(this.innerHd){
                this.innerHd.style.width = (vw)+'px';
            }
        }

        this.liveScroller.dom.style.top = this.hdHeight+"px";

        if(this.forceFit){
            if(this.lastViewWidth != vw){
                this.fitColumns(false, false);
                this.lastViewWidth = vw;
            }
        }else {
            this.autoExpand();
        }

        // adjust the number of visible rows and the height of the scroller.
        this.adjustVisibleRows();
        this.adjustBufferInset();

        this.onLayout(vw, vh);
    },

    /**
     * Overriden for Ext 2.2 to prevent call to focus Row.
     *
     */
    removeRow : function(row)
    {
        Ext.removeNode(this.getRow(row));
    },

    /**
     * Overriden for Ext 2.2 to prevent call to focus Row.
     * This method i s here for dom operations only - the passed arguments are the
     * index of the nodes in the dom, not in the model.
     *
     */
    removeRows : function(firstRow, lastRow)
    {
        var bd = this.mainBody.dom;
        for(var rowIndex = firstRow; rowIndex <= lastRow; rowIndex++){
            Ext.removeNode(bd.childNodes[firstRow]);
        }
    },

// {{{ ----------------------dom/mouse listeners--------------------------------

    /**
     * Tells the view to recalculate the number of rows displayable
     * and the buffer inset, when it gets expanded after it has been
     * collapsed.
     *
     */
    _onExpand : function(panel)
    {
        this.adjustVisibleRows();
        this.adjustBufferInset();
        this.adjustScrollerPos(this.rowHeight*this.rowIndex, true);
    },

    // private
    onColumnMove : function(cm, oldIndex, newIndex)
    {
        this.indexMap = null;
        this.replaceLiveRows(this.rowIndex, true);
        this.updateHeaders();
        this.updateHeaderSortState();
        this.afterMove(newIndex);
        this.grid.fireEvent('columnmove', oldIndex, newIndex);
    },


    /**
     * Called when a column width has been updated. Adjusts the scroller height
     * and the number of visible rows wether the horizontal scrollbar is shown
     * or not.
     */
    onColumnWidthUpdated : function(col, w, tw)
    {
        this.adjustVisibleRows();
        this.adjustBufferInset();
    },

    /**
     * Called when the width of all columns has been updated. Adjusts the scroller
     * height and the number of visible rows wether the horizontal scrollbar is shown
     * or not.
     */
    onAllColumnWidthsUpdated : function(ws, tw)
    {
        this.adjustVisibleRows();
        this.adjustBufferInset();
    },

    /**
     * Callback for selecting a row. The index of the row is the absolute index
     * in the datamodel. If the row is not rendered, this method will do nothing.
     */
    // private
    onRowSelect : function(row)
    {
        if (row < this.rowIndex || row > this.rowIndex+this.visibleRows) {
            return;
        }

        this.addRowClass(row, this.selectedRowClass);
    },

    /**
     * Callback for deselecting a row. The index of the row is the absolute index
     * in the datamodel. If the row is not currently rendered in the view, this method
     * will do nothing.
     */
    // private
    onRowDeselect : function(row)
    {
        if (row < this.rowIndex || row > this.rowIndex+this.visibleRows) {
            return;
        }

        this.removeRowClass(row, this.selectedRowClass);
    },


// {{{ ----------------------data listeners-------------------------------------
    /**
     * Called when the buffer gets cleared. Simply calls the updateLiveRows method
     * with the adjusted index and should force the store to reload
     */
    // private
    onClear : function()
    {
        this.reset(false);
    },

    /**
     * Callback for the "bulkremove" event of the attached datastore.
     *
     * @param {Ext.ux.grid.livegrid.Store} store
     * @param {Array} removedData
     *
     */
    onBulkRemove : function(store, removedData)
    {
        var record    = null;
        var index     = 0;
        var viewIndex = 0;
        var len       = removedData.length;

        var removedInView    = false;
        var removedAfterView = false;
        var scrollerAdjust   = 0;

        if (len == 0) {
            return;
        }

        var tmpRowIndex   = this.rowIndex;
        var removedBefore = 0;
        var removedAfter  = 0;
        var removedIn     = 0;

        for (var i = 0; i < len; i++) {
            record = removedData[i][0];
            index  = removedData[i][1];

            viewIndex = (index != Number.MIN_VALUE && index != Number.MAX_VALUE)
                      ? index + this.ds.bufferRange[0]
                      : index;

            if (viewIndex < this.rowIndex) {
                removedBefore++;
            } else if (viewIndex >= this.rowIndex && viewIndex <= this.rowIndex+(this.visibleRows-1)) {
                removedIn++;
            } else if (viewIndex >= this.rowIndex+this.visibleRows) {
                removedAfter++;
            }

            this.fireEvent("beforerowremoved", this, viewIndex, record);
            this.fireEvent("rowremoved",       this, viewIndex, record);
        }

        var totalLength = this.ds.totalLength;
        this.rowIndex   = Math.max(0, Math.min(this.rowIndex - removedBefore, totalLength-(this.visibleRows-1)));

        this.lastRowIndex = this.rowIndex;

        this.adjustScrollerPos(-(removedBefore*this.rowHeight), true);
        this.updateLiveRows(this.rowIndex, true);
        this.adjustBufferInset();
        this.processRows(0, undefined, false);

    },


    /**
     * Callback for the underlying store's remove method. The current
     * implementation does only remove the selected row which record is in the
     * current store.
     *
     * @see onBulkRemove()
     */
    // private
    onRemove : function(ds, record, index)
    {
        this.onBulkRemove(ds, [[record, index]]);
    },

    /**
     * The callback for the underlying data store when new data was added.
     * If <tt>index</tt> equals to <tt>Number.MIN_VALUE</tt> or <tt>Number.MAX_VALUE</tt>, the
     * method can't tell at which position in the underlying data model the
     * records where added. However, if <tt>index</tt> equals to <tt>Number.MIN_VALUE</tt>,
     * the <tt>rowIndex</tt> property will be adjusted to <tt>rowIndex+records.length</tt>,
     * and the <tt>liveScroller</tt>'s properties get adjusted so it matches the
     * new total number of records of the underlying data model.
     * The same will happen to any records that get added at the store index which
     * is currently represented by the first visible row in the view.
     * Any other value will cause the method to compute the number of rows that
     * have to be (re-)painted and calling the <tt>insertRows</tt> method, if
     * neccessary.
     *
     * This method triggers the <tt>beforerowsinserted</tt> and <tt>rowsinserted</tt>
     * event, passing the indexes of the records as they may default to the
     * positions in the underlying data model. However, due to the fact that
     * any sort algorithm may have computed the indexes of the records, it is
     * not guaranteed that the computed indexes equal to the indexes of the
     * underlying data model.
     *
     * @param {Ext.ux.grid.livegrid.Store} ds The datastore that buffers records
     *                                       from the underlying data model
     * @param {Array} records An array containing the newly added
     *                        {@link Ext.data.Record}s
     * @param {Number} index The index of the position in the underlying
     *                       {@link Ext.ux.grid.livegrid.Store} where the rows
     *                       were added.
     * @param {Boolean} isLastSet tells whether the record was added at the very
     *                            end of the store, and all of the store's data
     *                            was loaded, i.e. behind the last fetched index
     *                            are no more records
     *
     */
    // private
    onAdd : function(ds, records, index, isLastSet)
    {
        if (this._checkEmptyBody) {
            if (this.mainBody.dom.innerHTML == '&nbsp;') {
                this.mainBody.dom.innerHTML = '';
            }
            this._checkEmptyBody = false;
        }

        // deal with lastSet first
        if (isLastSet) {
            var viewIndexFirst = firstRow + ds.bufferRange[0],
                viewIndexLast = lastRow + ds.bufferRange[0],
                html;

            this.lastRowIndex = this.rowIndex;
            this.rowIndex++;

            firstRow = index-1;
            lastRow  = index-1;

            html = this.renderRows(firstRow, lastRow);
            Ext.DomHelper.insertHtml('beforeEnd', this.mainBody.dom, html);
            this.fireEvent(
                "rowsinserted", this,
                viewIndexFirst,
                viewIndexLast,
                (viewIndexLast-viewIndexFirst)+1
            );
            this.processRows(0, undefined, true);

            this.adjustVisibleRows();
            this.adjustBufferInset();

            this.adjustScrollerPos(this.rowHeight);

            return;
        }


        var recordLen = records.length;

        // values of index which equal to Number.MIN_VALUE or Number.MAX_VALUE
        // indicate that the records were not added to the store. The component
        // does not know which index those records do have in the underlying
        // data model
        if (index == Number.MAX_VALUE || index == Number.MIN_VALUE) {
            this.fireEvent("beforerowsinserted", this, index, index);

            // if index equals to Number.MIN_VALUE, shift rows!
            if (index == Number.MIN_VALUE) {

                this.rowIndex     = this.rowIndex + recordLen;
                this.lastRowIndex = this.rowIndex;

                this.adjustBufferInset();
                this.adjustScrollerPos(this.rowHeight*recordLen, true);

                this.fireEvent("rowsinserted", this, index, index, recordLen);
                this.processRows(0, undefined, false);
                // the cursor did virtually move
                this.fireEvent('cursormove', this, this.rowIndex,
                               Math.min(this.ds.totalLength, this.visibleRows-this.rowClipped),
                               this.ds.totalLength);

                return;
            }

            this.adjustBufferInset();
            this.fireEvent("rowsinserted", this, index, index, recordLen);
            return;
        }

        // only insert the rows which affect the current view.
        var start = index+this.ds.bufferRange[0];
        var end   = start + (recordLen-1);
        var len   = this.getRows().length;

        var firstRow = 0;
        var lastRow  = 0;

        // rows would be added at the end of the rows which are currently
        // displayed, so fire the event, resize buffer and adjust visible
        // rows and return
        if (start > this.rowIndex+(this.visibleRows-1)) {
            this.fireEvent("beforerowsinserted", this, start, end);
            this.fireEvent("rowsinserted",       this, start, end, recordLen);

            this.adjustVisibleRows();
            this.adjustBufferInset();

        }

        // rows get added somewhere in the current view.
        else if (start >= this.rowIndex && start <= this.rowIndex+(this.visibleRows-1)) {
            firstRow = index;
            // compute the last row that would be affected of an insert operation
            lastRow  = index+(recordLen-1);
            this.lastRowIndex  = this.rowIndex;
            this.rowIndex      = (start > this.rowIndex) ? this.rowIndex : start;

            this.insertRows(ds, firstRow, lastRow);

            if (this.lastRowIndex != this.rowIndex) {
                this.fireEvent('cursormove', this, this.rowIndex,
                               Math.min(this.ds.totalLength, this.visibleRows-this.rowClipped),
                               this.ds.totalLength);
            }

            this.adjustVisibleRows();
            this.adjustBufferInset();
        }

        // rows get added before the first visible row, which would not affect any
        // rows to be re-rendered
        else if (start < this.rowIndex) {
            this.fireEvent("beforerowsinserted", this, start, end);

            this.rowIndex     = this.rowIndex+recordLen;
            this.lastRowIndex = this.rowIndex;

            this.adjustVisibleRows();
            this.adjustBufferInset();

            this.adjustScrollerPos(this.rowHeight*recordLen, true);

            this.fireEvent("rowsinserted", this, start, end, recordLen);
            this.processRows(0, undefined, true);

            this.fireEvent('cursormove', this, this.rowIndex,
                           Math.min(this.ds.totalLength, this.visibleRows-this.rowClipped),
                           this.ds.totalLength);
        }




    },

// {{{ ----------------------store listeners------------------------------------
    /**
     * This callback for the store's "beforeload" event will adjust the start
     * position and the limit of the data in the model to fetch. It is guaranteed
     * that this method will only be called when the store initially loads,
     * remeote-sorts or reloads.
     * All other load events will be suspended when the view requests buffer data.
     * See {updateLiveRows}.
     * Note:
     * If you are using a custom proxy, such as {Ext.data.DirectProxy}, you should listen
     * to the 'abortrequest'-event, which will tell that an ongoing "read" request should be
     * aborted, since the grid's store gets refreshed.
     * If the store is using an instance of {Ext.data.HttpProxy}, the method will still be
     * fired, but the request made through this proxy will be aborted automatically.
     *
     *
     * @param {Ext.data.Store} store The store the Grid Panel uses
     * @param {Object} options The configuration object for the proxy that loads
     *                         data from the server
     */
    onBeforeLoad : function(store, options)
    {
        var proxy = store.proxy;
        if (proxy.activeRequest && proxy.activeRequest[Ext.data.Api.actions.read]) {
            proxy.getConnection().abort(proxy.activeRequest[Ext.data.Api.actions.read]);
        }
        this.fireEvent('abortrequest', store, options);

        this.isBuffering    = false;
        this.isPreBuffering = false;

        options.params = options.params || {};

        var apply = Ext.apply;

        apply(options, {
            scope    : this,
            callback : function(){
                this.reset(false);
            },
            suspendLoadEvent : false
        });

        var pn = store.paramNames,
            cp = {};

            cp[pn.start] = 0;
            cp[pn.limit] = this.ds.bufferSize;

        apply(options.params, cp);

        return true;
    },

    /**
     * Method is used as a callback for the load-event of the attached data store.
     * Adjusts the buffer inset based upon the <tt>totalCount</tt> property
     * returned by the response.
     * Overwrites the parent's implementation.
     */
    onLoad : function(o1, o2, options)
    {
        this.adjustBufferInset();
    },

    /**
     * This will be called when the data in the store has changed, i.e. a
     * re-buffer has occured. If the table was not rendered yet, a call to
     * <tt>refresh</tt> will initially render the table, which DOM elements will
     * then be used to re-render the table upon scrolling.
     *
     */
    // private
    onDataChange : function(store)
    {
        this.updateHeaderSortState();
    },

    /**
     * A callback for the store when new data has been buffered successfully.
     * If the current row index is not within the range of the newly created
     * data buffer or another request to new data has been made while the store
     * was loading, new data will be re-requested.
     *
     * Additionally, if there are any rows that have been selected which were not
     * in the data store, the method will request the pending selections from
     * the grid's selection model and add them to the selections if available.
     * This is because the component assumes that a user who scrolls through the
     * rows and updates the view's buffer during scrolling, can check the selected
     * rows which come into the view for integrity. It is up to the user to
     * deselect those rows not matchuing the selection.
     * Additionally, if the version of the store changes during various requests
     * and selections are still pending, the versionchange event of the store
     * can delete the pending selections after a re-bufer happened and before this
     * method was called.
     *
     */
    // private
    liveBufferUpdate : function(records, options, success)
    {
        if (success === true) {
            this.adjustBufferInset();

            this.fireEvent('buffer', this, this.ds, this.rowIndex,
                Math.min(this.ds.totalLength, this.visibleRows-this.rowClipped),
                this.ds.totalLength,
                options
            );

            // this is needed since references to records which have been unloaded
            // get lost when the store gets loaded with new data.
            // from the store
            this.grid.selModel.replaceSelections(records);

            this.isBuffering    = false;
            this.isPrebuffering = false;
            this.showLoadMask(false);

            if (this.requestQueue >= 0) {
                var offset = this.requestQueue;
                this.requestQueue = -1;
                this.updateLiveRows(offset);
                return;
            }

            if (this.isInRange(this.rowIndex)) {
                this.replaceLiveRows(this.rowIndex, options.forceRepaint);
            } else {
                this.updateLiveRows(this.rowIndex);
            }


            return;
        } else {
            this.fireEvent('bufferfailure', this, this.ds, options);
        }

        this.requestQueue   = -1;
        this.isBuffering    = false;
        this.isPrebuffering = false;
        this.showLoadMask(false);
    },


// {{{ ----------------------scroll listeners------------------------------------
    /**
     * Handles mousewheel event on the table's body. This is neccessary since the
     * <tt>liveScroller</tt> element is completely detached from the table's body.
     *
     * @param {Ext.EventObject} e The event object
     */
    handleWheel : function(e)
    {
        if (this.rowHeight == -1) {
            e.stopEvent();
            return;
        }
        var d = e.getWheelDelta();

        this.adjustScrollerPos(-(d*this.rowHeight));

        e.stopEvent();
    },

    /**
     * Handles scrolling through the grid. Since the grid is fixed and rows get
     * removed/ added subsequently, the only way to determine the actual row in
     * view is to measure the <tt>scrollTop</tt> property of the <tt>liveScroller</tt>'s
     * DOM element.
     *
     */
    onLiveScroll : function()
    {
        var scrollTop = this.liveScroller.dom.scrollTop;

        var cursor = Math.floor((scrollTop)/this.rowHeight);

        this.rowIndex = cursor;
        // the lastRowIndex will be set when refreshing the view has finished
        if (cursor == this.lastRowIndex) {
            return;
        }

        this.updateLiveRows(cursor);

        this.lastScrollPos = this.liveScroller.dom.scrollTop;
    },



// {{{ --------------------------helpers----------------------------------------

    // private
    refreshRow : function(record)
    {
        var ds = this.ds, index;
        if(typeof record == 'number'){
            index = record;
            record = ds.getAt(index);
        }else{
            index = ds.indexOf(record);
        }

        var viewIndex = index + this.ds.bufferRange[0];

        if (viewIndex < this.rowIndex || viewIndex >= this.rowIndex + this.visibleRows) {
            this.fireEvent("rowupdated", this, viewIndex, record);
            return;
        }

        this.insertRows(ds, index, index, true);
        this.fireEvent("rowupdated", this, viewIndex, record);
    },


    /**
     * Overwritten so the rowIndex can be changed to the absolute index.
     *
     * If the third parameter equals to <tt>true</tt>, the method will also
     * repaint the selections.
     */
    // private
    processRows : function(startRow, skipStripe, paintSelections)
    {
        if(!this.ds || this.ds.getCount() < 1){
            return;
        }

        skipStripe = skipStripe || !this.grid.stripeRows;

        var cursor      = this.rowIndex;
        var rows        = this.getRows();
        var index       = 0;
        var sm          = this.grid.selModel;
        var allSelected = sm.isAllSelected();

        var row = null;
        for (var idx = 0, len = rows.length; idx < len; idx++) {
            row = rows[idx];

            row.rowIndex = index = cursor+idx;
            row.className = row.className.replace(this.rowClsRe, ' ');
            if (!skipStripe && (index + 1) % 2 === 0) {
                row.className += ' x-grid3-row-alt';
            }

            if (paintSelections !== false) {
                if (sm.isSelected(this.ds.getAt(index)) === true) {
                    this.addRowClass(index, this.selectedRowClass);
                } else {
                    this.removeRowClass(index, this.selectedRowClass);
                }
                this.fly(row).removeClass("x-grid3-row-over");
            }
        }

        // add first/last-row classes
        if(cursor === 0){
            Ext.fly(rows[0]).addClass(this.firstRowCls);
        } else if (cursor + rows.length == this.ds.totalLength) {
            Ext.fly(rows[rows.length - 1]).addClass(this.lastRowCls);
        }
    },

    /**
     * API only, since the passed arguments are the indexes in the buffer store.
     * However, the method will try to compute the indexes so they might match
     * the indexes of the records in the underlying data model.
     *
     */
    // private
    insertRows : function(dm, firstRow, lastRow, isUpdate)
    {
        var viewIndexFirst = firstRow + this.ds.bufferRange[0];
        var viewIndexLast  = lastRow  + this.ds.bufferRange[0];

        if (!isUpdate) {
            this.fireEvent("beforerowsinserted", this, viewIndexFirst, viewIndexLast);
        }

        // first off, remove the rows at the bottom of the view to match the
        // visibleRows value and to not cause any spill in the DOM
        if (isUpdate !== true && (this.getRows().length + (lastRow-firstRow)) >= this.visibleRows) {
            this.removeRows((this.visibleRows-1)-(lastRow-firstRow), this.visibleRows-1);
        } else if (isUpdate) {
            this.removeRows(viewIndexFirst-this.rowIndex, viewIndexLast-this.rowIndex);
        }

        // compute the range of possible records which could be drawn into the view without
        // causing any spill
        var lastRenderRow = (firstRow == lastRow)
                          ? lastRow
                          : Math.min(lastRow,  (this.rowIndex-this.ds.bufferRange[0])+(this.visibleRows-1));

        var html = this.renderRows(firstRow, lastRenderRow);

        var before = this.getRow(viewIndexFirst);

        if (before) {
            Ext.DomHelper.insertHtml('beforeBegin', before, html);
        } else {
            Ext.DomHelper.insertHtml('beforeEnd', this.mainBody.dom, html);
        }

        // if a row is replaced, we need to set the row index for this
        // row
        if (isUpdate === true) {
            var rows   = this.getRows();
            var cursor = this.rowIndex;
            for (var i = 0, max_i = rows.length; i < max_i; i++) {
                rows[i].rowIndex = cursor+i;
            }
        }

        if (!isUpdate) {
            this.fireEvent("rowsinserted", this, viewIndexFirst, viewIndexLast, (viewIndexLast-viewIndexFirst)+1);
            this.processRows(0, undefined, true);
        }
    },

    /**
     * Return the <TR> HtmlElement which represents a Grid row for the specified index.
     * The passed argument is assumed to be the absolute index and will get translated
     * to the index of the row that represents the data in the view.
     *
     * @param {Number} index The row index
     *
     * @return {null|HtmlElement} The <TR> element, or null if the row is not rendered
     * in the view.
     */
    getRow : function(row)
    {
        if (row-this.rowIndex < 0) {
            return null;
        }

        return this.getRows()[row-this.rowIndex];
    },

    /**
     * Returns the grid's <TD> HtmlElement at the specified coordinates.
     * Returns null if the specified row is not currently rendered.
     *
     * @param {Number} row The row index in which to find the cell.
     * @param {Number} col The column index of the cell.
     * @return {HtmlElement} The &lt;TD> at the specified coordinates.
     */
    getCell : function(row, col)
    {
        var row = this.getRow(row);

        return row
               ? row.getElementsByTagName('td')[col]
               : null;
    },

    /**
     * Focuses the specified cell.
     * @param {Number} row The row index
     * @param {Number} col The column index
     */
    focusCell : function(row, col, hscroll)
    {
        var xy = this.ensureVisible(row, col, hscroll);

        if (!xy) {
        	return;
		}

		this.focusEl.setXY(xy);

        if(Ext.isGecko){
            this.focusEl.focus();
        }else{
            this.focusEl.focus.defer(1, this.focusEl);
        }

    },

    /**
     * Makes sure that the requested /row/col is visible in the viewport.
     * The method may invoke a request for new buffer data and triggers the
     * scroll-event of the <tt>liveScroller</tt> element.
     *
     */
    // private
    ensureVisible : function(row, col, hscroll)
    {
        if(typeof row != "number"){
            row = row.rowIndex;
        }

        if(row < 0 || row >= this.ds.totalLength){
            return;
        }

        col = (col !== undefined ? col : 0);

        var rowInd = row-this.rowIndex;

        if (this.rowClipped && row == this.rowIndex+this.visibleRows-1) {
            this.adjustScrollerPos(this.rowHeight );
        } else if (row >= this.rowIndex+this.visibleRows) {
            this.adjustScrollerPos(((row-(this.rowIndex+this.visibleRows))+1)*this.rowHeight);
        } else if (row <= this.rowIndex) {
            this.adjustScrollerPos((rowInd)*this.rowHeight);
        }

        var rowEl = this.getRow(row), cellEl;

        if(!rowEl){
            return;
        }

        if(!(hscroll === false && col === 0)){
            while(this.cm.isHidden(col)){
                col++;
            }
            cellEl = this.getCell(row, col);
        }

        var c = this.scroller.dom;

        if(hscroll !== false){
            var cleft = parseInt(cellEl.offsetLeft, 10);
            var cright = cleft + cellEl.offsetWidth;

            var sleft = parseInt(c.scrollLeft, 10);
            var sright = sleft + c.clientWidth;
            if(cleft < sleft){
                c.scrollLeft = cleft;
            }else if(cright > sright){
                c.scrollLeft = cright-c.clientWidth;
            }
        }


        return cellEl ?
            Ext.fly(cellEl).getXY() :
            [c.scrollLeft+this.el.getX(), Ext.fly(rowEl).getY()];
    },

    /**
     * Return strue if the passed record is in the visible rect of this view.
     *
     * @param {Ext.data.Record} record
     *
     * @return {Boolean} true if the record is rendered in the view, otherwise false.
     */
    isRecordRendered : function(record)
    {
        var ind = this.ds.indexOf(record);

        if (ind >= this.rowIndex && ind < this.rowIndex+this.visibleRows) {
            return true;
        }

        return false;
    },

    /**
     * Checks if the passed argument <tt>cursor</tt> lays within a renderable
     * area. The area is renderable, if the sum of cursor and the visibleRows
     * property does not exceed the current upper buffer limit.
     *
     * If this method returns <tt>true</tt>, it's basically save to re-render
     * the view with <tt>cursor</tt> as the absolute position in the model
     * as the first visible row.
     *
     * @param {Number} cursor The absolute position of the row in the data model.
     *
     * @return {Boolean} <tt>true</tt>, if the row can be rendered, otherwise
     *                   <tt>false</tt>
     *
     */
    isInRange : function(rowIndex)
    {
        var lastRowIndex = Math.min(this.ds.totalLength-1,
                                    rowIndex + (this.visibleRows-1));

        return (rowIndex     >= this.ds.bufferRange[0]) &&
               (lastRowIndex <= this.ds.bufferRange[1]);
    },

    /**
     * Calculates the bufferRange start index for a buffer request
     *
     * @param {Boolean} inRange If the index is within the current buffer range
     * @param {Number} index The index to use as a reference for the calculations
     * @param {Boolean} down Wether the calculation was requested when the user scrolls down
     */
    getPredictedBufferIndex : function(index, inRange, down)
    {
        if (!inRange) {
            if (index + this.ds.bufferSize >= this.ds.totalLength) {
                return this.ds.totalLength - this.ds.bufferSize;
            }
            // we need at last to render the index + the visible Rows
            return Math.max(0, (index + this.visibleRows) - Math.round(this.ds.bufferSize/2));
        }
        if (!down) {
            return Math.max(0, (index-this.ds.bufferSize)+this.visibleRows);
        }

        if (down) {
            return Math.max(0, Math.min(index, this.ds.totalLength-this.ds.bufferSize));
        }
    },


    /**
     * Updates the table view. Removes/appends rows as needed and fetches the
     * cells content out of the available store. If the needed rows are not within
     * the buffer, the method will advise the store to update it's contents.
     *
     * The method puts the requested cursor into the queue if a previously called
     * buffering is in process.
     *
     * @param {Number} cursor The row's position, absolute to it's position in the
     *                        data model
     *
     */
    updateLiveRows: function(index, forceRepaint, forceReload)
    {
        var inRange = this.isInRange(index);

        if (this.isBuffering) {
            if (this.isPrebuffering) {
                if (inRange) {
                    this.replaceLiveRows(index, forceRepaint);
                } else {
                    this.showLoadMask(true);
                }
            }

            this.fireEvent('cursormove', this, index,
                           Math.min(this.ds.totalLength,
                           this.visibleRows-this.rowClipped),
                           this.ds.totalLength);

            this.requestQueue = index;
            return;
        }

        var lastIndex  = this.lastIndex;
        this.lastIndex = index;
        var inRange    = this.isInRange(index);

        var down = false;

        if (inRange && forceReload !== true) {

            // repaint the table's view
            this.replaceLiveRows(index, forceRepaint);
            // has to be called AFTER the rowIndex was recalculated
            this.fireEvent('cursormove', this, index,
                       Math.min(this.ds.totalLength,
                       this.visibleRows-this.rowClipped),
                       this.ds.totalLength);
            // lets decide if we can void this method or stay in here for
            // requesting a buffer update
            if (index > lastIndex) { // scrolling down

                down = true;
                var totalCount = this.ds.totalLength;

                // while scrolling, we have not yet reached the row index
                // that would trigger a re-buffer
                if (index+this.visibleRows+this.nearLimit <= this.ds.bufferRange[1]) {
                    return;
                }

                // If we have already buffered the last range we can ever get
                // by the queried data repository, we don't need to buffer again.
                // This basically means that a re-buffer would only occur again
                // if we are scrolling up.
                if (this.ds.bufferRange[1]+1 >= totalCount) {
                    return;
                }
            } else if (index < lastIndex) { // scrolling up

                down = false;
                // We are scrolling up in the first buffer range we can ever get
                // Re-buffering would only occur upon scrolling down.
                if (this.ds.bufferRange[0] <= 0) {
                    return;
                }

                // if we are scrolling up and we are moving in an acceptable
                // buffer range, lets return.
                if (index - this.nearLimit > this.ds.bufferRange[0]) {
                    return;
                }
            } else {
                return;
            }

            this.isPrebuffering = true;
        }

        // prepare for rebuffering
        this.isBuffering = true;

        var bufferOffset = this.getPredictedBufferIndex(index, inRange, down);

        if (!inRange) {
            this.showLoadMask(true);
        }

        this.ds.suspendEvents();
        var sInfo  = this.ds.sortInfo;

        var params = {};
        if (this.ds.lastOptions) {
            Ext.apply(params, this.ds.lastOptions.params);
        }

        var pn = this.ds.paramNames;

        params[pn.start] = bufferOffset;
        params[pn.limit] = this.ds.bufferSize;

        if (sInfo) {
            params[pn.dir]  = sInfo.direction;
            params[pn.sort] = sInfo.field;
        }

        var opts = {
            forceRepaint     : forceRepaint,
            callback         : this.liveBufferUpdate,
            scope            : this,
            params           : params,
            suspendLoadEvent : true
        };

        this.fireEvent('beforebuffer', this, this.ds, index,
            Math.min(this.ds.totalLength, this.visibleRows-this.rowClipped),
            this.ds.totalLength, opts
        );

        this.ds.load(opts);
        this.ds.resumeEvents();
    },

    /**
     * Shows this' view own load mask to indicate that a large amount of buffer
     * data was requested by the store.
     * @param {Boolean} show <tt>true</tt> to show the load mask, otherwise
     *                       <tt>false</tt>
     */
    showLoadMask : function(show)
    {
        if (!this.loadMask || show == this.loadMaskDisplayed) {
            return;
        }

        var dom  = this._loadMaskAnchor.dom;
        var data = Ext.Element.data;

        var mask    = data(dom, 'mask');
        var maskMsg = data(dom, 'maskMsg');

        if (show) {
            mask.setDisplayed(true);
            maskMsg.setDisplayed(true);
            maskMsg.center(this._loadMaskAnchor);
            // this lines will help IE8 to re-calculate the height of the loadmask
            if(Ext.isIE && !(Ext.isIE7 && Ext.isStrict) && this._loadMaskAnchor.getStyle('height') == 'auto'){
	            mask.setSize(undefined, this._loadMaskAnchor.getHeight());
	        }
        } else {
            mask.setDisplayed(false);
            maskMsg.setDisplayed(false);
        }

        this.loadMaskDisplayed = show;
    },

    /**
     * Renders the table body with the contents of the model. The method will
     * prepend/ append rows after removing from either the end or the beginning
     * of the table DOM to reduce expensive DOM calls.
     * It will also take care of rendering the rows selected, taking the property
     * <tt>bufferedSelections</tt> of the {@link BufferedRowSelectionModel} into
     * account.
     * Instead of calling this method directly, the <tt>updateLiveRows</tt> method
     * should be called which takes care of rebuffering if needed, since this method
     * will behave erroneous if data of the buffer is requested which may not be
     * available.
     *
     * @param {Number} cursor The position of the data in the model to start
     *                        rendering.
     *
     * @param {Boolean} forceReplace <tt>true</tt> for recomputing the DOM in the
     *                               view, otherwise <tt>false</tt>.
     */
    // private
    replaceLiveRows : function(cursor, forceReplace, processRows)
    {
        var spill = cursor-this.lastRowIndex;

        if (spill == 0 && forceReplace !== true) {
            return;
        }

        // decide wether to prepend or append rows
        // if spill is negative, we are scrolling up. Thus we have to prepend
        // rows. If spill is positive, we have to append the buffers data.
        var append = spill > 0;

        // abs spill for simplyfiying append/prepend calculations
        spill = Math.abs(spill);

        // adjust cursor to the buffered model index
        var bufferRange = this.ds.bufferRange;
        var cursorBuffer = cursor-bufferRange[0];

        // compute the last possible renderindex
        var lpIndex = Math.min(cursorBuffer+this.visibleRows-1, bufferRange[1]-bufferRange[0]);
        // we can skip checking for append or prepend if the spill is larger than
        // visibleRows. We can paint the whole rows new then-
        if (spill >= this.visibleRows || spill == 0) {
            this.mainBody.update(this.renderRows(cursorBuffer, lpIndex));
        } else {
            if (append) {

                this.removeRows(0, spill-1);

                if (cursorBuffer+this.visibleRows-spill <= bufferRange[1]-bufferRange[0]) {
                    var html = this.renderRows(
                        cursorBuffer+this.visibleRows-spill,
                        lpIndex
                    );
                    Ext.DomHelper.insertHtml('beforeEnd', this.mainBody.dom, html);

                }

            } else {
                this.removeRows(this.visibleRows-spill, this.visibleRows-1);
                var html = this.renderRows(cursorBuffer, cursorBuffer+spill-1);
                Ext.DomHelper.insertHtml('beforeBegin', this.mainBody.dom.firstChild, html);

            }
        }

        if (processRows !== false) {
            this.processRows(0, undefined, true);
        }
        this.lastRowIndex = cursor;
    },



    /**
    * Adjusts the scroller height to make sure each row in the dataset will be
    * can be displayed, no matter which value the current height of the grid
    * component equals to.
    */
    // protected
    adjustBufferInset : function()
    {
        var liveScrollerDom = this.liveScroller.dom;
        var g = this.grid, ds = g.store;
        var c  = g.getGridEl();
        var elWidth = c.getSize().width;

        // hidden rows is the number of rows which cannot be
        // displayed and for which a scrollbar needs to be
        // rendered. This does also take clipped rows into account
        var hiddenRows = (ds.totalLength == this.visibleRows-this.rowClipped)
                       ? 0
                       : Math.max(0, ds.totalLength-(this.visibleRows-this.rowClipped));

        if (hiddenRows == 0) {
            this.scroller.setWidth(elWidth);
            liveScrollerDom.style.display = 'none';
            return;
        } else {
            this.scroller.setWidth(elWidth-this.getScrollOffset());
            liveScrollerDom.style.display = '';
        }

        var scrollbar = this.cm.getTotalWidth()+this.getScrollOffset() > elWidth;

        // adjust the height of the scrollbar
        var contHeight = liveScrollerDom.parentNode.offsetHeight +
                         ((ds.totalLength > 0 && scrollbar)
                         ? - this.horizontalScrollOffset
                         : 0)
                         - this.hdHeight;

        liveScrollerDom.style.height = Math.max(contHeight, this.horizontalScrollOffset*2)+"px";

        if (this.rowHeight == -1) {
            return;
        }

        var h  = (hiddenRows == 0 ? 0 : contHeight+(hiddenRows*this.rowHeight));
        var oh = h;
        var len = this.liveScrollerInsets.length;

        if (h == 0) {
            h = 0;
        } else {
            h = Math.round(h/len);
        }

        for (var i = 0; i < len; i++) {
            if (i == len-1 && h != 0) {
                h -= (h*3)-oh;
            }
            this.liveScrollerInsets[i].style.height = h+"px";
        }
    },

    /**
     * Recomputes the number of visible rows in the table based upon the height
     * of the component. The method adjusts the <tt>rowIndex</tt> property as
     * needed, if the sum of visible rows and the current row index exceeds the
     * number of total data available.
     */
    // protected
    adjustVisibleRows : function()
    {
        if (this.rowHeight == -1) {
            if (this.getRows()[0]) {
                this.rowHeight = this.getRows()[0].offsetHeight;

                if (this.rowHeight <= 0) {
                    this.rowHeight = -1;
                    return;
                }

            } else {
                return;
            }
        }


        var g = this.grid, ds = g.store;

        var c     = g.getGridEl();
        var cm    = this.cm;
        var size  = c.getSize();
        var width = size.width;
        var vh    = size.height;

        var vw = width-this.getScrollOffset();
        // horizontal scrollbar shown?
        if (cm.getTotalWidth() > vw) {
            // yes!
            vh -= this.horizontalScrollOffset;
        }

        vh -= this.mainHd.getHeight();

        var totalLength = ds.totalLength || 0;

        var visibleRows = Math.max(1, Math.floor(vh/this.rowHeight));

        this.rowClipped = 0;
        // only compute the clipped row if the total length of records
        // exceeds the number of visible rows displayable
        if (totalLength > visibleRows && this.rowHeight / 3 < (vh - (visibleRows*this.rowHeight))) {
            visibleRows = Math.min(visibleRows+1, totalLength);
            this.rowClipped = 1;
        }

        // if visibleRows   didn't change, simply void and return.
        if (this.visibleRows == visibleRows) {
            return;
        }

        this.visibleRows = visibleRows;

        // skip recalculating the row index if we are currently buffering, but not if we
        // are just pre-buffering
        if (this.isBuffering && !this.isPrebuffering) {
            return;
        }

        // when re-rendering, doe not take the clipped row into account
        if (this.rowIndex + (visibleRows-this.rowClipped) > totalLength) {
            this.rowIndex     = Math.max(0, totalLength-(visibleRows-this.rowClipped));
            this.lastRowIndex = this.rowIndex;
        }

        this.updateLiveRows(this.rowIndex, true);
    },


    adjustScrollerPos : function(pixels, suspendEvent)
    {
        if (pixels == 0) {
            return;
        }
        var liveScroller = this.liveScroller;
        var scrollDom    = liveScroller.dom;

        if (suspendEvent === true) {
            liveScroller.un('scroll', this.onLiveScroll, this);
        }
        this.lastScrollPos   = scrollDom.scrollTop;
        scrollDom.scrollTop += pixels;

        if (suspendEvent === true) {
            scrollDom.scrollTop = scrollDom.scrollTop;
            liveScroller.on('scroll', this.onLiveScroll, this, {buffer : this.scrollDelay});
        }

    }



});/**
 * Ext.ux.grid.livegrid.JsonReader
 * Copyright (c) 2007-2012, http://www.siteartwork.de
 *
 * Ext.ux.grid.livegrid.JsonReader is licensed under the terms of the
 *                  GNU Open Source GPL 3.0
 * license.
 *
 * Commercial use is prohibited. Visit <http://ext-livegrid.com>
 * if you need to obtain a commercial license.
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 */

Ext.namespace('Ext.ux.grid.livegrid');

/**
 * @class Ext.ux.grid.livegrid.JsonReader
 * @extends Ext.data.JsonReader
 * @constructor
 * @param {Object} config
 *
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 */
Ext.ux.grid.livegrid.JsonReader = function(meta, recordType){

    Ext.ux.grid.livegrid.JsonReader.superclass.constructor.call(this, meta, recordType);
};


Ext.extend(Ext.ux.grid.livegrid.JsonReader, Ext.data.JsonReader, {

    /**
     * @cfg {String} versionProperty Name of the property from which to retrieve the
     *                               version of the data repository this reader parses
     *                               the reponse from
     */

    buildExtractors : function()
    {
        if(this.ef){
            return;
        }

        var s = this.meta;

        if(s.versionProperty) {
            this.getVersion = this.createAccessor(s.versionProperty);
        }

        Ext.ux.grid.livegrid.JsonReader.superclass.buildExtractors.call(this);
    },

    /**
     * Create a data block containing Ext.data.Records from a JSON object.
     * @param {Object} o An object which contains an Array of row objects in the property specified
     * in the config as 'root, and optionally a property, specified in the config as 'totalProperty'
     * which contains the total size of the dataset.
     * @return {Object} data A data block which is used by an Ext.data.Store object as
     * a cache of Ext.data.Records.
     */
    readRecords : function(o)
    {
        // shorten for future calls
        if (!this.__readRecords) {
            this.__readRecords = Ext.ux.grid.livegrid.JsonReader.superclass.readRecords;
        }
        var intercept = this.__readRecords.call(this, o);

        if (this.meta.versionProperty) {
            var v = this.getVersion(o);
            intercept.version = (v === undefined || v === "") ? null : v;
        }

        return intercept;
    }

});/**
 * Ext.ux.grid.livegrid.RowSelectionModel
 * Copyright (c) 2007-2012, http://www.siteartwork.de
 *
 * Ext.ux.grid.livegrid.RowSelectionModel is licensed under the terms of the
 *                  GNU Open Source GPL 3.0
 * license.
 *
 * Commercial use is prohibited. Visit <http://ext-livegrid.com>
 * if you need to obtain a commercial license.
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 */

Ext.namespace('Ext.ux.grid.livegrid');

/**
 * @class Ext.ux.grid.livegrid.RowSelectionModel
 * @extends Ext.grid.RowSelectionModel
 * @constructor
 * @param {Object} config
 *
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 */
Ext.ux.grid.livegrid.RowSelectionModel = function(config) {


    this.addEvents({
        /**
         * The selection dirty event will be triggered in case records were
         * inserted/ removed at view indexes that may affect the current
         * selection ranges which are only represented by view indexes, but not
         * current record-ids
         */
        'selectiondirty' : true
    });

    Ext.apply(this, config);

    this.allSelected = false;
    this.excludes    = [];

    this.pendingSelections = {};

    Ext.ux.grid.livegrid.RowSelectionModel.superclass.constructor.call(this);

};

Ext.extend(Ext.ux.grid.livegrid.RowSelectionModel, Ext.grid.RowSelectionModel, {


 // private
    initEvents : function()
    {
        Ext.ux.grid.livegrid.RowSelectionModel.superclass.initEvents.call(this);

        var grid  = this.grid,
            view  = grid.view,
            store = grid.store;

        view.on('rowsinserted',    this.onAdd,            this);
        store.on('selectionsload', this.onSelectionsLoad, this);
        store.on('load',           this.onStoreLoad,      this);
    },

    /**
     * Listener for the store's load event.
     *
     * @param {Ext.data.Store} store
     * @param {Array} records
     * @param {Object} options
     *
     */
    onStoreLoad : function(store, records, options)
    {
        this.replaceSelections(records);
    },

    /**
     * Callback is called when a row gets removed in the view. The process to
     * invoke this method is as follows:
     *
     * <ul>
     *  <li>1. store.remove(record);</li>
     *  <li>2. view.onRemove(store, record, indexInStore, isUpdate)<br />
     *   [view triggers rowremoved event]</li>
     *  <li>3. this.onRemove(view, indexInStore, record)</li>
     * </ul>
     *
     * If r defaults to <tt>null</tt> and index is within the pending selections
     * range, the selectionchange event will be called, too.
     * Additionally, the method will shift all selections and trigger the
     * selectiondirty event if any selections are pending.
     *
     */
    onRemove : function(v, index, r)
    {
        var ranges           = this.getPendingSelections();
        var rangesLength     = ranges.length;
        var selectionChanged = false;

        // if index equals to Number.MIN_VALUE or Number.MAX_VALUE, mark current
        // pending selections as dirty
        if (index == Number.MIN_VALUE || index == Number.MAX_VALUE) {

            if (r) {
                // if the record is part of the current selection, shift the selection down by 1
                // if the index equals to Number.MIN_VALUE
                if (this.isIdSelected(r.id) && index == Number.MIN_VALUE) {
                    // bufferRange already counted down when this method gets
                    // called
                    this.shiftSelections(this.grid.store.bufferRange[1], -1);
                }
                this.selections.remove(r);
                selectionChanged = true;
            }

            // clear all pending selections that are behind the first
            // bufferrange, and shift all pending Selections that lay in front
            // front of the second bufferRange down by 1!
            if (index == Number.MIN_VALUE) {
                this.clearPendingSelections(0, this.grid.store.bufferRange[0]);
            } else {
                // clear pending selections that are in front of bufferRange[1]
                this.clearPendingSelections(this.grid.store.bufferRange[1]);
            }

            // only fire the selectiondirty event if there were pendning ranges
            if (rangesLength != 0) {
                this.fireEvent('selectiondirty', this, index, 1);
            }

        } else {

            selectionChanged = this.isIdSelected(r.id);

            // if the record was not part of the selection, return
            if (!selectionChanged) {
                return;
            }

            this.selections.remove(r);
            //this.last = false;
            // if there are currently pending selections, look up the interval
            // to tell whether removing the record would mark the selection dirty
            if (rangesLength != 0) {

                var startRange = ranges[0];
                var endRange   = ranges[rangesLength-1];
                if (index <= endRange || index <= startRange) {
                    this.shiftSelections(index, -1);
                    this.fireEvent('selectiondirty', this, index, 1);
                }
             }

        }

        if (selectionChanged) {
            this.fireEvent('selectionchange', this);
        }
    },


    /**
     * If records where added to the store, this method will work as a callback,
     * called by the views' rowsinserted event.
     * Selections will be shifted down if, and only if, the listeners for the
     * selectiondirty event will return <tt>true</tt>.
     *
     */
    onAdd : function(store, index, endIndex, recordLength)
    {
        var ranges       = this.getPendingSelections();
        var rangesLength = ranges.length;

        // if index equals to Number.MIN_VALUE or Number.MAX_VALUE, mark current
        // pending selections as dirty
        if ((index == Number.MIN_VALUE || index == Number.MAX_VALUE)) {

            if (index == Number.MIN_VALUE) {
                // bufferRange already counted down when this method gets
                // called
                this.clearPendingSelections(0, this.grid.store.bufferRange[0]);
                this.shiftSelections(this.grid.store.bufferRange[1], recordLength);
            } else {
                this.clearPendingSelections(this.grid.store.bufferRange[1]);
            }

            // only fire the selectiondirty event if there were pendning ranges
            if (rangesLength != 0) {
                this.fireEvent('selectiondirty', this, index, r);
            }

            return;
        }

        // it is safe to say that the selection is dirty when the inserted index
        // is less or equal to the first selection range index or less or equal
        // to the last selection range index
        var startRange = ranges[0];
        var endRange   = ranges[rangesLength-1];
        var viewIndex  = index;
        if (viewIndex <= endRange || viewIndex <= startRange) {
            this.fireEvent('selectiondirty', this, viewIndex, recordLength);
            this.shiftSelections(viewIndex, recordLength);
        }
    },



    /**
     * Shifts current/pending selections. This method can be used when rows where
     * inserted/removed and the selection model has to synchronize itself.
     */
    shiftSelections : function(startRow, length)
    {
        var index         = 0;
        var newIndex      = 0;
        var newRequests   = {};

        var ds            = this.grid.store;
        var storeIndex    = startRow-ds.bufferRange[0];
        var newStoreIndex = 0;
        var totalLength   = this.grid.store.totalLength;
        var rec           = null;

        //this.last = false;

        var ranges       = this.getPendingSelections();
        var rangesLength = ranges.length;

        if (rangesLength == 0) {
            return;
        }

        for (var i = 0; i < rangesLength; i++) {
            index = ranges[i];

            if (index < startRow) {
                continue;
            }

            newIndex      = index+length;
            newStoreIndex = storeIndex+length;
            if (newIndex >= totalLength) {
                break;
            }

            rec = ds.getAt(newStoreIndex);
            if (rec) {
                this.selections.add(rec);
            } else {
                newRequests[newIndex] = true;
            }
        }

        this.pendingSelections = newRequests;
    },

    /**
     *
     * @param {Array} records The records that have been loaded
     * @param {Array} ranges  An array representing the model index ranges the
     *                        reords have been loaded for.
     */
    onSelectionsLoad : function(store, records, ranges)
    {
        this.replaceSelections(records);
    },

    /**
     * Returns true if there is a next record to select
     * @return {Boolean}
     */
    hasNext : function()
    {
        return this.last !== false && (this.last+1) < this.grid.store.getTotalCount();
    },

    /**
     * Gets the number of selected rows.
     * @return {Number}
     */
    getCount : function()
    {
        return this.selections.length + this.getPendingSelections().length;
    },

    /**
     * Returns True if the specified row is selected.
     *
     * @param {Number/Record} record The record or index of the record to check
     * @return {Boolean}
     */
    isSelected : function(index)
    {
        if (typeof index == "number") {
            var orgInd = index;
            index = this.grid.store.getAt(orgInd);
            if (!index) {
                var ind = this.getPendingSelections().indexOf(orgInd);
                if (ind != -1) {
                    return true;
                }

                return false;
            }
        }

        var r = index;

        if (this.allSelected && !this.excludes[r.id]) {
            return true;
        }

        return (r && this.selections.key(r.id) ? true : false);
    },


    /**
     * Deselects a record.
     * The emthod assumes that the record is physically available, i.e.
     * pendingSelections will not be taken into account
     */
    deselectRecord : function(record, preventViewNotify)
    {
        if(this.locked) {
            return;
        }

        var isSelected = this.selections.key(record.id);

        if (!isSelected) {
            return;
        }

        if (this.allSelected) {
            this.excludes[record.id] = true;
        }

        var store = this.grid.store;
        var index = store.indexOfId(record.id);

        if (index == -1) {
            index = store.findInsertIndex(record);
            if (index != Number.MIN_VALUE && index != Number.MAX_VALUE) {
                index += store.bufferRange[0];
            }
        } else {
            // just to make sure, though this should not be
            // set if the record was availablein the selections
            delete this.pendingSelections[index];
        }

        if (this.last == index) {
            this.last = false;
        }

        if (this.lastActive == index) {
            this.lastActive = false;
        }

        this.selections.remove(record);

        if(!preventViewNotify){
            this.grid.getView().onRowDeselect(index);
        }

        this.fireEvent("rowdeselect", this, index, record);
        this.fireEvent("selectionchange", this);
    },

    /**
     * Deselects a row.
     * @param {Number} row The index of the row to deselect
     */
    deselectRow : function(index, preventViewNotify)
    {
        if(this.locked) return;
        if(this.last == index){
            this.last = false;
        }

        if(this.lastActive == index){
            this.lastActive = false;
        }
        var r = this.grid.store.getAt(index);

        delete this.pendingSelections[index];

        if (r) {
            if (this.allSelected) {
                this.excludes[r.id] = true;
            }
            this.selections.remove(r);
        }
        if(!preventViewNotify){
            this.grid.getView().onRowDeselect(index);
        }
        this.fireEvent("rowdeselect", this, index, r);
        this.fireEvent("selectionchange", this);
    },


    /**
     * Selects a row.
     * @param {Number} row The index of the row to select
     * @param {Boolean} keepExisting (optional) True to keep existing selections
     */
    selectRow : function(index, keepExisting, preventViewNotify)
    {
        if(//this.last === index
           //||
           this.locked
           || index < 0
           || index >= this.grid.store.getTotalCount()) {
            return;
        }

        var r = this.grid.store.getAt(index);

        if(this.fireEvent("beforerowselect", this, index, keepExisting, r) !== false){
            if(!keepExisting || this.singleSelect){
                this.clearSelections();
            }

            if (r) {
                this.selections.add(r);
                delete this.pendingSelections[index];
                delete this.excludes[r.id];
            } else {
                this.pendingSelections[index] = true;
            }

            this.last = this.lastActive = index;

            if(!preventViewNotify){
                this.grid.getView().onRowSelect(index);
            }

            this.fireEvent("rowselect", this, index, r);
            this.fireEvent("selectionchange", this);
        }
    },

    clearPendingSelections : function(startIndex, endIndex)
    {
        if (endIndex == undefined) {
            endIndex = Number.MAX_VALUE;
        }

        var newSelections = {};

        var ranges       = this.getPendingSelections();
        var rangesLength = ranges.length;

        var index = 0;

        for (var i = 0; i < rangesLength; i++) {
            index = ranges[i];
            if (index <= endIndex && index >= startIndex) {
                continue;
            }

            newSelections[index] = true;
        }

        this.pendingSelections = newSelections;
    },

    /**
     * Replaces already set data with new data from the store if those
     * records can be found within this.selections or this.pendingSelections
     *
     * @param {Array} An array with records buffered by the store
     */
    replaceSelections : function(records)
    {
        if (!records || records.length == 0) {
            return;
        }

        var ds  = this.grid.store;
        var rec = null;

        var assigned     = [];
        var ranges       = this.getPendingSelections();
        var rangesLength = ranges.length

        var selections = this.selections;
        var index      = 0;

        for (var i = 0; i < rangesLength; i++) {
            index = ranges[i];
            rec   = ds.getAt(index);
            if (rec) {
                selections.add(rec);
                assigned.push(rec.id);
                delete this.pendingSelections[index];
            }
        }

        var id  = null;
        for (i = 0, len = records.length; i < len; i++) {
            rec = records[i];
            id  = rec.id;
            if (assigned.indexOf(id) == -1 && selections.containsKey(id)) {
                selections.add(rec);
            }
        }

    },

    getPendingSelections : function(asRange)
    {
        var index         = 1;
        var ranges        = [];
        var currentRange  = 0;
        var tmpArray      = [];

        for (var i in this.pendingSelections) {
            tmpArray.push(parseInt(i));
        }

        tmpArray.sort(function(o1,o2){
            if (o1 > o2) {
                return 1;
            } else if (o1 < o2) {
                return -1;
            } else {
                return 0;
            }
        });

        if (!asRange) {
            return tmpArray;
        }

        var max_i = tmpArray.length;

        if (max_i == 0) {
            return [];
        }

        ranges[currentRange] = [tmpArray[0], tmpArray[0]];
        for (var i = 0, max_i = max_i-1; i < max_i; i++) {
            if (tmpArray[i+1] - tmpArray[i] == 1) {
                ranges[currentRange][1] = tmpArray[i+1];
            } else {
                currentRange++;
                ranges[currentRange] = [tmpArray[i+1], tmpArray[i+1]];
            }
        }

        return ranges;
    },

    /**
     * Clears all selections.
     */
    clearSelections : function(fast)
    {
        if(this.locked) return;
        if(fast !== true){
            var ds  = this.grid.store;
            var s   = this.selections;
            var ind = -1;
            s.each(function(r){
                ind = ds.indexOfId(r.id);
                if (ind != -1) {
                    this.deselectRow(ind+ds.bufferRange[0]);
                }
            }, this);
            s.clear();

            this.pendingSelections = {};

        }else{
            this.selections.clear();
            this.pendingSelections    = {};
        }
        this.last = false;
        this.allSelected = false;
        this.excludes = [];
    },


    /**
     * Selects a range of rows. All rows in between startRow and endRow are also
     * selected.
     *
     * @param {Number} startRow The index of the first row in the range
     * @param {Number} endRow The index of the last row in the range
     * @param {Boolean} keepExisting (optional) True to retain existing selections
     */
    selectRange : function(startRow, endRow, keepExisting)
    {
        if(this.locked) {
            return;
        }

        if(!keepExisting) {
            this.clearSelections();
        }

        if (startRow <= endRow) {
            for(var i = startRow; i <= endRow; i++) {
                this.selectRow(i, true);
            }
        } else {
            for(var i = startRow; i >= endRow; i--) {
                this.selectRow(i, true);
            }
        }

    },

    /**
     * Returns true if all rows available are selected.
     */
    isAllSelected : function()
    {
        return this.allSelected;
    },

    /**
     * Selects all rows if the selection model
     * {@link Ext.grid.AbstractSelectionModel#isLocked is not locked}.
     */
    selectAll : function()
    {
        if(this.isLocked()){
            return;
        }

        this.excludes = [];
        this.selectRange(0, this.grid.store.getTotalCount(), false);
        this.allSelected = true;
    },

    /**
     * Returns an object with all records currently being excluded,
     * whereas the key is the id of the record, and it's value is
     * set to boolean true.
     */
    getExcludes : function()
    {
        return this.excludes;
    }



});


/**
 * Ext.ux.grid.livegrid.Store
 * Copyright (c) 2007-2012, http://www.siteartwork.de
 *
 * Ext.ux.grid.livegrid.Store is licensed under the terms of the
 *                  GNU Open Source GPL 3.0
 * license.
 *
 * Commercial use is prohibited. Visit <http://ext-livegrid.com>
 * if you need to obtain a commercial license.
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 */

Ext.namespace('Ext.ux.grid.livegrid');

/**
 * @class Ext.ux.grid.livegrid.Store
 * @extends Ext.data.Store
 *
 * The BufferedGridSore is a special implementation of a Ext.data.Store. It is used
 * for loading chunks of data from the underlying data repository as requested
 * by the Ext.ux.BufferedGridView. It's size is limited to the config parameter
 * bufferSize and is thereby guaranteed to never hold more than this amount
 * of records in the store.
 *
 * Requesting selection ranges:
 * ----------------------------
 * This store implementation has 2 Http-proxies: A data proxy for requesting data
 * from the server for displaying and another proxy to request pending selections:
 * Pending selections are represented by row indexes which have been selected but
 * which records have not yet been available in the store. The loadSelections method
 * will initiate a request to the data repository (same url as specified in the
 * url config parameter for the store) to fetch the pending selections. The additional
 * parameter send to the server is the "ranges" parameter, which will hold a json
 * encoded string representing ranges of row indexes to load from the data repository.
 * As an example, pending selections with the indexes 1,2,3,4,5,9,10,11,16 would
 * have to be translated to [1,5],[9,11],[16].
 * Please note, that by indexes we do not understand (primary) keys of the data,
 * but indexes as represented by the view. To get the ranges of pending selections,
 * you can use the getPendingSelections method of the BufferedRowSelectionModel, which
 * should be used as the default selection model of the grid.
 *
 * Version-property:
 * -----------------
 * This implementation does also introduce a new member called "version". The version
 * property will help you in determining if any pending selections indexes are still
 * valid or may have changed. This is needed to reduce the danger of data inconsitence
 * when you are requesting data from the server: As an example, a range of indexes must
 * be read from the server but may have been become invalid when the row represented
 * by the index is no longer available in teh underlying data store, caused by a
 * delete or insert operation. Thus, you have to take care of the version property
 * by yourself (server side) and change this value whenever a row was deleted or
 * inserted. You can specify the path to the version property in the BufferedJsonReader,
 * which should be used as the default reader for this store. If the store recognizes
 * a version change, it will fire the versionchange event. It is up to the user
 * to remove all selections which are pending, or use them anyway.
 *
 * Inserting data:
 * ---------------
 * Another thing to notice is the way a user inserts records into the data store.
 * A user should always provide a sortInfo for the grid, so the findInsertIndex
 * method can return a value that comes close to the value as it would have been
 * computed by the underlying store's sort algorithm. Whenever a record should be
 * added to the store, the insert index should be calculated and the used as the
 * parameter for the insert method. The findInsertIndex method will return a value
 * that equals to Number.MIN_VALUE or Number.MAX_VALUE if the added record would not
 * change the current state of the store. If that happens, this data is not available
 * in the store, and may be requested later on when a new request for new data is made.
 *
 * Sorting:
 * --------
 * remoteSort will always be set to true, no matter what value the user provides
 * using the config object.
 *
 * @constructor
 * Creates a new Store.
 * @param {Object} config A config object containing the objects needed for the Store to access data,
 * and read the data into Records.
 *
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 */
Ext.ux.grid.livegrid.Store = function(config) {

    config = config || {};

    // remoteSort will always be set to true.
    config.remoteSort = true;

    // we will intercept the autoLoad property and set it to false so we do not
    // load any contents of the store before the View has not fully initialized
    // itself. if autoLoad was set to true, the Ext.ux.grid.livegrid.GridPanel
    // will take care of loading the store once it has been rendered
    this._autoLoad  = config.autoLoad ? true : false;
    config.autoLoad = false;

    this.addEvents(
         /**
          * @event bulkremove
          * Fires when a bulk remove operation was finished.
          * @param {Ext.ux.BufferedGridStore} this
          * @param {Array} An array with the records that have been removed.
          * The values for each array index are
          * record - the record that was removed
          * index - the index of the removed record in the store
          */
        'bulkremove',
         /**
          * @event versionchange
          * Fires when the version property has changed.
          * @param {Ext.ux.BufferedGridStore} this
          * @param {String} oldValue
          * @param {String} newValue
          */
        'versionchange',
         /**
          * @event beforeselectionsload
          * Fires before the store sends a request for ranges of records to
          * the server.
          * @param {Ext.ux.BufferedGridStore} this
          * @param {Array} ranges
          */
        'beforeselectionsload',
         /**
          * @event selectionsload
          * Fires when selections have been loaded.
          * @param {Ext.ux.BufferedGridStore} this
          * @param {Array} records An array containing the loaded records from
          * the server.
          * @param {Array} ranges An array containing the ranges of indexes this
          * records may represent.
          */
        'selectionsload',
        /**
         * @event insertindexfound
         * Fires when the store's find insertInsertIndex method found an insert index
         * This is for interested listeners to be able to adjust the insert index.
         * @param {Ext.ux.grid.livegrid.Store} this
         * @param {Object} an object containing information about the insert index found
         *                 the insert index is available from the property "insertIndex"
         *
         */
         'insertindexfound'
    );

    Ext.ux.grid.livegrid.Store.superclass.constructor.call(this, config);

    this.totalLength = 0;


    /**
     * The array represents the range of rows available in the buffer absolute to
     * the indexes of the data model. Initialized with  [-1, -1] which tells that no
     * records are currrently buffered
     * @param {Array}
     */
    this.bufferRange = [-1, -1];

    this.on('clear', function (){
        this.bufferRange = [-1, -1];
    }, this);

    if(this.url && !this.selectionsProxy){
        this.selectionsProxy = new Ext.data.HttpProxy({url: this.url});
    }

};

Ext.extend(Ext.ux.grid.livegrid.Store, Ext.data.Store, {

    /**
     * The version of the data in the store. This value is represented by the
     * versionProperty-property of the BufferedJsonReader.
     * @property
     */
    version : null,

    /**
     * Tells whether the dataset loaded is the last dataset, and if the
     * specified index represents the last record in the dataset
     *
     * @param {Number} index
     *
     * @return {Boolean}
     */
    isLastSet : function(index)
    {
        return index == this.bufferSize && this.isAllLoaded();
    },

    /**
     * Returns true if the loaded dataset represents the last dataset
     * which can be loaded, otherwise false.
     *
     * @return {Boolean}
     */
    isAllLoaded : function()
    {
        return (this.totalLength-1 == this.bufferRange[1]);
    },



    /**
     * Inserts a record at the position as specified in index.
     * If the index equals to Number.MIN_VALUE or Number.MAX_VALUE, the record will
     * not be added to the store, but still fire the add-event to indicate that
     * the set of data in the underlying store has been changed.
     * If the index equals to 0 and the length of data in the store equals to
     * bufferSize, the add-event will be triggered with Number.MIN_VALUE to
     * indicate that a record has been prepended. If the index equals to
     * bufferSize, the method will assume that the record has been appended and
     * trigger the add event with index set to Number.MAX_VALUE.
     *
     * Note:
     * -----
     * The index parameter is not a view index, but a value in the range of
     * [0, this.bufferSize].
     *
     * You are strongly advised to not use this method directly. Instead, call
     * findInsertIndex wirst and use the return-value as the first parameter for
     * for this method.
     */
    insert : function(index, records)
    {
        var isLastSet   = this.isLastSet(index),
            isAllLoaded = this.isAllLoaded();

        // hooray for haskell!
        records = [].concat(records);

        index = index >= this.bufferSize
                ? (isLastSet ? index : Number.MAX_VALUE)
                : index;

        if (index == Number.MIN_VALUE || index == Number.MAX_VALUE) {
            var l = records.length;
            if (index == Number.MIN_VALUE) {
                this.bufferRange[0] += l;
                this.bufferRange[1] += l;
            }

            this.totalLength += l;
            this.fireEvent("add", this, records, index);
            return;
        }

        var split = false;
        var insertRecords = records;

        if (!isAllLoaded && !isLastSet
            && (records.length + index >= this.bufferSize)) {
            split = true;
            insertRecords = records.splice(0, this.bufferSize-index)
        }
        this.totalLength += insertRecords.length;

        // if the store was loaded without data and the bufferRange
        // has to be filled first
        if (this.bufferRange[0] <= -1) {
            this.bufferRange[0] = 0;
        }
        if (this.bufferRange[1] < (this.bufferSize-1)) {
            this.bufferRange[1] = Math.min(
                this.bufferRange[1] +
                insertRecords.length, this.bufferSize-1
            );
        }

        for (var i = 0, len = insertRecords.length; i < len; i++) {
            this.data.insert(index, insertRecords[i]);
            insertRecords[i].join(this);
        }

        while (this.getCount() > this.bufferSize) {
            this.data.remove(
                (isLastSet ? this.data.first() : this.data.last())
            );
        }

        // shift the ranges if we are in the last possible data set
        if (isLastSet) {
            this.bufferRange[0]  += insertRecords.length;
            this.bufferRange[1]  += insertRecords.length;
        }

        this.fireEvent("add", this, insertRecords, index, isLastSet);

        if (split == true) {
            this.fireEvent("add", this, records, Number.MAX_VALUE, isLastSet);
        }
    },

    /**
     * Remove a Record from the Store and fires the remove event.
     *
     * This implementation will check for the appearance of the record id
     * in the store. The record to be removed does not neccesarily be bound
     * to the instance of this store.
     * If the record is not within the store, the method will try to guess it's
     * index by calling findInsertIndex.
     *
     * Please note that this method assumes that the records that's about to
     * be removed from the store does belong to the data within the store or the
     * underlying data store, thus the remove event will always be fired.
     * This may lead to inconsitency if you have to stores up at once. Let A
     * be the store that reads from the data repository C, and B the other store
     * that only represents a subset of data of the data repository C. If you
     * now remove a record X from A, which has not been in the store, but is assumed
     * to be available in the data repository, and would like to sync the available
     * data of B, then you have to check first if X may have apperead in the subset
     * of data C represented by B before calling remove from the B store (because
     * the remove operation will always trigger the "remove" event, no matter what).
     * (Common use case: you have selected a range of records which are then stored in
     * the row selection model. User scrolls through the data and the store's buffer
     * gets refreshed with new data for displaying. Now you want to remove all records
     * which are within the rowselection model, but not anymore within the store.)
     * One possible workaround is to only remove the record X from B if, and only
     * if the return value of a call to [object instance of store B].data.indexOf(X)
     * does not return a value less than 0. Though not removing the record from
     * B may not update the view of an attached BufferedGridView immediately.
     *
     * @param {Ext.data.Record} record
     * @param {Boolean} suspendEvent true to suspend the "remove"-event
     *
     * @return Number the index of the record removed.
     */
    remove : function(record, suspendEvent)
    {
        // check wether the record.id can be found in this store
        var index = this._getIndex(record);

        if (index < 0) {
            this.totalLength -= 1;
            if(this.pruneModifiedRecords){
                this.modified.remove(record);
            }
            // adjust the buffer range if a record was removed
            // in the range that is actually behind the bufferRange
            this.bufferRange[0] = Math.max(-1, this.bufferRange[0]-1);
            this.bufferRange[1] = Math.max(-1, this.bufferRange[1]-1);

            if (suspendEvent !== true) {
                this.fireEvent("remove", this, record, index);
            }
            return index;
        }

        this.bufferRange[1] = Math.max(-1, this.bufferRange[1]-1);
        this.data.removeAt(index);

        if(this.pruneModifiedRecords){
            this.modified.remove(record);
        }

        this.totalLength -= 1;
        if (suspendEvent !== true) {
            this.fireEvent("remove", this, record, index);
        }

        return index;
    },

    _getIndex : function(record)
    {
        var index = this.indexOfId(record.id);

        if (index < 0) {
            index = this.findInsertIndex(record);
        }

        return index;
    },

    /**
     * Removes a larger amount of records from the store and fires the "bulkremove"
     * event.
     * This helps listeners to determine whether the remove operation of multiple
     * records is still pending.
     *
     * @param {Array} records
     */
    bulkRemove : function(records)
    {
        var rec  = null;
        var recs = [];
        var ind  = 0;
        var len  = records.length;

        var orgIndexes = [];
        for (var i = 0; i < len; i++) {
            rec = records[i];

            orgIndexes[rec.id] = this._getIndex(rec);
        }

        for (var i = 0; i < len; i++) {
            rec = records[i];
            this.remove(rec, true);
            recs.push([rec, orgIndexes[rec.id]]);
        }

        this.fireEvent("bulkremove", this, recs);
    },

    /**
     * Remove all Records from the Store and fires the clear event.
     * The method assumes that there will be no data available anymore in the
     * underlying data store.
     */
    removeAll : function()
    {
        this.totalLength = 0;
        this.bufferRange = [-1, -1];
        this.data.clear();

        if(this.pruneModifiedRecords){
            this.modified = [];
        }
        this.fireEvent("clear", this);
    },

    /**
     * Requests a range of data from the underlying data store. Similiar to the
     * start and limit parameter usually send to the server, the method needs
     * an array of ranges of indexes.
     * Example: To load all records at the positions 1,2,3,4,9,12,13,14, the supplied
     * parameter should equal to [[1,4],[9],[12,14]].
     * The request will only be done if the beforeselectionsloaded events return
     * value does not equal to false.
     */
    loadRanges : function(ranges)
    {
        var max_i = ranges.length;

        var pn = this.paramNames;

        if(max_i > 0 && !this.selectionsProxy.activeRequest[Ext.data.Api.actions.read]
           && this.fireEvent("beforeselectionsload", this, ranges) !== false){

            var lParams = this.lastOptions.params;

            var params = {};
            params.ranges = Ext.encode(ranges);

            if (lParams) {
                if (lParams[pn.sort]) {
                    params[pn.sort] = lParams[pn.sort];
                }
                if (lParams[pn.dir]) {
                    params[pn.dir] = lParams[pn.dir];
                }
            }

            var options = {};
            for (var i in this.lastOptions) {
                options.i = this.lastOptions.i;
            }

            options.ranges = params.ranges;

            this.selectionsProxy.doRequest(
                Ext.data.Api.actions.read, null, options, this.reader,
                this.selectionsLoaded, this, options
            );
        }
    },

    /**
     * Alias for loadRanges.
     */
    loadSelections : function(ranges)
    {
        if (ranges.length == 0) {
            return;
        }
        this.loadRanges(ranges);
    },

    /**
     * Called as a callback by the proxy which loads pending selections.
     * Will fire the selectionsload event with the loaded records if, and only
     * if the return value of the checkVersionChange event does not equal to
     * false.
     */
    selectionsLoaded : function(o, options, success)
    {
        if (this.checkVersionChange(o, options, success) !== false) {

            var r = o.records;
            for(var i = 0, len = r.length; i < len; i++){
                r[i].join(this);
            }

            this.fireEvent("selectionsload", this, o.records, Ext.decode(options.ranges));
        } else {
            this.fireEvent("selectionsload", this, [], Ext.decode(options.ranges));
        }
    },

    /**
     * Checks if the version supplied in <tt>o</tt> differs from the version
     * property of the current instance of this object and fires the versionchange
     * event if it does.
     */
    // private
    checkVersionChange : function(o, options, success)
    {
        if(o && success !== false){
            if (o.version !== undefined) {
                var old      = this.version;
                this.version = o.version;
                if (this.version !== old) {
                    return this.fireEvent('versionchange', this, old, this.version);
                }
            }
        }
    },

    /**
     * The sort procedure tries to respect the current data in the buffer. If the
     * found index would not be within the bufferRange, Number.MIN_VALUE is returned to
     * indicate that the record would be sorted below the first record in the buffer
     * range, while Number.MAX_VALUE would indicate that the record would be added after
     * the last record in the buffer range.
     *
     * The method is not guaranteed to return the relative index of the record
     * in the data model as returned by the underlying domain model.
     */
    findInsertIndex : function(record)
    {
        this.remoteSort = false;
        var index = Ext.ux.grid.livegrid.Store.superclass.findInsertIndex.call(this, record);
        this.remoteSort = true;

        // special case... index is 0 and we are at the very first record
        // buffered
        if (this.bufferRange[0] <= 0 && index == 0) {
            //return index;
        } else if (this.bufferRange[0] > 0 && index == 0) {
            index = Number.MIN_VALUE;
        } else if (index >= this.bufferSize) {
            index = Number.MAX_VALUE;
        }

        var obj = {
            insertIndex : index
        };
        this.fireEvent('insertindexfound', this, obj);

        return obj.insertIndex;
    },

    /**
     * Removed snapshot check
     */
    // private
    sortData : function(f, direction)
    {
        var snap = this.snapshot;

        this.snapshot = false;

        Ext.ux.grid.livegrid.Store.superclass.sortData.apply(this, arguments);

        this.snapshot = snap;
    },



    /**
     * @cfg {Number} bufferSize The number of records that will at least always
     * be available in the store for rendering. This value will be send to the
     * server as the <tt>limit</tt> parameter and should not change during the
     * lifetime of a grid component. Note: In a paging grid, this number would
     * indicate the page size.
     * The value should be set high enough to make a userfirendly scrolling
     * possible and should be greater than the sum of {nearLimit} and
     * {visibleRows}. Usually, a value in between 150 and 200 is good enough.
     * A lesser value will more often make the store re-request new data, while
     * a larger number will make loading times higher.
     */


    // private
    onMetaChange : function(meta, rtype, o)
    {
        this.version = null;
        Ext.ux.grid.livegrid.Store.superclass.onMetaChange.call(this, meta, rtype, o);
    },


    /**
     * Will fire the versionchange event if the version of incoming data has changed.
     */
    // private
    loadRecords : function(o, options, success)
    {
        this.checkVersionChange(o, options, success);

        // we have to stay in sync with rows that may have been skipped while
        // the request was loading.
        // if the response didn't make it through, set buffer range to -1,-1
        if (!o) {
            this.bufferRange = [-1,-1];
        } else {

            var pn = this.paramNames;

            this.bufferRange = [
                options.params.start,
                Math.max(0, Math.min((
                    options.params[pn.start]+options.params[pn.limit])-1,
                    o.totalRecords-1
                ))
            ];
        }

        if (options.suspendLoadEvent === true) {
            this.suspendEvents();
        }
        Ext.ux.grid.livegrid.Store.superclass.loadRecords.call(this, o, options, success);
        if (options.suspendLoadEvent === true) {
            this.resumeEvents();
        }
    },

    /**
     * Get the Record at the specified index.
     * The function will take the bufferRange into account and translate the passed argument
     * to the index of the record in the current buffer.
     *
     * @param {Number} index The index of the Record to find.
     * @return {Ext.data.Record} The Record at the passed index. Returns undefined if not found.
     */
    getAt : function(index)
    {
        //anything buffered yet?
        if (this.bufferRange[0] == -1) {
            return undefined;
        }

        var modelIndex = index - this.bufferRange[0];
        return this.data.itemAt(modelIndex);
    },

//--------------------------------------EMPTY-----------------------------------
    // no interface concept, so simply overwrite and leave them empty as for now
    clearFilter : function(){},
    isFiltered : function(){},
    collect : function(){},
    createFilterFn : function(){},
    sum : function(){},
    filter : function(){},
    filterBy : function(){},
    query : function(){},
    queryBy : function(){},
    find : function(){},
    findBy : function(){}

});/**
 * Ext.ux.grid.livegrid.CheckboxSelectionModel
 * Copyright (c) 2007-2012, http://www.siteartwork.de
 *
 * Ext.ux.grid.livegrid.CheckboxSelectionModel is licensed under the terms of the
 *                  GNU Open Source GPL 3.0
 * license.
 *
 * Commercial use is prohibited. Visit <http://ext-livegrid.com>
 * if you need to obtain a commercial license.
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 */

Ext.namespace('Ext.ux.grid.livegrid');

/**
 * @class Ext.ux.grid.livegrid.CheckboxSelectionModel
 * @extends Ext.ux.grid.livegrid.RowSelectionModel
 * @constructor
 * @param {Object} config
 *
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 */
Ext.ux.grid.livegrid.CheckboxSelectionModel = Ext.extend(Ext.ux.grid.livegrid.RowSelectionModel, {

    /**
     * @cfg {Boolean} checkOnly <tt>true</tt> if rows can only be selected by clicking on the
     * checkbox column (defaults to <tt>false</tt>).
     */
    /**
     * @cfg {Number} width The default width in pixels of the checkbox column (defaults to <tt>20</tt>).
     */
    width : 20,

    // private
    menuDisabled : true,
    sortable : false,
    fixed : true,
    dataIndex : '',
    id : 'checker',
    headerCheckbox : null,
    markAll : false,

    constructor : function()
    {
        if (!this.header) {
            this.header = Ext.grid.CheckboxSelectionModel.prototype.header;
        }

        this.sortable = false;

        Ext.ux.grid.livegrid.CheckboxSelectionModel.superclass.constructor.call(this);
    },

    // private
    initEvents : function()
    {
        Ext.ux.grid.livegrid.CheckboxSelectionModel.superclass.initEvents.call(this);

        this.grid.view.on('reset', function(gridView, forceReload) {
                this.headerCheckbox = new Ext.Element(
                    gridView.getHeaderCell(this.grid.getColumnModel().getIndexById(this.id)).firstChild
                );
                if (this.markAll && forceReload === false) {
                    this.headerCheckbox.addClass('x-grid3-hd-checker-on');
                }
        }, this);

        Ext.grid.CheckboxSelectionModel.prototype.initEvents.call(this);
    },

    // private
    onMouseDown : function(e, t)
    {
        if(e.button === 0 && t.className == 'x-grid3-row-checker') {
            e.stopEvent();
            var row = e.getTarget('.x-grid3-row');
            if(row){
                if (this.headerCheckbox) {
                    this.markAll = false;
                    this.headerCheckbox.removeClass('x-grid3-hd-checker-on');
                }
            }
        }

        return Ext.grid.CheckboxSelectionModel.prototype.onMouseDown.call(this, e, t);
    },

    // private
    onHdMouseDown : function(e, t)
    {
        if (t.className == 'x-grid3-hd-checker' && !this.headerCheckbox) {
            this.headerCheckbox = new Ext.Element(t.parentNode);
        }

        return Ext.grid.CheckboxSelectionModel.prototype.onHdMouseDown.call(this, e, t);
    },

    // private
    renderer : function(v, p, record)
    {
        return Ext.grid.CheckboxSelectionModel.prototype.renderer.call(this, v, p, record);
    },

// -------- overrides

    /**
     * Overriden to prevent selections by shift-clicking
     */
    handleMouseDown : function(g, rowIndex, e)
    {
        if (e.shiftKey) {
            return;
        }

        this.markAll = false;

        if (this.headerCheckbox) {
            this.headerCheckbox.removeClass('x-grid3-hd-checker-on');
        }

        Ext.ux.grid.livegrid.CheckboxSelectionModel.superclass.handleMouseDown.call(this, g, rowIndex, e);
    },

    /**
     * Overriden to clear header sort state
     */
    clearSelections : function(fast)
    {
        if(this.isLocked()){
            return;
        }

        this.markAll = false;

        if (this.headerCheckbox) {
            this.headerCheckbox.removeClass('x-grid3-hd-checker-on');
        }

        Ext.ux.grid.livegrid.CheckboxSelectionModel.superclass.clearSelections.call(this, fast);
    },

    /**
     * Selects all rows if the selection model
     * {@link Ext.grid.AbstractSelectionModel#isLocked is not locked}.
     */
    selectAll : function()
    {
        Ext.ux.grid.livegrid.CheckboxSelectionModel.superclass.selectAll.call(this);

        this.markAll = true;

        if (this.headerCheckbox) {
            this.headerCheckbox.addClass('x-grid3-hd-checker-on');
        }
    }


});/**
 * Ext.ux.grid.livegrid.Toolbar
 * Copyright (c) 2007-2012, http://www.siteartwork.de
 *
 * Ext.ux.grid.livegrid.Toolbar is licensed under the terms of the
 *                  GNU Open Source GPL 3.0
 * license.
 *
 * Commercial use is prohibited. Visit <http://ext-livegrid.com>
 * if you need to obtain a commercial license.
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 */

Ext.namespace('Ext.ux.grid.livegrid');

/**
 * toolbar that is bound to a {@link Ext.ux.grid.livegrid.GridView}
 * and provides information about the indexes of the requested data and the buffer
 * state.
 *
 * @class Ext.ux.grid.livegrid.Toolbar
 * @extends Ext.Toolbar
 * @constructor
 * @param {Object} config
 *
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 */
Ext.ux.grid.livegrid.Toolbar = Ext.extend(Ext.Toolbar, {

    /**
     * @cfg {Ext.grid.GridPanel} grid
     * The grid the toolbar is bound to. If ommited, use the cfg property "view"
     */

    /**
     * @cfg {Ext.grid.GridView} view The view the toolbar is bound to
     * The grid the toolbar is bound to. If ommited, use the cfg property "grid"
     */

    /**
     * @cfg {Boolean} displayInfo
     * True to display the displayMsg (defaults to false)
     */

    /**
     * @cfg {String} displayMsg
     * The paging status message to display (defaults to "Displaying {start} - {end} of {total}")
     */
    displayMsg : 'Displaying {0} - {1} of {2}',

    /**
     * @cfg {String} emptyMsg
     * The message to display when no records are found (defaults to "No data to display")
     */
    emptyMsg : 'No data to display',

    /**
     * Value to display as the tooltip text for the refresh button. Defaults to
     * "Refresh"
     * @param {String}
     */
    refreshText : "Refresh",

    initComponent : function()
    {
        Ext.ux.grid.livegrid.Toolbar.superclass.initComponent.call(this);

        if (this.grid) {
            this.view = this.grid.getView();
        }

        var me = this;
        this.view.init = this.view.init.createSequence(function(){
            me.bind(this);
        }, this.view);
    },

    // private
    updateInfo : function(rowIndex, visibleRows, totalCount)
    {
        if(this.displayEl){
            var msg = totalCount == 0 ?
                this.emptyMsg :
                String.format(this.displayMsg, rowIndex+1,
                              rowIndex+visibleRows, totalCount);
            this.displayEl.update(msg);
        }
    },

    /**
     * Unbinds the toolbar.
     *
     * @param {Ext.grid.GridView|Ext.gid.GridPanel} view Either The view to unbind
     * or the grid
     */
    unbind : function(view)
    {
        var st;
        var vw;

        if (view instanceof Ext.grid.GridView) {
            vw = view;
        } else {
            // assuming parameter is of type Ext.grid.GridPanel
            vw = view.getView();
        }

        st = view.ds;

        st.un('loadexception', this.enableLoading,  this);
        st.un('beforeload',    this.disableLoading, this);
        st.un('load',          this.enableLoading,  this);
        vw.un('rowremoved',    this.onRowRemoved,   this);
        vw.un('rowsinserted',  this.onRowsInserted, this);
        vw.un('beforebuffer',  this.beforeBuffer,   this);
        vw.un('cursormove',    this.onCursorMove,   this);
        vw.un('buffer',        this.onBuffer,       this);
        vw.un('bufferfailure', this.enableLoading,  this);

        this.view = undefined;
    },

    /**
     * Binds the toolbar to the specified {@link Ext.ux.grid.Livegrid}
     *
     * @param {Ext.grird.GridView} view The view to bind
     */
    bind : function(view)
    {
        this.view = view;
        var st = view.ds;

        st.on('loadexception',   this.enableLoading,  this);
        st.on('beforeload',      this.disableLoading, this);
        st.on('load',            this.enableLoading,  this);
        view.on('rowremoved',    this.onRowRemoved,   this);
        view.on('rowsinserted',  this.onRowsInserted, this);
        view.on('beforebuffer',  this.beforeBuffer,   this);
        view.on('cursormove',    this.onCursorMove,   this);
        view.on('buffer',        this.onBuffer,       this);
        view.on('bufferfailure', this.enableLoading,  this);
    },

// ----------------------------------- Listeners -------------------------------
    enableLoading : function()
    {
        this.loading.setDisabled(false);
    },

    disableLoading : function()
    {
        this.loading.setDisabled(true);
    },

    onCursorMove : function(view, rowIndex, visibleRows, totalCount)
    {
        this.updateInfo(rowIndex, visibleRows, totalCount);
    },

    // private
    onRowsInserted : function(view, start, end)
    {
        this.updateInfo(view.rowIndex, Math.min(view.ds.totalLength, view.visibleRows-view.rowClipped),
                        view.ds.totalLength);
    },

    // private
    onRowRemoved : function(view, index, record)
    {
        this.updateInfo(view.rowIndex, Math.min(view.ds.totalLength, view.visibleRows-view.rowClipped),
                        view.ds.totalLength);
    },

    // private
    beforeBuffer : function(view, store, rowIndex, visibleRows, totalCount, options)
    {
        this.loading.disable();
        this.updateInfo(rowIndex, visibleRows, totalCount);
    },

    // private
    onBuffer : function(view, store, rowIndex, visibleRows, totalCount)
    {
        this.loading.enable();
        this.updateInfo(rowIndex, visibleRows, totalCount);
    },

    // private
    onClick : function(type)
    {
        switch (type) {
            case 'refresh':
                if (this.view.reset(true)) {
                    this.loading.disable();
                } else {
                    this.loading.enable();
                }
            break;

        }
    },

    // private
    onRender : function(ct, position)
    {
        Ext.PagingToolbar.superclass.onRender.call(this, ct, position);

        this.loading = new Ext.Toolbar.Button({
            tooltip : this.refreshText,
            iconCls : "x-tbar-loading",
            handler : this.onClick.createDelegate(this, ["refresh"])
        });

        this.addButton(this.loading);

        this.addSeparator();

        if(this.displayInfo){
            this.displayEl = Ext.fly(this.el.dom).createChild({cls:'x-paging-info'});
        }
    }
});/**
 * Ext.ux.grid.livegrid.DragZone
 * Copyright (c) 2007-2012, http://www.siteartwork.de
 *
 * Ext.ux.grid.livegrid.DragZone is licensed under the terms of the
 *                  GNU Open Source GPL 3.0
 * license.
 *
 * Commercial use is prohibited. Visit <http://ext-livegrid.com>
 * if you need to obtain a commercial license.
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 */

Ext.namespace('Ext.ux.grid.livegrid');

/**
 * @class Ext.ux.grid.livegrid.DragZone
 * @extends Ext.dd.DragZone
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 */
Ext.ux.grid.livegrid.DragZone = function(grid, config){

    Ext.ux.grid.livegrid.DragZone.superclass.constructor.call(this, grid, config);

    this.view.ds.on('beforeselectionsload', this._onBeforeSelectionsLoad, this);
    this.view.ds.on('selectionsload',       this._onSelectionsLoad,       this);
};

Ext.extend(Ext.ux.grid.livegrid.DragZone, Ext.grid.GridDragZone, {

    /**
     * Tells whether a drop is valid. Used inetrnally to determine if pending
     * selections need to be loaded/ have been loaded.
     * @type {Boolean}
     */
    isDropValid : true,

    /**
     * Overriden for loading pending selections if needed.
     */
    onInitDrag : function(e)
    {
        this.view.ds.loadSelections(this.grid.selModel.getPendingSelections(true));

        Ext.ux.grid.livegrid.DragZone.superclass.onInitDrag.call(this, e);
    },

    /**
     * Gets called before pending selections are loaded. Any drop
     * operations are invalid/get paused if the component needs to
     * wait for selections to load from the server.
     *
     */
    _onBeforeSelectionsLoad : function()
    {
        this.isDropValid = false;
        Ext.fly(this.proxy.el.dom.firstChild).addClass('ext-ux-livegrid-drop-waiting');
    },

    /**
     * Gets called after pending selections have been loaded.
     * Any paused drop operation will be resumed.
     *
     */
    _onSelectionsLoad : function()
    {
        this.isDropValid = true;
        this.ddel.innerHTML = this.grid.getDragDropText();
        Ext.fly(this.proxy.el.dom.firstChild).removeClass('ext-ux-livegrid-drop-waiting');
    }
});/**
 * Ext.ux.grid.livegrid.EditorGridPanel
 * Copyright (c) 2007-2012, http://www.siteartwork.de
 *
 * Ext.ux.grid.livegrid.EditorGridPanel is licensed under the terms of the
 *                  GNU Open Source GPL 3.0
 * license.
 *
 * Commercial use is prohibited. Visit <http://ext-livegrid.com>
 * if you need to obtain a commercial license.
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 */

Ext.namespace('Ext.ux.grid.livegrid');

/**
 * @class Ext.ux.grid.livegrid.EditorGridPanel
 * @extends Ext.grid.EditorGridPanel
 * @constructor
 * @param {Object} config
 *
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 */
Ext.ux.grid.livegrid.EditorGridPanel = Ext.extend(Ext.grid.EditorGridPanel, {

    /**
     * Overriden so the panel listens to the "cursormove" event for
     * cancelling any edit that is in progress.
     *
     * @private
     */
    initEvents : function()
    {
        Ext.ux.grid.livegrid.EditorGridPanel.superclass.initEvents.call(this);

        this.view.on("cursormove", this.stopEditing, this, [true]);
    },

    /**
     * Starts editing the specified for the specified row/column
     * Will be cancelled if the requested row index to edit is not
     * represented by data due to out of range regarding the view's
     * store buffer.
     *
     * @param {Number} rowIndex
     * @param {Number} colIndex
     */
    startEditing : function(row, col)
    {
        this.stopEditing();
        if(this.colModel.isCellEditable(col, row)){
            this.view.ensureVisible(row, col, true);
            if (!this.store.getAt(row)) {
                return;
            }
        }

        return Ext.ux.grid.livegrid.EditorGridPanel.superclass.startEditing.call(this, row, col);
    },

// Since we do not have multiple inheritance, we need to override the
// same methods in this class we have overriden for
// Ext.ux.grid.livegrid.GridPanel
    walkCells : function(row, col, step, fn, scope)
    {
        return Ext.ux.grid.livegrid.GridPanel.prototype.walkCells.call(this, row, col, step, fn, scope);
    },

    onRender : function(ct, position)
    {
        return Ext.ux.grid.livegrid.GridPanel.prototype.onRender.call(this, ct, position);
    },

    initComponent : function()
    {
        if (this.cls) {
            this.cls += ' ext-ux-livegrid';
        } else {
            this.cls = 'ext-ux-livegrid';
        }

        return Ext.ux.grid.livegrid.EditorGridPanel.superclass.initComponent.call(this);
    }

});