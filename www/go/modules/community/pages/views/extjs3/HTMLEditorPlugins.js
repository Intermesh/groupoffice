/*!
 * MIT
 */
/**
 * @author Based on plugin made by Shea Frederick - http://www.vinylfox.com
 * @class Ext.ux.form.HtmlEditor.MidasCommand
 * @extends Ext.util.Observable
 * <p>A base plugin for extending to create standard Midas command buttons.</p>
 */
Ext.ns('Ext.ux.form.HtmlEditor');

Ext.ux.form.HtmlEditor.MidasCommand = Ext.extend(Ext.util.Observable, {
    // private
    init: function (cmp) {
	this.cmp = cmp;
	this.btns = [];
	this.combo = [];
	this.cmp.on('render', this.onRender, this);
	this.cmp.on('initialize', this.onInit, this, {
	    delay: 100,
	    single: true
	});
    },
    // private
    onInit: function () {
	Ext.EventManager.on(this.cmp.getDoc(), {
	    'mousedown': this.onEditorEvent,
	    'dblclick': this.onEditorEvent,
	    'click': this.onClick,
	    'keyup': this.onEditorEvent,
	    buffer: 100,
	    scope: this
	});
    },
    // private
    onRender: function () {
	var midasCmdButton, tb = this.cmp.getToolbar(), btn;
	Ext.each(this.midasBtns, function (b) {
	    if (typeof (b) == 'object') {
		// Certain commands also require a value to be passed such as the heading plugin.
		if (!b.value) {
		    b.value = "";
		}
		midasCmdButton = {
		    tabIndex: -1,

		    iconCls: 'x-edit-' + b.cmd,
		    handler: function () {
			this.cmp.relayCmd(b.cmd, b.value);
		    },
		    scope: this,
		    tooltip: b.tooltip ||
			    {
				title: b.title
			    },
		    overflowText: b.overflowText || b.title
		};
	    } else {
		midasCmdButton = new Ext.Toolbar.Separator();
	    }
	    btn = tb.addButton(midasCmdButton);
	    if (b.enableOnSelection) {
		btn.disable();
	    }
	    this.btns.push(btn);
	}, this);
	this.combo = this.cmp.getToolbar().findByType('combo');
    },
    onClick: function () {
	//closes any comboboxes that might be opened.
	Ext.each(this.combo, function (b) {
	    if (b.isExpanded()) {
		b.collapse();
	    }
	});

	this.onEditorEvent();
    },
    // private
    onEditorEvent: function () {
	var doc = this.cmp.getDoc();
	Ext.each(this.btns, function (b, i) {
	    if (this.midasBtns[i].enableOnSelection || this.midasBtns[i].disableOnSelection) {
		if (doc.getSelection) {
		    if ((this.midasBtns[i].enableOnSelection && doc.getSelection() !== '') || (this.midasBtns[i].disableOnSelection && doc.getSelection() === '')) {
			b.enable();
		    } else {
			b.disable();
		    }
		} else if (doc.selection) {
		    if ((this.midasBtns[i].enableOnSelection && doc.selection.createRange().text !== '') || (this.midasBtns[i].disableOnSelection && doc.selection.createRange().text === '')) {
			b.enable();
		    } else {
			b.disable();
		    }
		}
	    }
	    if (this.midasBtns[i].monitorCmdState) {
		b.toggle(doc.queryCommandState(this.midasBtns[i].cmd));
	    }
	}, this);
    }
});

/**
 * @author based on the HeadingMenu by Shea Frederick - http://www.vinylfox.com
 * @class Ext.ux.form.HtmlEditor.HeadingMenuEdited
 * @extends Ext.util.Observable
 * <p>A plugin that creates a menu on the HtmlEditor for selecting a heading size. This variant only has 2 headings and normal text. 
 * This is an edited version of the HeadingMenu method to be used in the pages module.</p>
 */
Ext.ux.form.HtmlEditor.HeadingMenuModified = Ext.extend(Ext.util.Observable, {
    init: function (cmp) {
	this.cmp = cmp;
	this.cmp.on('render', this.onRender, this);
    },
    // private
    onRender: function () {
	var cmp = this.cmp;
	btn = this.cmp.getToolbar().addItem({
	    xtype: 'combo',
	    displayField: 'display',
	    valueField: 'value',
	    name: 'headingsize',
	    forceSelection: true,
	    mode: 'local',
	    triggerAction: 'all',
	    width: dp(150),
	    emptyText: 'Heading',
	    store: {
		xtype: 'arraystore',
		autoDestroy: true,
		fields: ['value', 'display'],
		data: [['p', 'Normal text'], ['H1', 'Heading 1'], ['H2', 'Heading 2']]
	    },
	    listeners: {
		'select': function (combo, rec) {
		    this.relayCmd('formatblock', '<' + rec.get('value') + '>');
		    combo.reset();
		},
		scope: cmp
	    }
	});
    },
});

