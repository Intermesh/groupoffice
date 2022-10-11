interface StoreCfg {
	relations: any
	links: any[]
	filters: any[]
}

function $regApp(name: string, cfg: any) {
	var old: any = {
		mainPanel: {},
		initModule: function (self) {
			cfg.add =()=>{} // fix addPanel()  to except instances
			cfg.init();
			cfg.ui.iconCls = 'ic'; /// blehh
			self.panelConfig = cfg.ui;
			for(const path in cfg.routes) {
				// @ts-ignore
				go.Router.add(path, cfg.routes[path]);
			}
		}
	};

	const entities = [];
	for(const sname in cfg.stores) {
		cfg.stores[sname].name = sname;
		entities.push(cfg.stores[sname]);
	}
	if(entities.length)
		old.entities = entities;
	// @ts-ignore
	go.Modules.register("community", name, old);
}