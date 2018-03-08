/**
 * Ext.ux.util.FlashControl
 * Copyright (c) 2009, http://www.siteartwork.de
 *
 * Ext.ux.util.FlashControl is licensed under the terms of the
 *                  GNU Open Source LGPL 3.0
 * license.
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the LGPL as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the LGPL License for more
 * details.
 *
 * You should have received a copy of the GNU LGPL along with
 * this program. If not, see <http://www.gnu.org/licenses/lgpl.html>.
 */

Ext.namespace('Ext.ux.util');

/**
 * This control takes care of managing an Ext.FlashComponent and renders it
 * on top of the page at the coordinates where it usually would be a child
 * component of the container it would have been added to.
 * It registers various listeners to give the user the feeling a container
 * controls the Ext.FlashComponent, though this control takes care of sizing,
 * hiding and rendering the Ext.FlashComponent, based on the specified events
 * this control listens to.
 * Its intended use is with Flash-Movies that are deeply nested within the dom tree
 * (hiding/showing in an Ext powered environment would restart the movie or detach the
 * Flash-object from the DOM tree, depending on the hidden/shown component).
 *
 * WARNING:
 * Rendering of the flashComponent can be managed by the container's afterlayout
 * event and the listener implemented by this class - afterContainerLayout.
 * However, the parent of the FlashComponent can change via "registerComponents()",
 * so there is no "autodestroy" for "flashComponent" if the "container" gets destroyed.
 * You can change this behavior by setting the config-property "autoDestroy" to "true".
 * Otherwise, the FlashComponent will not get destroyed once the container is destroyed.
 *
 * @class Ext.ux.util.FlashControl
 * @singleton
 *
 * @constructor
 * @param {Object} config The configuration options.
 */
Ext.ux.util.FlashControl = function(cfg) {

    cfg = cfg || {};

    var flash = cfg.flashComponent;
    var cont  = cfg.container;
    var fn    = null;

    if (this.getListenerConfig == Ext.emptyFn) {
        fn = cfg.getListenerConfig;
    }

    delete cfg.flashComponent;
    delete cfg.container;
    delete cfg.getListenerConfig;

    Ext.apply(this, cfg);

    this.registerComponents(flash, cont, fn);
};

Ext.ux.util.FlashControl.prototype = {

    /**
     * @cfg {Ext.FlashComponent} flashComponent The flashComponent that should
     * be managed by this control. During runtime, the position attribute of the
     * flashComponent will be set to "abolute"
     */
    flashComponent : null,

    /**
     * @cfg {Ext.Container} container The container that would usually manage the
     * flashComponent. During runtime, the default implementation for the
     * "afterlayout" event will resize the flashComponent using its height and
     * width, and its getX()/getY() values to determine the position of the
     * flashComponent on the screen.
     */
    container : null,

    /**
     * @cfg {Boolean} autoDestroy Whether to auto-destroy flashComponent when the
     * container's destroy event fires. Defaults to false and should not be set to
     * true if you plan to change containers during runtime (see "registerComponents()").
     */
    autoDestroy : false,

    /**
     * @cfg {Boolean} autoDragTracker Whether to check if any of "container"'s parents
     * are draggable, and if that is the case, create a dragTrackers for those elements.
     * The drag tracker does automatically check for dragstart/dragend events and hide/shows
     * the flashComponend accordingly.
     */
    autoDragTracker : false,

    /**
     * @cfg {Mixed} autoAddListener Whether to check container's parents for
     * collapsible property or the functionality to get activated/deactivated.
     * If set to true, the control will install listeners for the following events
     * to this containers:
     * - expand
     * - beforeexpand
     * - collapse
     * - beforecollapse
     * - activate
     * - deactivate
     * - maximize
     * If set to an array, the listeners will only be installed for the above events that
     * appear in the array. This is helpful if you need to create custom listeners for those
     * events for any panel or if you know that there are listeners that could return "false":
     * In this case, the flashComponent might get accidentaly hidden/shown.
     */
    autoAddListeners : false,

    /**
     * @cfg {Boolean} quirksFF Whether to activate some workarounds for flash-reload issues
     * related with FF or not.
     * When this is activated, a workaround will be added to prevent flash movies from restarting
     * when the css class "x-window-maximized-ct" gets added to the document.body.
     * NOTE:
     * This is only neded if you are using windows which are direct children
     * of document.body, if they are maximizable AND if the style declaration "overflow"
     * for document.body is not already set to "hidden"
     */
    quirksFF : false,

    /**
     * @cfg {Boolean} quirksIE Whether to activate some workarounds for sizing issues with
     * IE. Iy your flash movie has an interface for setting sizings and you use this, you should
     * set this to false. Defaults to false.
     */
    quirksIE : false,

    /**
     * @type {Array|Ext.dd.DragTracker} dragTrackers dragTrackers that were automatically added
     */
    dragTrackers : null,

    /**
     * @type {Object} listeners The listener configuration object as returned by
     * "getListenerConfig()"
     */
    listeners : null,

    /**
     * @type {Array} appliedListeners An array containing all currently active listeners
     * which where attached to various components using "addListeners()"
     */
    appliedListeners : null,

    /**
     * @type {Ext.Window} windowParent If the control detects that the container is the
     * child of an Ext.Window, it will retrieve some information, such as zIndex, from this
     * window
     */
    windowParent : null,

    /**
     * @type {Array} autoEvents A list of default events that will be used to add
     * automatically events. If you want to specify a custom list, use "autoAddListeners".
     * This array serves as a whitelist for autoAddListeners.
     */
    autoEvents : [
        'expand',
        'beforeexpand',
        'collapse',
        'beforecollapse',
        'activate',
        'deactivate',
        'maximize'
    ],

    /**
     * @type {Ext.Window} lastWindow If the control detects this container to be nested within
     * a Window, this property will store the first window in the DOM hierarchy, and if
     * the window's container is teh document.body, install some workarounds if needed.
     */
    lastWindow : null,

    /**
     * This is a function that will be called once a container has been configured
     * to be managed by this control, triggered by "registerComponents()".
     * It will be called in the scope of "this". It can be passed via the "config"
     * object when creating an instance of this class, or using "registerComponents"
     * when the container changes.
     * It should return a config object where the keys are valid events any
     * component may fire, and their value should be objects containing the following
     * properties:
     * fn    : the function to call for the specified event (object key). This can
     *         either be a function or a string containing the name of any of
     *         Ext.ux.util.FlashControl's predefined methods (such as 'hideFlash' or
     *         'showFlash'). The scope for the function to call will be defined
     *         in scope, if, and only if the value is nnot of the type string (and thus
     *         assumed to represent a function as defined by Ext.ux.util.FlashControl)
     * items : an array of {Ext.Components} that can trigger the event defined in the
     *         key
     * scope : The scope in which fn should be called. If scope is omitted,
     *         "this" will be used as the default scope.
     * strict : If set to "true", the listener will only be attached to those items
     *          found in the "items"-config,  which are a direct parent of "container",
     *          or container itself.
     *          If set to "false", the listeners will be attached to every item found in
     *          "items", No matter what. Defaults to "true".
     *          If set to any {Ext.Container} instance, the listener will only be applied
     *          if the item in items and container share this {Ext.Container} as their parent.
     *          If set to an array of {Ext.Container}s, the listener will be only applied if
     *          any item shares one of the container found in this array with this.container as
     *          a parent at any level.
     *
     * An example implementation of "getListenerConfig":
     *
     * getListenerConfig : function() {
     *
     *     var items = some_code_to_retrieve_relevant_items();
     *
     *     return {
     *         beforehide : {
     *             fn    : 'hideFlash',
     *             items : items
     *         }
     *
     *         show : {
     *             fn : function() {
     *                 if (this.ownerCt.hidden) {
     *                     return;
     *                 }
     *                 Ext.getCmp('flashId').show();
     *             },
     *             items : items,
     *             scope : this.container.ownerCt
     *         }
     *
     *     };
     * }
     *
     */
    getListenerConfig : Ext.emptyFn,

    /**
     * Registers the components controlled by this class.
     * Can be called during runtime to change the specified properties
     * during runtime, for example if a flash movie should be "attached"
     * to another container, due to drag/drop operations.
     *
     * @param {Ext.FlashComponent} flashComponent
     * @param {Ext.Container} container
     * @param {Function} getListenerConfig If omitted, the currently defined
     * "getListenerConfig" will be used
     */
    registerComponents : function(flashComponent, container, getListenerConfig)
    {
        if (getListenerConfig) {
            this.getListenerConfig = getListenerConfig;
        }

        this.initData(flashComponent, container);
    },

    /**
     * This will init this component and add various default listener
     * to flashComponent and container. If the current instance variables
     * flashComponent or container do not equal to the passed arguments,
     * their default listeners attached by this control will be removed.
     *
     * The following listeners will be attached to "container":
     * - afterContainerLayout (called for the container's afterlayout event)
     * - refreshListeners (called for the container's render event)
     * - removeListeners (called for the container's destroy event)
     *
     * The following listeners will be attached to "flashComponent":
     * - removeListeners (called for the flashComponent's destroy event)
     *
     * If "container" is already rendered and does not equal to the current
     * container managed by this control, "refreshListeners" will be
     * after the default listeners have been attached.
     *
     * If "container" is null, all listeners configured by "getListenerConfig()"
     * will be removed, the same applies if "flashComponent" is null.
     *
     * If a new container or a new flashComponent is passed as an argument,
     * "removeListeners()" will be called beforehand.
     *
     *
     * @param {Ext.FlashComponent} flashcomponent
     * @param {Ext.Container}      flashcomponent
     *
     * @private
     */
    initData : function(flashComponent, container)
    {
        if (this.flashComponent && this.flashComponent != flashComponent) {
            this.flashComponent.un('destroy', this.onDestroy, this);
            this.removeListeners();
            if (!flashComponent) {
                this.flashComponent.destroy();
            }
        }

        if (flashComponent && flashComponent != this.flashComponent) {
            flashComponent.on('destroy', this.onDestroy, this);
        }

        if (this.container && this.container != container) {
            this.container.un('afterrender',      this.refreshListeners,     this);
            this.container.un('destroy',     this.onDestroy,            this);
            this.container.un('afterlayout', this.afterContainerLayout, this);
            this.removeListeners();
        }

        if (container && container != this.container) {
            container.on('afterrender', this.refreshListeners,     this);
            container.on('destroy',     this.onDestroy,            this);
            container.on('afterlayout', this.afterContainerLayout, this);
        }

        if (!flashComponent || !container) {
            this.removeListeners();
        }

        this.flashComponent = flashComponent;
        this.container      = container;

        if (this.container && this.container.rendered) {
            this.refreshListeners();
            this.afterContainerLayout();
        }
    },

    /**
     * Listener for the flashComponent's and container's destroy event.
     * Will remove all listeners and reset "getListenerConfig".
     * The listener will be auto-attached to container and flashComponent
     * during a call to "registerComponents".
     * If autoDestroy is set to true for this instance and the source of the
     * destroy event is the container, currently registered flashComponent will
     * also be destroyed.
     *
     * @param {Ext.Component} component The component that triggered the destroy event
     *
     *
     */
    onDestroy : function(component)
    {
        if (component == this.container) {
            if (this.autoDestroy) {
                this.initData(null, null);
            } else {
                this.initData(null, this.flashComponent);
            }
        } else if (this.component == this.flashComponent) {
            this.initData(this.container, null);
        } else {
            this.initData(null, null);
        }

        this.getListenerConfig = Ext.emptyFn;
    },

    /**
     * This method will be called for the "afterlayout" event of "this.container".
     * If "this.flashComponent" is not yet rendered, it will be rendered directly
     * to "document.body".
     * Its position will be set to "absolute", and width/height/x-position/y-position
     * to the relevant positions of "this.container".
     */
    afterContainerLayout : function()
    {
        var height = this.container.body.getHeight();
        var width  = this.container.body.getWidth();

        if (Ext.isIE && this.quirksIE) {
            // solves an resizing issue with IE. Some flash panels
            // (f.e. normal youtube embed) won't refresh their sizings,
            // though they do in FF.
            this.flashComponent.setSize({height : 0, width  : 0});
            (function(){
                this.flashComponent.setSize({height : height, width  : width});
            }).defer(100, this);
        } else {
            this.flashComponent.setSize({height : height, width  : width});
        }

        this.flashComponent.setPosition(
            this.container.body.getX(),
            this.container.body.getY()
        );

        if (!this.flashComponent.rendered) {
            this.flashComponent.render(document.body);
            this.flashComponent.addClass('ext-ux-flashcontrol');
            this.flashComponent.el.setStyle('position', 'absolute');
        }

        if (this.windowParent) {
            var zIndex = parseInt(this.windowParent.el.getStyle('zIndex'));
            if (zIndex >= 0) {
                this.flashComponent.el.setStyle('zIndex', zIndex+1);
            }
        }
    },

    /**
     * Detaches all listeners, then attaches them again.
     * Beforehand, the "getListenerConfig()" method will be called.
     * This method should be called whener there is a need to remove/
     * add listeners, in favor of calling removeListeners/addListeners
     * directly.
     * For example, if a panel holding a flashComponent is dropped to
     * another panel, "refreshListener" can be called as a drop listener
     * for this panels' "drop" event.
     *
     */
    refreshListeners : function()
    {
        this.removeListeners();
        this.applyListenerConfig();
        this.addListeners();
    },

    /**
     * Calls "getListenerConfig()" in the scope of this instance and applies
     * the returned object to "listeners".
     *
     * @private
     */
    applyListenerConfig : function()
    {
        this.listeners = this.getListenerConfig.call(this);
    },

    /**
     * Traverses the containers and checks if the container sits within a window.
     * The first window found will be stored in "windowParent" to determine the
     * zIndex for the flashComponent. The last window found will be used to check whether
     * this control needs to install some workarounds for FF, if quirksFF is set to true.
     */
    detectWindows : function()
    {
        var container = this.container;

        while (container) {
            if (container instanceof Ext.Window) {
                if (!this.windowParent) {
                    this.windowParent = container;
                }
                this.lastWindow = container;
            }

            container = container.ownerCt;
        }

        if (Ext.isGecko && this.quirksFF && (this.lastWindow.maximizable) && this.lastWindow.container.dom == document.body) {
            var overflow = this.lastWindow.container.getStyle('overflow');
            var rule = Ext.util.CSS.getRule('.ext-ux-flashcontrol-container.x-window-maximized-ct');
            Ext.util.CSS.updateRule('.ext-ux-flashcontrol-container.x-window-maximized-ct', 'overflow', overflow);
            this.lastWindow.container.addClass('ext-ux-flashcontrol-container')
        }
    },

    /**
     * Traverses the container's owners and checks for functionality to auto add
     * listeners to the following events:
     * - collapse
     * - beforecollapse
     * - expand
     * - beforeexpand
     * - activate
     * - deactivate
     * - maximize
     *
     * @private
     */
    autoInstallListeners : function()
    {
        if (!this.autoAddListeners) {
            return;
        }

        var container = this.container;

        var events       = [];
        var activeEvents = {};

        if (this.autoAddListeners === true) {
            events = this.autoEvents;
        } else if (Ext.isArray(this.autoAddListeners)) {
            events = this.autoAddListeners;
        }

        for (var i = 0, len = events.length; i < len; i++) {
            if (this.autoEvents.indexOf(events[i]) != -1) {
                activeEvents[this.autoAddListeners[i]] = true;
            }
        }

        var showFlashComponent = this.showFlashComponent,
            hideFlashComponent = this.hideFlashComponent;


        while (container) {

            // check if autoAddListeners is active
            if (this.autoAddListeners) {

                switch (true) {

                    // check if the container can be activated/deactivated
                    case (container.events['activate'] || container.events['deactivate']):
                        if (activeEvents['activate']) {
                            container.on('activate', showFlashComponent, this);
                            this.appliedListeners.push([
                                container, 'activate', showFlashComponent, this
                            ]);
                        }
                        if (activeEvents['deactivate']) {
                            container.on('deactivate', hideFlashComponent, this);
                            this.appliedListeners.push([
                                container, 'deactivate', hideFlashComponent, this
                            ]);
                        }

                    // add listener for "maximize" event
                    case (container.maximize || container.maximizable):
                        if (activeEvents['maximize']) {
                            container.on('maximize', showFlashComponent, this);
                            this.appliedListeners.push([
                                container, 'maximize', showFlashComponent, this
                            ]);
                        }

                    // add appropriate listeners if container is collapsible
                    case (container.collapsible):

                        switch (true) {
                            case (activeEvents['expand']):
                                container.on('expand', showFlashComponent, this);
                                this.appliedListeners.push([
                                    container, 'expand', showFlashComponent, this
                                ]);
                            case (activeEvents['beforeexpand']):
                                container.on('beforeexpand', hideFlashComponent, this);
                                this.appliedListeners.push([
                                    container, 'beforeexpand', hideFlashComponent, this
                                ]);
                            case (activeEvents['collapse']):
                                container.on('collapse', hideFlashComponent, this);
                                this.appliedListeners.push([
                                    container, 'collapse', hideFlashComponent, this
                                ]);
                            case (activeEvents['beforecollapse']):
                                container.on('beforecollapse', hideFlashComponent, this);
                                this.appliedListeners.push([
                                    container, 'beforecollapse', hideFlashComponent, this
                                ]);
                            break;
                        }
                    break;
                }

            }

            container = container.ownerCt;
        }

    },

    /**
     * Traverses the containers owners and checks whether a "dd" property
     * exists for them, thus assuming that this parent  container is draggable.
     * It will then install DragTrackers to be able to react to dragstart/dragend
     * events.
     * DragTrackers will only be installed if "autoDragTracker" is set to "true".
     *
     * @private
     */
    installDragTrackers : function()
    {
        if (!this.autoDragTracker) {
            return;
        }

        var container   = this.container,
            dragTracker = null,
            dragEl      = null;

        if (!this.dragTrackers) {
            this.dragTrackers = [];
        }

        while (container) {
            if (container.dd) {
                dragEl = container.dd.getDragEl();

                dragTracker = new Ext.dd.DragTracker({
                    el        : new Ext.Element(dragEl),
                    active    : true,
                    autoStart : 100
                });

                dragTracker.on('dragstart', this.dragstart, this);
                dragTracker.on('dragend',   this.dragend, this,
                    (Ext.isIE ? {delay : 100} : {})
                );

                this.appliedListeners.push([
                    dragTracker, 'dragstart', this.dragstart, this
                ]);
                this.appliedListeners.push([
                    dragTracker, 'dragend', this.dragend, this
                ]);

                this.dragTrackers.push(dragTracker);
            }

            container = container.ownerCt;
        }
    },

    /**
     * Attaches all listeners based on the config returned by
     * getListenerConfig().
     *
     * @private
     */
    addListeners : function()
    {
        if (!this.appliedListeners) {
            this.appliedListeners = [];
        }

        this.installDragTrackers();
        this.detectWindows();
        this.autoInstallListeners();

        var eventName   = null,
            func        = null,
            scope       = null,
            items       = null,
            strict      = null,
            config      = null,
            isThisFn    = false,
            item        = null,
            isComponent = false,
            owner       = null,
            strictItem  = false,
            strictCont  = false;

        for (var i in this.listeners) {
            config      = this.listeners[i];
            isThisFn    = Ext.isString(config.fn);
            isComponent = (Ext.isArray(config.strict) || (Ext.isObject(config.strict) && config.strict.getId()))
                          ? true : false;
            eventName   = i;
            scope       = config.scope && !isThisFn ? config.scope : this;
            strict      = config.strict !== false && !isComponent
                          ? true
                          : (isComponent ? config.strict : false);
            items       = config.items;
            func        = isThisFn ? this[config.fn] : config.fn;

            for (var a = 0, lena = items.length; a < lena; a++) {
                item = items[a];
                if (strict === false) {
                    item.on(eventName, func, scope);
                    this.appliedListeners.push([
                        item, eventName, func, scope
                    ]);
                } else {
                    if (strict === true) {
                        // strict! check if the current item is a direct parent
                        // of this.container, or the container itself
                        owner = this.container;
                        while (owner) {
                            if (owner == item) {
                                item.on(eventName, func, scope);
                                this.appliedListeners.push([
                                    item, eventName, func, scope
                                ]);
                                break;
                            }
                            owner = owner.ownerCt;
                        }
                    } else if (isComponent) {
                        strict = [].concat(strict);
                        // attach only those listeners to the item if it shares
                        // "strict" (points to an Ext.Component) as an owner with
                        // this.container
                        for (var b = 0, lenb = strict.length; b < lenb; b++) {
                            if (strict[b].findById(this.container.getId()) &&
                                strict[b].findById(item.getId())) {
                                item.on(eventName, func, scope);
                                this.appliedListeners.push([
                                    item, eventName, func, scope
                                ]);
                            }
                        }
                    }
                }
            }
        }

    },

    /**
     * Removes all currently applied listeners found in "appliedListeners"
     *
     * @private
     */
    removeListeners : function()
    {
        if (this.dragTrackers) {
            for (var i = 0, len = this.dragTrackers.length; i < len; i++) {
                this.dragTrackers[i].destroy();
            }
            this.dragTrackers = null;
        }

        if (this.quirksFF && this.lastWindow && this.lastWindow.container.dom == document.body) {
            this.lastWindow.container.removeClass('ext-ux-flashcontrol-container')
        }

        this.lastWindow   = null;
        this.windowParent = null;

        if (!this.appliedListeners) {
            return;
        }

        var listener = null;
        for (var i = 0, len = this.appliedListeners.length; i < len; i++) {
            listener = this.appliedListeners[i];
            listener[0].un(listener[1], listener[2], listener[3]);
        }

        this.appliedListeners = [];
    },

    /**
     * Default implementation for hiding the flashComponent.
     */
    hideFlashComponent : function()
    {
        this.flashComponent.hide();
    },

    /**
     * Default implementation for showing the flashComponent.
     */
    showFlashComponent : function()
    {
        this.afterContainerLayout();
        this.flashComponent.show();
    },

    /**
     * Default implementation for dragstart.
     *
     */
    dragstart : function()
    {
        this.flashComponent.hide();
    },

    /**
     * Default implementation for dragend.
     *
     */
    dragend : function()
    {
        this.afterContainerLayout();
        this.flashComponent.show();
    }
};