/**
 * @class Ext.ux.tot2ivn.VrTabPanel
 * @version		0.21		Tested working with ExtJS 3.2+ on IE6+, FireFox 2+, Chrome 4+, Opera 9.6+, Safari 4+
 * @author	Anh Nguyen (Totti)
 * @description		Vertical TabPanel implements the same set of features as those of Ext.TabPanel. 
 *	Tab position defaults to 'left'. Position 'right' is not supported.
 *	Auto-scrolling currently not implemented.
 *  Three config properties users would want to config are :
	@cfg border
	@cfg tabWidth
	@cfg tabMarginTop
	See description of config properties below.
	
 * @extends Ext.Panel
 * @constructor
 * @param {Object} config The configuration options
 * @xtype tabpanel
 */

Ext.ns('Ext.ux.tot2ivn');
Ext.ux.tot2ivn.VrTabPanel = Ext.extend(Ext.Panel,  {
    /** Vertical Tab Panel cfg */
	/**
     * @cfg {Boolean} border
     * Set to true to draw the outline border of the whole panel. Defaults to true.
     */
	 border: true,
	 /**
     * @cfg {Number} tabWidth The initial width in pixels of each new tab title (defaults to 130).
     */
    tabWidth : 130,	
	/**
     * @cfg {Number} tabMarginTop The initial top margin in pixels of the tab strip. (defaults to 15).
     */
	tabMarginTop : 15,
	
	/**
     * @cfg {Boolean} layoutOnTabChange
     * Set to true to force a layout of the active tab when the tab is changed. Defaults to false.
     * See {@link Ext.layout.CardLayout}.<code>{@link Ext.layout.CardLayout#layoutOnCardChange layoutOnCardChange}</code>.
     */
    /**
     * @cfg {String} tabCls <b>This config option is used on <u>child Components</u> of ths TabPanel.</b> A CSS
     * class name applied to the tab strip item representing the child Component, allowing special
     * styling to be applied.
     */
    /**
     * @cfg {Boolean} deferredRender
     */
    deferredRender : true,    
    /**
     * @cfg {Number} minTabWidth The minimum width in pixels for each tab when {@link #resizeTabs} = true (defaults to 30).
     */
    minTabWidth : 30,
    /**
     * @cfg {Boolean} resizeTabs True to automatically resize each tab so that the tabs will completely fill the
     * tab strip (defaults to false).  Setting this to true may cause specific widths that might be set per tab to
     * be overridden in order to fit them all into view (although {@link #minTabWidth} will always be honored).
     */
    resizeTabs : false,
    /**
     * @cfg {Boolean} enableTabScroll True to enable scrolling to tabs that may be invisible due to overflowing the
     * overall TabPanel width. Only available with tabPosition:'top' (defaults to false).
     */
    enableTabScroll : false,
    /**
     * @cfg {Number} scrollIncrement The number of pixels to scroll each time a tab scroll button is pressed
     * (defaults to <tt>100</tt>, or if <tt>{@link #resizeTabs} = true</tt>, the calculated tab width).  Only
     * applies when <tt>{@link #enableTabScroll} = true</tt>.
     */
    scrollIncrement : 0,
    /**
     * @cfg {Number} scrollRepeatInterval Number of milliseconds between each scroll while a tab scroll button is
     * continuously pressed (defaults to <tt>400</tt>).
     */
    scrollRepeatInterval : 400,
    /**
     * @cfg {Float} scrollDuration The number of milliseconds that each scroll animation should last (defaults
     * to <tt>.35</tt>). Only applies when <tt>{@link #animScroll} = true</tt>.
     */
    scrollDuration : 0.35,
    /**
     * @cfg {Boolean} animScroll True to animate tab scrolling so that hidden tabs slide smoothly into view (defaults
     * to <tt>true</tt>).  Only applies when <tt>{@link #enableTabScroll} = true</tt>.
     */
    animScroll : true,
    /**
	 * @hide
     * @cfg {String} tabPosition The position where the tab strip should be rendered (defaults to <tt>'left'</tt>).
     * No other value supported.  <b>Note</b>: tab scrolling is currently not supported.
     * <tt>tabPosition: 'top'</tt>. Config property internally remained 'top' to reuse Ext.TabPanel styles.
     */
    tabPosition : 'top',
    
	/**
     * @cfg {String} baseCls The base CSS class applied to the panel (defaults to <tt>'x-tab-panel'</tt>).
     */
    baseCls : 'x-tab-panel x-tot2ivn-vr-tab-panel',
    
	/**
     * @cfg {Boolean} autoTabs
     */
    autoTabs : false,
    /**
     * @cfg {String} autoTabSelector The CSS selector used to search for tabs in existing markup when
     * <tt>{@link #autoTabs} = true</tt> (defaults to <tt>'div.x-tab'</tt>).  This can be any valid selector
     * supported by {@link Ext.DomQuery#select}. Note that the query will be executed within the scope of this
     * tab panel only (so that multiple tab panels from markup can be supported on a page).
     */
    autoTabSelector : 'div.x-tab',
    /**
     * @cfg {String/Number} activeTab A string id or the numeric index of the tab that should be initially
     * activated on render (defaults to undefined).
     */
    activeTab : undefined,
    /**
     * @cfg {Number} tabMargin The number of pixels of space to calculate into the sizing and scrolling of
     * tabs. If you change the margin in CSS, you will need to update this value so calculations are correct
     * with either <tt>{@link #resizeTabs}</tt> or scrolling tabs. (defaults to <tt>2</tt>)
     */
    tabMargin : 2,
    /**
     * @cfg {Boolean} plain </tt>true</tt> to render the tab strip without a background container image
     * (defaults to <tt>false</tt>).
     */
    plain : false,
    /**
     * @cfg {Number} wheelIncrement For scrolling tabs, the number of pixels to increment on mouse wheel
     * scrolling (defaults to <tt>20</tt>).
     */
    wheelIncrement : 20,

    /*
     * This is a protected property used when concatenating tab ids to the TabPanel id for internal uniqueness.
     * It does not generally need to be changed, but can be if external code also uses an id scheme that can
     * potentially clash with this one.
     */
    idDelimiter : '__',

    // private
    itemCls : 'x-tab-item',

    // private config overrides
    elements : 'body',
    headerAsText : false,
    frame : false,
    hideBorders :true,

    // private
    initComponent : function(){
        this.frame = false;
        Ext.ux.tot2ivn.VrTabPanel.superclass.initComponent.call(this);
		
		// Add border
		if (this.border) {
			this.style = 'border: 1px solid #99BBE8; ' + this.style;
		}
		
        this.addEvents(
            /**
             * @event beforetabchange
             * Fires before the active tab changes. Handlers can <tt>return false</tt> to cancel the tab change.
             * @param {TabPanel} this
             * @param {Panel} newTab The tab being activated
             * @param {Panel} currentTab The current active tab
             */
            'beforetabchange',
            /**
             * @event tabchange
             * Fires after the active tab has changed.
             * @param {TabPanel} this
             * @param {Panel} tab The new active tab
             */
            'tabchange',
            /**
             * @event contextmenu
             * Relays the contextmenu event from a tab selector element in the tab strip.
             * @param {TabPanel} this
             * @param {Panel} tab The target tab
             * @param {EventObject} e
             */
            'contextmenu'
        );
        /**
         * @cfg {Object} layoutConfig
         * TabPanel implicitly uses {@link Ext.layout.CardLayout} as its layout manager.
         * <code>layoutConfig</code> may be used to configure this layout manager.
         * <code>{@link #deferredRender}</code> and <code>{@link #layoutOnTabChange}</code>
         * configured on the TabPanel will be applied as configs to the layout manager.
         */
        this.setLayout(new Ext.layout.CardLayout(Ext.apply({
            layoutOnCardChange: this.layoutOnTabChange,
            deferredRender: this.deferredRender
        }, this.layoutConfig)));

        if(this.tabPosition == 'top'){
            this.elements += ',header';
            this.stripTarget = 'header';
        }else {
            this.elements += ',footer';
            this.stripTarget = 'footer';
        }
        if(!this.stack){
            this.stack = Ext.ux.tot2ivn.VrTabPanel.AccessStack();
        }
        this.initItems();
    },

    // private
    onRender : function(ct, position){
        Ext.ux.tot2ivn.VrTabPanel.superclass.onRender.call(this, ct, position);

        if(this.plain){
            var pos = this.tabPosition == 'top' ? 'header' : 'footer';
            this[pos].addClass('x-tab-panel-'+pos+'-plain');
        }

        var st = this[this.stripTarget];

        this.stripWrap = st.createChild({cls:'x-tab-strip-wrap x-tot2ivn-vr-tab-strip-wrap', cn:{
            style: 'margin-top: ' + this.tabMarginTop + 'px;',
			tag:'ul', cls:'x-tab-strip x-tot2ivn-vr-tab-strip x-tab-strip-'+this.tabPosition + ' x-tot2ivn-vr-tab-strip-' + this.tabPosition}});

        var beforeEl = (this.tabPosition=='bottom' ? this.stripWrap : null);
        st.createChild({cls:'x-tab-strip-spacer x-tot2ivn-vr-tab-strip-spacer'}, beforeEl);
        this.strip = new Ext.Element(this.stripWrap.dom.firstChild);

        // create an empty span with class x-tab-strip-text to force the height of the header element when there's no tabs.        
        this.strip.createChild({cls:'x-clear'});

        this.body.addClass('x-tab-panel-body-'+this.tabPosition);

        /**
         * @cfg {Template/XTemplate} itemTpl <p>(Optional) A {@link Ext.Template Template} or
         */
        if(!this.itemTpl){
            var tt = new Ext.Template(
                 '<li class="{cls} x-tot2ivn-vr-tab-strip-title" id="{id}"><a class="x-tab-strip-close"></a>',
                 '<a class="x-tab-right" href="#"><em class="x-tab-left">',
                 '<span class="x-tab-strip-inner"><span class="x-tab-strip-text x-tot2ivn-vr-tab-strip-text {iconCls}">{text}</span></span>',
                 '</em></a></li>'
            );
            tt.disableFormats = true;
            tt.compile();
            Ext.ux.tot2ivn.VrTabPanel.prototype.itemTpl = tt;
        }

        this.items.each(this.initTab, this);
    },

    // private
    afterRender : function(){
        Ext.ux.tot2ivn.VrTabPanel.superclass.afterRender.call(this);
        if(this.autoTabs){
            this.readTabs(false);
        }
        if(this.activeTab !== undefined){
            var item = Ext.isObject(this.activeTab) ? this.activeTab : this.items.get(this.activeTab);
            delete this.activeTab;
            this.setActiveTab(item);
        }
    },

    // private
    initEvents : function(){
        Ext.ux.tot2ivn.VrTabPanel.superclass.initEvents.call(this);
        this.mon(this.strip, {
            scope: this,
            mousedown: this.onStripMouseDown,
            contextmenu: this.onStripContextMenu
        });
        if(this.enableTabScroll){
            this.mon(this.strip, 'mousewheel', this.onWheel, this);
        }
    },

    // private
    findTargets : function(e){
        var item = null,
            itemEl = e.getTarget('li:not(.x-tab-edge)', this.strip);

        if(itemEl){
            item = this.getComponent(itemEl.id.split(this.idDelimiter)[1]);
            if(item.disabled){
                return {
                    close : null,
                    item : null,
                    el : null
                };
            }
        }
        return {
            close : e.getTarget('.x-tab-strip-close', this.strip),
            item : item,
            el : itemEl
        };
    },

    // private
    onStripMouseDown : function(e){
        if(e.button !== 0){
            return;
        }
        e.preventDefault();
        var t = this.findTargets(e);
        if(t.close){
            if (t.item.fireEvent('beforeclose', t.item) !== false) {
                t.item.fireEvent('close', t.item);
                this.remove(t.item);
            }
            return;
        }
        if(t.item && t.item != this.activeTab){
            this.setActiveTab(t.item);
        }
    },

    // private
    onStripContextMenu : function(e){
        e.preventDefault();
        var t = this.findTargets(e);
        if(t.item){
            this.fireEvent('contextmenu', this, t.item, e);
        }
    },

    /**
     * True to scan the markup in this tab panel for <tt>{@link #autoTabs}</tt> using the
     * <tt>{@link #autoTabSelector}</tt>
     * @param {Boolean} removeExisting True to remove existing tabs
     */
    readTabs : function(removeExisting){
        if(removeExisting === true){
            this.items.each(function(item){
                this.remove(item);
            }, this);
        }
        var tabs = this.el.query(this.autoTabSelector);
        for(var i = 0, len = tabs.length; i < len; i++){
            var tab = tabs[i],
                title = tab.getAttribute('title');
            tab.removeAttribute('title');
            this.add({
                title: title,
                contentEl: tab
            });
        }
    },

    // private
    initTab : function(item, index){
        var before = this.strip.dom.childNodes[index],
            p = this.getTemplateArgs(item),
            el = before ?
                 this.itemTpl.insertBefore(before, p) :
                 this.itemTpl.append(this.strip, p),
            cls = 'x-tab-strip-over',
            tabEl = Ext.get(el);

        tabEl.hover(function(){
            if(!item.disabled){
                tabEl.addClass(cls);
            }
        }, function(){
            tabEl.removeClass(cls);
        });

        if(item.tabTip){
            tabEl.child('span.x-tab-strip-text', true).qtip = item.tabTip;
        }
        item.tabEl = el;

        // Route *keyboard triggered* click events to the tab strip mouse handler.
        tabEl.select('a').on('click', function(e){
            if(!e.getPageX()){                
				this.onStripMouseDown(e);				
            }
        }, this, {preventDefault: true});

        item.on({
            scope: this,
            disable: this.onItemDisabled,
            enable: this.onItemEnabled,
            titlechange: this.onItemTitleChanged,
            iconchange: this.onItemIconChanged,
            beforeshow: this.onBeforeShowItem
        });
    },



    /**
     * <p>Provides template arguments for rendering a tab selector item in the tab strip.</p>
     * <p>This method returns an object hash containing properties used by the TabPanel's <tt>{@link #itemTpl}</tt>
     * to create a formatted, clickable tab selector element. The properties which must be returned
     * are:</p><div class="mdetail-params"><ul>
     * <li><b>id</b> : String<div class="sub-desc">A unique identifier which links to the item</div></li>
     * <li><b>text</b> : String<div class="sub-desc">The text to display</div></li>
     * <li><b>cls</b> : String<div class="sub-desc">The CSS class name</div></li>
     * <li><b>iconCls</b> : String<div class="sub-desc">A CSS class to provide appearance for an icon.</div></li>
     * </ul></div>
     * @param {BoxComponent} item The {@link Ext.BoxComponent BoxComponent} for which to create a selector element in the tab strip.
     * @return {Object} An object hash containing the properties required to render the selector element.
     */
    getTemplateArgs : function(item) {
        var cls = item.closable ? 'x-tab-strip-closable' : '';
        if(item.disabled){
            cls += ' x-item-disabled';
        }
        if(item.iconCls){
            cls += ' x-tab-with-icon';
        }
        if(item.tabCls){
            cls += ' ' + item.tabCls;
        }

        return {
            id: this.id + this.idDelimiter + item.getItemId(),
            text: item.title,
            cls: cls,
            iconCls: item.iconCls || ''
        };
    },

    // private
    onAdd : function(c){
        Ext.ux.tot2ivn.VrTabPanel.superclass.onAdd.call(this, c);
        if(this.rendered){
            var items = this.items;
            this.initTab(c, items.indexOf(c));
            if(items.getCount() == 1){
                this.syncSize();
            }
            this.delegateUpdates();
        }
    },

    // private
    onBeforeAdd : function(item){
        var existing = item.events ? (this.items.containsKey(item.getItemId()) ? item : null) : this.items.get(item);
        if(existing){
            this.setActiveTab(item);
            return false;
        }
        Ext.ux.tot2ivn.VrTabPanel.superclass.onBeforeAdd.apply(this, arguments);
        var es = item.elements;
        item.elements = es ? es.replace(',header', '') : es;
        item.border = (item.border === true);
    },

    // private
    onRemove : function(c){
        var te = Ext.get(c.tabEl);
        // check if the tabEl exists, it won't if the tab isn't rendered
        if(te){
            te.select('a').removeAllListeners();
            Ext.destroy(te);
        }
        Ext.ux.tot2ivn.VrTabPanel.superclass.onRemove.call(this, c);
        this.stack.remove(c);
        delete c.tabEl;
        c.un('disable', this.onItemDisabled, this);
        c.un('enable', this.onItemEnabled, this);
        c.un('titlechange', this.onItemTitleChanged, this);
        c.un('iconchange', this.onItemIconChanged, this);
        c.un('beforeshow', this.onBeforeShowItem, this);
        if(c == this.activeTab){
            var next = this.stack.next();
            if(next){
                this.setActiveTab(next);
            }else if(this.items.getCount() > 0){
                this.setActiveTab(0);
            }else{
                this.setActiveTab(null);
            }
        }
        if(!this.destroying){
            this.delegateUpdates();
        }
    },

    // private
    onBeforeShowItem : function(item){
        if(item != this.activeTab){
            this.setActiveTab(item);
            return false;
        }
    },

    // private
    onItemDisabled : function(item){
        var el = this.getTabEl(item);
        if(el){
            Ext.fly(el).addClass('x-item-disabled');
        }
        this.stack.remove(item);
    },

    // private
    onItemEnabled : function(item){
        var el = this.getTabEl(item);
        if(el){
            Ext.fly(el).removeClass('x-item-disabled');
        }
    },

    // private
    onItemTitleChanged : function(item){
        var el = this.getTabEl(item);
        if(el){
            Ext.fly(el).child('span.x-tab-strip-text', true).innerHTML = item.title;
        }
    },

    //private
    onItemIconChanged : function(item, iconCls, oldCls){
        var el = this.getTabEl(item);
        if(el){
            el = Ext.get(el);
            el.child('span.x-tab-strip-text').replaceClass(oldCls, iconCls);
            el[Ext.isEmpty(iconCls) ? 'removeClass' : 'addClass']('x-tab-with-icon');
        }
    },

    /**
     * Gets the DOM element for the tab strip item which activates the child panel with the specified
     * ID. Access this to change the visual treatment of the item, for example by changing the CSS class name.
     * @param {Panel/Number/String} tab The tab component, or the tab's index, or the tabs id or itemId.
     * @return {HTMLElement} The DOM node
     */
    getTabEl : function(item){
        var c = this.getComponent(item);
        return c ? c.tabEl : null;
    },

    // private
    onResize : function(){
        this.header.setStyle('display', 'none');
		Ext.ux.tot2ivn.VrTabPanel.superclass.onResize.apply(this, arguments);
		this.header.setStyle('display', 'block');
        this.delegateUpdates();
    },

    /**
     * Suspends any internal calculations or scrolling while doing a bulk operation. See {@link #endUpdate}
     */
    beginUpdate : function(){
        this.suspendUpdates = true;
    },

    /**
     * Resumes calculations and scrolling at the end of a bulk operation. See {@link #beginUpdate}
     */
    endUpdate : function(){
        this.suspendUpdates = false;
        this.delegateUpdates();
    },

    /**
     * Hides the tab strip item for the passed tab
     * @param {Number/String/Panel} item The tab index, id or item
     */
    hideTabStripItem : function(item){
        item = this.getComponent(item);
        var el = this.getTabEl(item);
        if(el){
            el.style.display = 'none';
            this.delegateUpdates();
        }
        this.stack.remove(item);
    },

    /**
     * Unhides the tab strip item for the passed tab
     * @param {Number/String/Panel} item The tab index, id or item
     */
    unhideTabStripItem : function(item){
        item = this.getComponent(item);
        var el = this.getTabEl(item);
        if(el){
            el.style.display = '';
            this.delegateUpdates();
        }
    },

    // private
    delegateUpdates : function(){
        if(this.suspendUpdates){
            return;
        }
        if(this.resizeTabs && this.rendered){
            // this.autoSizeTabs();
        }
        if(this.enableTabScroll && this.rendered){
            // this.autoScrollTabs();
			// TODO
        }
		
		this.adjustBodySize();
    },

	adjustBodySize : function() {
		// Header
		var tw = this.tabWidth;			
		
		if (Ext.isNumber(tw)) {						
			var bd = this.body,
				bw = bd.getWidth(),
				pw = bw - tw;			

			this.adjustBodyWidth(tw);
			
			// Set left panel width
			if (pw) {
				bd.setWidth(pw);
			}
		}
		
		this.doLayout();
	},		

	
    // private
    autoSizeTabs : function(){
        var count = this.items.length,
            ce = this.tabPosition != 'bottom' ? 'header' : 'footer',
            ow = this[ce].dom.offsetWidth,
            aw = this[ce].dom.clientWidth;

        if(!this.resizeTabs || count < 1 || !aw){ // !aw for display:none
            return;
        }

        var each = Math.max(Math.min(Math.floor((aw-4) / count) - this.tabMargin, this.tabWidth), this.minTabWidth); // -4 for float errors in IE
        this.lastTabWidth = each;
        var lis = this.strip.query('li:not(.x-tab-edge)');
        for(var i = 0, len = lis.length; i < len; i++) {
            var li = lis[i],
                inner = Ext.fly(li).child('.x-tab-strip-inner', true),
                tw = li.offsetWidth,
                iw = inner.offsetWidth;
            inner.style.width = (each - (tw-iw)) + 'px';
        }
    },

    // private
    adjustBodyWidth : function(w){
        if(this.header){
            this.header.setWidth(w);
        }
        if(this.footer){
            this.footer.setWidth(w);
        }
        return w;
    },

    /**
     * Sets the specified tab as the active tab. This method fires the {@link #beforetabchange} event which
     * can <tt>return false</tt> to cancel the tab change.
     * @param {String/Number} item
     * The id or tab Panel to activate. This parameter may be any of the following:
     * <div><ul class="mdetail-params">
     * <li>a <b><tt>String</tt></b> : representing the <code>{@link Ext.Component#itemId itemId}</code>
     * or <code>{@link Ext.Component#id id}</code> of the child component </li>
     * <li>a <b><tt>Number</tt></b> : representing the position of the child component
     * within the <code>{@link Ext.Container#items items}</code> <b>property</b></li>
     * </ul></div>
     * <p>For additional information see {@link Ext.util.MixedCollection#get}.
     */
    setActiveTab : function(item){
        item = this.getComponent(item);
        if(this.fireEvent('beforetabchange', this, item, this.activeTab) === false){
            return;
        }
        if(!this.rendered){
            this.activeTab = item;
            return;
        }
        if(this.activeTab != item){
            if(this.activeTab){
                var oldEl = this.getTabEl(this.activeTab);
                if(oldEl){
                    Ext.fly(oldEl).removeClass('x-tab-strip-active');
                }
            }
            if(item){
                var el = this.getTabEl(item);
                Ext.fly(el).addClass('x-tab-strip-active');
                this.activeTab = item;
                this.stack.add(item);

                this.layout.setActiveItem(item);
                if(this.scrolling){
                    this.scrollToTab(item, this.animScroll);
                }
            }
			
            this.fireEvent('tabchange', this, item);
        }
    },

    /**
     * Returns the Component which is the currently active tab. <b>Note that before the TabPanel
     * first activates a child Component, this method will return whatever was configured in the
     * {@link #activeTab} config option.</b>
     * @return {BoxComponent} The currently active child Component if one <i>is</i> active, or the {@link #activeTab} config value.
     */
    getActiveTab : function(){
        return this.activeTab || null;
    },

    /**
     * Gets the specified tab by id.
     * @param {String} id The tab id
     * @return {Panel} The tab
     */
    getItem : function(item){
        return this.getComponent(item);
    },

    // private
    autoScrollTabs : function(){
        this.pos = this.tabPosition=='bottom' ? this.footer : this.header;
        var count = this.items.length,
            ow = this.pos.dom.offsetHeight,
            tw = this.pos.dom.clientHeight,
            wrap = this.stripWrap,
            wd = wrap.dom,
            cw = wd.offsetHeight,
            pos = this.getScrollPos(),
            l = cw + pos + 10;

        if(!this.enableTabScroll || count < 1 || cw < 20){ // 20 to prevent display:none issues
            return;
        }
        if(l <= tw){
            wd.scrollTop = 0;
            wrap.setHeight(tw);
            if(this.scrolling){
                this.scrolling = false;
                this.pos.removeClass('x-tab-scrolling');
                this.scrollTop.hide();
                this.scrollBottom.hide();
                // See here: http://extjs.com/forum/showthread.php?t=49308&highlight=isSafari
                if(Ext.isAir || Ext.isWebKit){
                    // wd.style.marginTop = '';
                    // wd.style.marginBottom = '';
                }
            }
        }else{
            if(!this.scrolling){
                this.pos.addClass('x-tab-scrolling');
                // See here: http://extjs.com/forum/showthread.php?t=49308&highlight=isSafari
                if(Ext.isAir || Ext.isWebKit){
                    // wd.style.marginTop = '18px';
                    // wd.style.marginBottom = '18px';
                }
            }
            tw -= wrap.getMargins('tb');
            wrap.setHeight(tw > 20 ? tw : 20);
            if(!this.scrolling){
                if(!this.scrollTop){
                    this.createScrollers();
                }else{
                    this.scrollTop.show();
                    this.scrollBottom.show();
                }
            }
            this.scrolling = true;
            if(pos > (l-tw)){ // ensure it stays within bounds
                wd.scrollTop = l-tw;
            }else{ // otherwise, make sure the active tab is still visible
                this.scrollToTab(this.activeTab, false);
            }
            this.updateScrollButtons();
        }
    },

    // private	
    createScrollers : function(){
		
        this.header.addClass('x-tab-scrolling-' + this.tabPosition);
        var w = this.tabWidth,
			sw = this.stripWrap;
		
		// stripWrap		
		sw.setWidth(w);
		sw.applyStyles({'margin': '0'});
		
        // top
        var sl = this.header.insertFirst({
            cls:'x-tab-scroller-top'
        });
        sl.setWidth(w);
        sl.addClassOnOver('x-tab-scroller-top-over');
        this.leftRepeater = new Ext.util.ClickRepeater(sl, {
            interval : this.scrollRepeatInterval,
            handler: this.onscrollTop,
            scope: this
        });
        this.scrollTop = sl;

        // bottom
        var sr = this.stripWrap.insertSibling({
            cls:'x-tab-scroller-bottom'
        }, 'after');
        sr.setWidth(w);
        sr.addClassOnOver('x-tab-scroller-bottom-over');
        this.rightRepeater = new Ext.util.ClickRepeater(sr, {
            interval : this.scrollRepeatInterval,
            handler: this.onscrollBottom,
            scope: this
        });
        this.scrollBottom = sr;
    },

    // private
    getScrollHeight : function(){
        return this.getScrollPos();
    },

    // private
    getScrollPos : function(){
        return parseInt(this.stripWrap.dom.scrollTop, 10) || 0;
    },

    // private
    getScrollArea : function(){
        return parseInt(this.stripWrap.dom.clientHeight, 10) || 0;
    },

    // private
    getScrollAnim : function(){
        return {duration:this.scrollDuration, callback: this.updateScrollButtons, scope: this};
    },

    // private
    getScrollIncrement : function(){
        return this.scrollIncrement || (this.resizeTabs ? this.lastTabWidth+2 : 100);
    },

    /**
     * Scrolls to a particular tab if tab scrolling is enabled
     * @param {Panel} item The item to scroll to
     * @param {Boolean} animate True to enable animations
     */

    scrollToTab : function(item, animate){
        if(!item){
            return;
        }
        var el = this.getTabEl(item),
            pos = this.getScrollPos(),
            area = this.getScrollArea(),
            top = Ext.fly(el).getOffsetsTo(this.stripWrap)[0] + pos,
            bottom = top + el.offsetHeight;
        if(top < pos){
            this.scrollTo(top, animate);
        }else if(bottom > (pos + area)){
            this.scrollTo(bottom - area, animate);
        }
    },

    // private
    scrollTo : function(pos, animate){
        this.stripWrap.scrollTo('top', pos, animate ? this.getScrollAnim() : false);
        if(!animate){
            this.updateScrollButtons();
        }
    },

    onWheel : function(e){
        var d = e.getWheelDelta()*this.wheelIncrement*-1;
        e.stopEvent();

        var pos = this.getScrollPos(),
            newpos = pos + d,
            sw = this.getScrollHeight()-this.getScrollArea();

        var s = Math.max(0, Math.min(sw, newpos));
        if(s != pos){
            this.scrollTo(s, false);
        }
    },

    // private
    onscrollBottom : function(){
        var sw = this.getScrollHeight()-this.getScrollArea(),
            pos = this.getScrollPos(),
            s = Math.min(sw, pos + this.getScrollIncrement());
        if(s != pos){
            this.scrollTo(s, this.animScroll);
        }
    },

    // private
    onscrollTop : function(){
        var pos = this.getScrollPos(),
            s = Math.max(0, pos - this.getScrollIncrement());
        if(s != pos){
            this.scrollTo(s, this.animScroll);
        }
    },

    // private
    updateScrollButtons : function(){
        var pos = this.getScrollPos();
        this.scrollTop[pos === 0 ? 'addClass' : 'removeClass']('x-tab-scroller-left-disabled');
        this.scrollBottom[pos >= (this.getScrollHeight()-this.getScrollArea()) ? 'addClass' : 'removeClass']('x-tab-scroller-right-disabled');
    },

    // private
    beforeDestroy : function() {
        Ext.destroy(this.leftRepeater, this.rightRepeater);
        this.deleteMembers('strip', 'edge', 'scrollTop', 'scrollBottom', 'stripWrap');
        this.activeTab = null;
        Ext.ux.tot2ivn.VrTabPanel.superclass.beforeDestroy.apply(this);
    }

    /**
     * @cfg {Boolean} collapsible
     * @hide
     */
    /**
     * @cfg {String} header
     * @hide
     */
    /**
     * @cfg {Boolean} headerAsText
     * @hide
     */
    /**
     * @property header
     * @hide
     */
    /**
     * @cfg title
     * @hide
     */
    /**
     * @cfg {Array} tools
     * @hide
     */
    /**
     * @cfg {Array} toolTemplate
     * @hide
     */
    /**
     * @cfg {Boolean} hideCollapseTool
     * @hide
     */
    /**
     * @cfg {Boolean} titleCollapse
     * @hide
     */
    /**
     * @cfg {Boolean} collapsed
     * @hide
     */
    /**
     * @cfg {String} layout
     * @hide
     */
    /**
     * @cfg {Boolean} preventBodyReset
     * @hide
     */
});
Ext.reg('vrtabpanel', Ext.ux.tot2ivn.VrTabPanel);

/**
 * See {@link #setActiveTab}. Sets the specified tab as the active tab. This method fires
 * the {@link #beforetabchange} event which can <tt>return false</tt> to cancel the tab change.
 * @param {String/Panel} tab The id or tab Panel to activate
 * @method activate
 */
Ext.ux.tot2ivn.VrTabPanel.prototype.activate = Ext.ux.tot2ivn.VrTabPanel.prototype.setActiveTab;

// private utility class used by TabPanel
Ext.ux.tot2ivn.VrTabPanel.AccessStack = function(){
    var items = [];
    return {
        add : function(item){
            items.push(item);
            if(items.length > 10){
                items.shift();
            }
        },

        remove : function(item){
            var s = [];
            for(var i = 0, len = items.length; i < len; i++) {
                if(items[i] != item){
                    s.push(items[i]);
                }
            }
            items = s;
        },

        next : function(){
            return items.pop();
        }
    };
};
