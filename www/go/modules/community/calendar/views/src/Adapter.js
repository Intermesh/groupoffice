function buildAdapter(cls, cfg, items) {
    if (cfg.tagName) {
        cfg.autoEl = cfg.tagName;
        delete cfg.tagName;
    }
    if (cfg.icon) {
        cfg.iconCls = 'ic-' + cfg.icon.replace('_', '-');
        delete cfg.icon;
    }
    if (cfg.flex) {
        cfg.cls += ' flex';
    }
    if (cfg.layout) {
        cfg.cls += ' ' + cfg.layout;
        delete cfg.layout;
    }
    if (items)
        cfg.items = items;
    return new cls(cfg);
}
function fieldAdapter(cls, cfg) {
    if (cfg.required) {
        cfg.allowEmpty = false;
    }
    if (cfg.label) {
        cfg.fieldLabel = cfg.label;
    }
    return buildAdapter(cls, cfg);
}
function columnAdapter(cfg) {
    for (var _i = 0, _a = cfg.columns; _i < _a.length; _i++) {
        var col = _a[_i];
        col.dataIndex = col.id;
    }
    return cfg;
}
var comp = function (cfg) {
    var items = [];
    for (var _i = 1; _i < arguments.length; _i++) {
        items[_i - 1] = arguments[_i];
    }
    return buildAdapter(Ext.Container, cfg, items);
}, tbar = function (cfg) {
    var items = [];
    for (var _i = 1; _i < arguments.length; _i++) {
        items[_i - 1] = arguments[_i];
    }
    return buildAdapter(Ext.Toolbar, cfg, items);
}, btn = function (cfg) { return buildAdapter(Ext.Button, cfg); }, menu = function (cfg) {
    var items = [];
    for (var _i = 1; _i < arguments.length; _i++) {
        items[_i - 1] = arguments[_i];
    }
    for (var i in items) {
        if (typeof items[i] !== 'string')
            items[i] = new Ext.menu.Item(items[i].initialConfig);
    }
    return buildAdapter(Ext.menu.Menu, cfg, items);
}, cards = function (cfg) {
    var items = [];
    for (var _i = 1; _i < arguments.length; _i++) {
        items[_i - 1] = arguments[_i];
    }
    return buildAdapter(Ext.TabPanel, cfg, items);
}, datepicker = function (cfg) { return new Ext.DatePicker(cfg); }, list = function (cfg) { return buildAdapter(Ext.list.ListView, columnAdapter(cfg)); }, store = function (cfg) {
    cfg.fields = cfg.properties;
    cfg.entityStore = cfg.entity;
    cfg.enableCustomFields = false;
    return new go.data.Store(cfg);
};
// forms
form = function (cfg) {
    var items = [];
    for (var _i = 1; _i < arguments.length; _i++) {
        items[_i - 1] = arguments[_i];
    }
    return buildAdapter(Ext.form.FormPanel, cfg, items);
},
    select = function (cfg) { return fieldAdapter(go.form.SelectField, cfg); },
    textfield = function (cfg) { return fieldAdapter(Ext.form.TextField, cfg); },
    datefield = function (cfg) { return fieldAdapter(Ext.form.DateField, cfg); },
    fieldset = function (cfg) {
        var items = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            items[_i - 1] = arguments[_i];
        }
        return buildAdapter(Ext.form.FieldSet, cfg, items);
    },
    checkbox = function (cfg) { return fieldAdapter(Ext.form.Checkbox, cfg); },
    htmlfield = function (cfg) { return fieldAdapter(go.form.HtmlEditor, cfg); };
