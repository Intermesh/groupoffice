function buildAdapter(cls, cfg, items?) {
	if(cfg.tagName) {
		cfg.autoEl = cfg.tagName;
		delete cfg.tagName;
	}
	if(cfg.icon) {
		cfg.iconCls = 'ic-'+cfg.icon.replace('_','-');
		delete cfg.icon;
	}
	if(cfg.flex) {
		cfg.cls += ' flex';
	}
	if(cfg.layout) {
		cfg.cls += ' '+cfg.layout;
		delete cfg.layout;
	}
	if(items)
		cfg.items = items;
	return new cls(cfg);
}
function fieldAdapter(cls,cfg) {
	if(cfg.required) {
		cfg.allowBlank = false;
	}
	if(cfg.label) {
		cfg.fieldLabel = cfg.label;
	}
	return buildAdapter(cls,cfg);
}
function columnAdapter(cfg) {
	for(const col of cfg.columns) {
		col.dataIndex = col.id;
	}
	return cfg;
}

const
	comp = (cfg, ...items) => buildAdapter(Ext.Container, cfg, items),
	tbar = (cfg, ...items) => buildAdapter(Ext.Toolbar, cfg, items),
	btn = cfg => buildAdapter(Ext.Button, cfg),
	menu = (cfg, ...items) => {
		for (const i in items) {
			if (typeof items[i] !== 'string')
				items[i] = new Ext.menu.Item(items[i].initialConfig);
		}
		return buildAdapter(Ext.menu.Menu, cfg, items);
	},
	cards = (cfg, ...items) => buildAdapter(Ext.TabPanel, cfg, items),
	datepicker = cfg => {return new Ext.DatePicker(cfg); },
	list = cfg => buildAdapter(Ext.list.ListView, columnAdapter(cfg)),
	store = cfg => {
		cfg.fields = cfg.properties;
		cfg.entityStore = cfg.entity;
		cfg.enableCustomFields = false; // yet to be implemented
		return new go.data.Store(cfg);
	},

	// forms
	form = (cfg, ...items) => buildAdapter(Ext.form.FormPanel, cfg, items),
	select = (cfg) => fieldAdapter(go.form.SelectField, cfg),
	textfield = (cfg) => fieldAdapter(Ext.form.TextField, cfg),
	datefield = (cfg) => fieldAdapter(Ext.form.DateField, cfg),
	fieldset = (cfg, ...items) => buildAdapter(Ext.form.FieldSet, cfg, items),
	checkbox = (cfg) => fieldAdapter(Ext.form.Checkbox, cfg),
	htmlfield = cfg => fieldAdapter(go.form.HtmlEditor,cfg);
