// Append templates for links to this map
GO.panels.LinksTpl = new Ext.XTemplate('<tpl if="values.links"><div class="icons"><tpl for="links">\
		<tpl if="type !== this.previousType(xindex, parent)">\
			<hr class="indent">\
			<h5 class="indent">{type}s</h5>\
		</tpl>\
		{[this[values.type] ? this[values.type](values, parent, xindex, xcount) : this.default(values, parent, xindex, xcount)]}\
	</tpl></div></tpl>', {
	previousType : function(xindex, parent) {
		return parent.links && parent.links[xindex-2] && parent.links[xindex-2].type;
	},
	default: function(values, parent, xindex, xcount) {
			Ext.apply(values, {parent: parent, xindex: xindex, xcount:xcount});
			return (new Ext.XTemplate('<a>\
			<tpl if="type !== parent.previousType">\
				<i class="label go-model-icon-{[values.model_name.replace(/\\\\/g,"_")]}" ext:qtip="{type}"></i>\
			</tpl>\
			<span>{name}</span>\
			<label>{mtime}</label>\
			<span>{description}</span>\
		</a>')).apply(values);
	},
	Event: function(values, parent, xindex, xcount) {
			Ext.apply(values, {parent: parent, xindex: xindex, xcount:xcount});
			return (new Ext.XTemplate('<a>\
			bam {name}\
		</a>')).apply(values);
	}
});
