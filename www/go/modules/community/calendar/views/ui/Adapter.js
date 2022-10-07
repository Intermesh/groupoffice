const xadapter = {
	comp(cfg, ...items) {
		cfg.items = items;
		return new Ext.Component(cfg);
	},
	tbar(cfg, ...items) {
		cfg.items = items;
		return new Ext.Toolbar(cfg);
	},
	btn(cfg) {
		return new Ext.Button(cfg);
	},
	menu(cfg, ...items) {
		cfg.items = items;
		return new Ext.menu.Menu(cfg);
	},
	cards(cfg, ...items) {
		cfg.items = items;
		return new Ext.TabPanel(cfg);
	},
	datepicker(cfg) {
		return new Ext.DatePicker(cfg);
	},
	table(cfg) {
		return new Ext.Component(cfg);
	}
};