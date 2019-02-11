/* global go */

go.modules.core.core.Links = {
	getAll : function() {
		var e = [];
						
		go.Modules.getAvailable().forEach(function (m) {
			var pkg = m.package || "legacy";
			if(go.Modules.registered[pkg][m.name].links) {

				var c = go.Modules.registered[pkg][m.name].links;
				c.forEach(function(l) {
					if(!l.title) {
						l.title = t(l.entity, m.package, m.name);
					}
					if(!l.iconCls) {
						l.iconCls = "entity " + l.entity;
					}
				});
				e = e.concat(c);
			}
		});	
		
		e.sort(function (a, b) {
			return a.title.localeCompare(b.title);
		});
		
		return e;
	}
};
