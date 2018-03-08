// vim: ts=4:sw=4:nu:fdc=4:nospell
/*global Ext */
/**
 * @class Ext.ux.grid.RowActions
 * @extends Ext.util.Observable
 *
 * RowActions plugin for Ext grid. Contains renderer for icons and fires events when an icon is clicked.
 * CSS rules from Ext.ux.RowActions.css are mandatory
 *
 * Important general information: Actions are identified by iconCls. Wherever an <i>action</i>
 * is referenced (event argument, callback argument), the iconCls of clicked icon is used.
 * In other words, action identifier === iconCls.
 *
 * @author    Ing. Jozef Sakáloš
 * @copyright (c) 2008, by Ing. Jozef Sakáloš
 * @date      22. March 2008
 * @version   1.0
 * @revision  $Id: RowAction.js 14816 2013-05-21 08:31:20Z mschering $
 *
 * @license Ext.ux.grid.RowActions is licensed under the terms of
 * the Open Source LGPL 3.0 license.  Commercial use is permitted to the extent
 * that the code/component(s) do NOT become part of another Open Source or Commercially
 * licensed development library or toolkit without explicit permission.
 *
 * <p>License details: <a href="http://www.gnu.org/licenses/lgpl.html"
 * target="_blank">http://www.gnu.org/licenses/lgpl.html</a></p>
 *
 * @forum     29961
 * @demo      http://rowactions.extjs.eu
 * @download
 * <ul>
 * <li><a href="http://rowactions.extjs.eu/rowactions.tar.bz2">rowactions.tar.bz2</a></li>
 * <li><a href="http://rowactions.extjs.eu/rowactions.tar.gz">rowactions.tar.gz</a></li>
 * <li><a href="http://rowactions.extjs.eu/rowactions.zip">rowactions.zip</a></li>
 * </ul>
 *
 * @donate
 * <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
 * <input type="hidden" name="cmd" value="_s-xclick">
 * <input type="hidden" name="hosted_button_id" value="3430419">
 * <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif"
 * border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
 * <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
 * </form>
 */

Ext.ns('Ext.ux.grid');

// add RegExp.escape if it has not been already added
if('function' !== typeof RegExp.escape) {
	RegExp.escape = function(s) {
		if('string' !== typeof s) {
			return s;
		}
		// Note: if pasting from forum, precede ]/\ with backslash manually
		return s.replace(/([.*+?\^=!:${}()|\[\]\/\\])/g, '\\$1');
	}; // eo function escape
}

/**
 * Creates new RowActions plugin
 * @constructor
 * @param {Object} config A config object
 */
Ext.ux.grid.RowActions = function(config) {
	Ext.apply(this, config);

	// {{{
	this.addEvents(
		/**
		 * @event beforeaction
		 * Fires before action event. Return false to cancel the subsequent action event.
		 * @param {Ext.grid.GridPanel} grid
		 * @param {Ext.data.Record} record Record corresponding to row clicked
		 * @param {String} action Identifies the action icon clicked. Equals to icon css class name.
		 * @param {Integer} rowIndex Index of clicked grid row
		 * @param {Integer} colIndex Index of clicked grid column that contains all action icons
		 */
		 'beforeaction'
		/**
		 * @event action
		 * Fires when icon is clicked
		 * @param {Ext.grid.GridPanel} grid
		 * @param {Ext.data.Record} record Record corresponding to row clicked
		 * @param {String} action Identifies the action icon clicked. Equals to icon css class name.
		 * @param {Integer} rowIndex Index of clicked grid row
		 * @param {Integer} colIndex Index of clicked grid column that contains all action icons
		 */
		,'action'
		/**
		 * @event beforegroupaction
		 * Fires before group action event. Return false to cancel the subsequent groupaction event.
		 * @param {Ext.grid.GridPanel} grid
		 * @param {Array} records Array of records in this group
		 * @param {String} action Identifies the action icon clicked. Equals to icon css class name.
		 * @param {String} groupId Identifies the group clicked
		 */
		,'beforegroupaction'
		/**
		 * @event groupaction
		 * Fires when icon in a group header is clicked
		 * @param {Ext.grid.GridPanel} grid
		 * @param {Array} records Array of records in this group
		 * @param {String} action Identifies the action icon clicked. Equals to icon css class name.
		 * @param {String} groupId Identifies the group clicked
		 */
		,'groupaction'
	);
	// }}}

	// call parent
	Ext.ux.grid.RowActions.superclass.constructor.call(this);
};

Ext.extend(Ext.ux.grid.RowActions, Ext.util.Observable, {

	// configuration options
	// {{{
	/**
	 * @cfg {Array} actions Mandatory. Array of action configuration objects. The action
	 * configuration object recognizes the following options:
	 * <ul class="list">
	 * <li style="list-style-position:outside">
	 *   {Function} <b>callback</b> (optional). Function to call if the action icon is clicked.
	 *   This function is called with same signature as action event and in its original scope.
	 *   If you need to call it in different scope or with another signature use
	 *   createCallback or createDelegate functions. Works for statically defined actions. Use
	 *   callbacks configuration options for store bound actions.
	 * </li>
	 * <li style="list-style-position:outside">
	 *   {Function} <b>cb</b> Shortcut for callback.
	 * </li>
	 * <li style="list-style-position:outside">
	 *   {String} <b>iconIndex</b> Optional, however either iconIndex or iconCls must be
	 *   configured. Field name of the field of the grid store record that contains
	 *   css class of the icon to show. If configured, shown icons can vary depending
	 *   of the value of this field.
	 * </li>
	 * <li style="list-style-position:outside">
	 *   {String} <b>iconCls</b> CSS class of the icon to show. It is ignored if iconIndex is
	 *   configured. Use this if you want static icons that are not base on the values in the record.
	 * </li>
	 * <li style="list-style-position:outside">
	 *   {Boolean} <b>hide</b> Optional. True to hide this action while still have a space in
	 *   the grid column allocated to it. IMO, it doesn't make too much sense, use hideIndex instead.
	 * </li>
	 * <li style="list-style-position:outside">
	 *   {String} <b>hideIndex</b> Optional. Field name of the field of the grid store record that
	 *   contains hide flag (falsie [null, '', 0, false, undefined] to show, anything else to hide).
	 * </li>
	 * <li style="list-style-position:outside">
	 *   {String} <b>qtipIndex</b> Optional. Field name of the field of the grid store record that
	 *   contains tooltip text. If configured, the tooltip texts are taken from the store.
	 * </li>
	 * <li style="list-style-position:outside">
	 *   {String} <b>tooltip</b> Optional. Tooltip text to use as icon tooltip. It is ignored if
	 *   qtipIndex is configured. Use this if you want static tooltips that are not taken from the store.
	 * </li>
	 * <li style="list-style-position:outside">
	 *   {String} <b>qtip</b> Synonym for tooltip
	 * </li>
	 * <li style="list-style-position:outside">
	 *   {String} <b>textIndex</b> Optional. Field name of the field of the grids store record
	 *   that contains text to display on the right side of the icon. If configured, the text
	 *   shown is taken from record.
	 * </li>
	 * <li style="list-style-position:outside">
	 *   {String} <b>text</b> Optional. Text to display on the right side of the icon. Use this
	 *   if you want static text that are not taken from record. Ignored if textIndex is set.
	 * </li>
	 * <li style="list-style-position:outside">
	 *   {String} <b>style</b> Optional. Style to apply to action icon container.
	 * </li>
	 * </ul>
	 */

	/**
	 * @cfg {String} actionEvent Event to trigger actions, e.g. click, dblclick, mouseover (defaults to 'click')
	 */
	 actionEvent:'click'
	/**
	 * @cfg {Boolean} autoWidth true to calculate field width for iconic actions only (defaults to true).
	 * If true, the width is calculated as {@link #widthSlope} * number of actions + {@link #widthIntercept}.
	 */
	,autoWidth:true

	/**
	 * @cfg {String} dataIndex - Do not touch!
	 * @private
	 */
	,dataIndex:''

	/**
	 * @cfg {Boolean} editable - Do not touch!
	 * Must be false to prevent errors in editable grids
	 */
	,editable:false

	/**
	 * @cfg {Array} groupActions Array of action to use for group headers of grouping grids.
	 * These actions support static icons, texts and tooltips same way as {@link #actions}. There is one
	 * more action config option recognized:
	 * <ul class="list">
	 * <li style="list-style-position:outside">
	 *   {String} <b>align</b> Set it to 'left' to place action icon next to the group header text.
	 *   (defaults to undefined = icons are placed at the right side of the group header.
	 * </li>
	 * </ul>
	 */

	/**
	 * @cfg {Object} callbacks iconCls keyed object that contains callback functions. For example:
	 * <pre>
	 * callbacks:{
	 * &nbsp;    'icon-open':function(...) {...}
	 * &nbsp;   ,'icon-save':function(...) {...}
	 * }
	 * </pre>
	 */

	/**
	 * @cfg {String} header Actions column header
	 */
	,header:''

	/**
	 * @cfg {Boolean} isColumn
	 * Tell ColumnModel that we are column. Do not touch!
	 * @private
	 */
	,isColumn:true

	/**
	 * @cfg {Boolean} keepSelection
	 * Set it to true if you do not want action clicks to affect selected row(s) (defaults to false).
	 * By default, when user clicks an action icon the clicked row is selected and the action events are fired.
	 * If this option is true then the current selection is not affected, only the action events are fired.
	 */
	,keepSelection:false

	/**
	 * @cfg {Boolean} menuDisabled No sense to display header menu for this column
	 * @private
	 */
	,menuDisabled:true

	/**
	 * @cfg {Boolean} sortable Usually it has no sense to sort by this column
	 * @private
	 */
	,sortable:false

	/**
	 * @cfg {String} tplGroup Template for group actions
	 * @private
	 */
	,tplGroup:
		 '<tpl for="actions">'
		+'<div class="ux-grow-action-item<tpl if="\'right\'===align"> ux-action-right</tpl> '
		+'{cls}" style="{style}" qtip="{qtip}">{text}</div>'
		+'</tpl>'

	/**
	 * @cfg {String} tplRow Template for row actions
	 * @private
	 */
	,tplRow:
		 '<div class="ux-row-action">'
		+'<tpl for="actions">'
		+'<div class="ux-row-action-item {cls} <tpl if="text">'
		+'ux-row-action-text</tpl>" style="{hide}{style}" qtip="{qtip}">'
		+'<tpl if="text"><span qtip="{qtip}">{text}</span></tpl></div>'
		+'</tpl>'
		+'</div>'

	/**
	 * @cfg {String} hideMode How to hide hidden icons. Valid values are: 'visibility' and 'display'
	 * (defaluts to 'visibility'). If the mode is visibility the hidden icon is not visible but there
	 * is still blank space occupied by the icon. In display mode, the visible icons are shifted taking
	 * the space of the hidden icon.
	 */
	,hideMode:'visibility'

	/**
	 * @cfg {Number} widthIntercept Constant used for auto-width calculation (defaults to 4).
	 * See {@link #autoWidth} for explanation.
	 */
	,widthIntercept:4

	/**
	 * @cfg {Number} widthSlope Constant used for auto-width calculation (defaults to 21).
	 * See {@link #autoWidth} for explanation.
	 */
	,widthSlope:21
	// }}}

	// methods
	// {{{
	/**
	 * Init function
	 * @param {Ext.grid.GridPanel} grid Grid this plugin is in
	 */
	,init:function(grid) {
		this.grid = grid;

		// the actions column must have an id for Ext 3.x
		this.id = this.id || Ext.id();

		// for Ext 3.x compatibility
		var lookup = grid.getColumnModel().lookup;
		delete(lookup[undefined]);
		lookup[this.id] = this;

		// {{{
		// setup template
		if(!this.tpl) {
			this.tpl = this.processActions(this.actions);

		} // eo template setup
		// }}}

		// calculate width
		if(this.autoWidth) {
			this.width =  this.widthSlope * this.actions.length + this.widthIntercept;
			this.fixed = true;
		}

		// body click handler
		var view = grid.getView();
		var cfg = {scope:this};
		cfg[this.actionEvent] = this.onClick;
		grid.afterRender = grid.afterRender.createSequence(function() {
			view.mainBody.on(cfg);
			grid.on('destroy', this.purgeListeners, this);
		}, this);

		// setup renderer
		if(!this.renderer) {
			this.renderer = function(value, cell, record, row, col, store) {
				cell.css += (cell.css ? ' ' : '') + 'ux-row-action-cell';
				return this.tpl.apply(this.getData(value, cell, record, row, col, store));
			}.createDelegate(this);
		}

		// actions in grouping grids support
		if(view.groupTextTpl && this.groupActions) {
			view.interceptMouse = view.interceptMouse.createInterceptor(function(e) {
				if(e.getTarget('.ux-grow-action-item')) {
					return false;
				}
			});
			view.groupTextTpl =
				 '<div class="ux-grow-action-text">' + view.groupTextTpl +'</div>'
				+this.processActions(this.groupActions, this.tplGroup).apply()
			;
		}

		// cancel click
		if(true === this.keepSelection) {
			grid.processEvent = grid.processEvent.createInterceptor(function(name, e) {
				if('mousedown' === name) {
					return !this.getAction(e);
				}
			}, this);
		}

	} // eo function init
	// }}}
	// {{{
	/**
	 * Returns data to apply to template. Override this if needed.
	 * @param {Mixed} value
	 * @param {Object} cell object to set some attributes of the grid cell
	 * @param {Ext.data.Record} record from which the data is extracted
	 * @param {Number} row row index
	 * @param {Number} col col index
	 * @param {Ext.data.Store} store object from which the record is extracted
	 * @return {Object} data to apply to template
	 */
	,getData:function(value, cell, record, row, col, store) {
		return record.data || {};
	} // eo function getData
	// }}}
	// {{{
	/**
	 * Processes actions configs and returns template.
	 * @param {Array} actions
	 * @param {String} template Optional. Template to use for one action item.
	 * @return {String}
	 * @private
	 */
	,processActions:function(actions, template) {
		var acts = [];

		// actions loop
		Ext.each(actions, function(a, i) {
			// save callback
			if(a.iconCls && 'function' === typeof (a.callback || a.cb)) {
				this.callbacks = this.callbacks || {};
				this.callbacks[a.iconCls] = a.callback || a.cb;
			}

			// data for intermediate template
			var o = {
				 cls:a.iconIndex ? '{' + a.iconIndex + '}' : (a.iconCls ? a.iconCls : '')
				,qtip:a.qtipIndex ? '{' + a.qtipIndex + '}' : (a.tooltip || a.qtip ? a.tooltip || a.qtip : '')
				,text:a.textIndex ? '{' + a.textIndex + '}' : (a.text ? a.text : '')
				,hide:a.hideIndex
					? '<tpl if="' + a.hideIndex + '">'
						+ ('display' === this.hideMode ? 'display:none' :'visibility:hidden') + ';</tpl>'
					: (a.hide ? ('display' === this.hideMode ? 'display:none' :'visibility:hidden;') : '')
				,align:a.align || 'right'
				,style:a.style ? a.style : ''
			};
			acts.push(o);

		}, this); // eo actions loop

		var xt = new Ext.XTemplate(template || this.tplRow);
		return new Ext.XTemplate(xt.apply({actions:acts}));

	} // eo function processActions
	// }}}
	,getAction:function(e) {
		var action = false;
		var t = e.getTarget('.ux-row-action-item');
		if(t) {
			action = t.className.replace(/ux-row-action-item /, '');
			if(action) {
				action = action.replace(/ ux-row-action-text/, '');
				action = action.trim();
			}
		}
		return action;
	} // eo function getAction
	// {{{
	/**
	 * Grid body actionEvent event handler
	 * @private
	 */
	,onClick:function(e, target) {

		var view = this.grid.getView();

		// handle row action click
		var row = e.getTarget('.x-grid3-row');
		var col = view.findCellIndex(target.parentNode.parentNode);
		var action = this.getAction(e);

//		var t = e.getTarget('.ux-row-action-item');
//		if(t) {
//			action = this.getAction(t);
//			action = t.className.replace(/ux-row-action-item /, '');
//			if(action) {
//				action = action.replace(/ ux-row-action-text/, '');
//				action = action.trim();
//			}
//		}
		if(false !== row && false !== col && false !== action) {
			var record = this.grid.store.getAt(row.rowIndex);

			// call callback if any
			if(this.callbacks && 'function' === typeof this.callbacks[action]) {
				this.callbacks[action](this.grid, record, action, row.rowIndex, col);
			}

			// fire events
			if(true !== this.eventsSuspended && false === this.fireEvent('beforeaction', this.grid, record, action, row.rowIndex, col)) {
				return;
			}
			else if(true !== this.eventsSuspended) {
				this.fireEvent('action', this.grid, record, action, row.rowIndex, col);
			}

		}

		// handle group action click
		t = e.getTarget('.ux-grow-action-item');
		if(t) {
			// get groupId
			var group = view.findGroup(target);
			var groupId = group ? group.id.replace(/ext-gen[0-9]+-gp-/, '') : null;

			// get matching records
			var records;
			if(groupId) {
				var re = new RegExp(RegExp.escape(groupId));
				records = this.grid.store.queryBy(function(r) {
					return r._groupId.match(re);
				});
				records = records ? records.items : [];
			}
			action = t.className.replace(/ux-grow-action-item (ux-action-right )*/, '');

			// call callback if any
			if('function' === typeof this.callbacks[action]) {
				this.callbacks[action](this.grid, records, action, groupId);
			}

			// fire events
			if(true !== this.eventsSuspended && false === this.fireEvent('beforegroupaction', this.grid, records, action, groupId)) {
				return false;
			}
			this.fireEvent('groupaction', this.grid, records, action, groupId);
		}
	} // eo function onClick
	// }}}

});

// registre xtype
Ext.reg('rowactions', Ext.ux.grid.RowActions);

// eof

