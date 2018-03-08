GO.grid.RemoteGridTotals  = function(config){
	Ext.apply(this, config);
};

Ext.extend(GO.grid.RemoteGridTotals , Ext.util.Observable, {
	init : function(grid){
		this.grid = grid;
		this.cm = grid.getColumnModel();
		this.view = grid.getView();

		var v = this.view;

		v.layout = this.layout.createDelegate(this);

		v.afterMethod('refresh', this.refreshSummary, this);
		v.afterMethod('refreshRow', this.refreshSummary, this);
		v.afterMethod('onColumnWidthUpdated', this.refreshSummary, this);
		v.afterMethod('onAllColumnWidthsUpdated', this.refreshSummary, this);
		v.afterMethod('onColumnHiddenUpdated', this.refreshSummary, this);
		v.afterMethod('onUpdate', this.refreshSummary, this);
		v.afterMethod('onRemove', this.refreshSummary, this);

		if(!this.rowTpl){
			this.rowTpl = new Ext.Template(
				'<div class="x-grid3-summary-row" style="{tstyle}">',
				'<table class="x-grid3-summary-table" border="0" cellspacing="0" cellpadding="0" style="{tstyle}">',
				'<tbody><tr>{cells}</tr></tbody>',
				'</table></div>'
				);
			this.rowTpl.disableFormats = true;
		}
		this.rowTpl.compile();

		if(!this.cellTpl){
			this.cellTpl = new Ext.Template(
				'<td class="x-grid3-col x-grid3-cell x-grid3-td-{id} {css}" style="{style}">',
				'<div class="x-grid3-cell-inner x-grid3-col-{id}" unselectable="on">{value}</div>',
				"</td>"
				);
			this.cellTpl.disableFormats = true;
		}
		this.cellTpl.compile();
	},

	renderSummary : function(o, cs){
		cs = cs || this.view.getColumnData();
		var cfg = this.cm.config;

		var buf = [], c, p = {}, cf, last = cs.length-1;
		for(var i = 0, len = cs.length; i < len; i++){
			c = cs[i];
			
			cf = cfg[i];
			p.id = c.id;
			p.style = c.style;
			p.css = i == 0 ? 'x-grid3-cell-first ' : (i == last ? 'x-grid3-cell-last ' : '');			
			p.value=!Ext.isEmpty(o.data[c.name]) ? o.data[c.name] : '';
			if(cf.summaryRenderer){
				p.value=cf.summaryRenderer(p.value, p, o);
			}
			
			buf[buf.length] = this.cellTpl.apply(p);
		}
		

		return this.rowTpl.apply({
			tstyle: 'width:'+this.view.getTotalWidth()+';',
			cells: buf.join('')
		});
	},

	calculate : function(rs, cs){
		var data = {}, r, c, cfg = this.cm.config, cf;
		for(var j = 0, jlen = rs.length; j < jlen; j++){
			r = rs[j];
			for(var i = 0, len = cs.length; i < len; i++){
				c = cs[i];
				cf = cfg[i];
				if(cf && cf.summaryType){
					data[c.name] = GO.grid.RemoteGridTotals .Calculations[cf.summaryType](data[c.name] || 0, r, c.name, data);
				}
			}
		}
		return data;
	},

	layout : function(){
		if (!this.view.summary)
			this.view.summary = Ext.DomHelper.insertAfter(this.view.mainBody.dom.parentNode, {
				tag:'div'
			}, true);

		if(!this.view.mainBody){
			return;
		}
		var g = this.grid;
		var c = g.getGridEl(), cm = this.cm,
		expandCol = g.autoExpandColumn,
		gv = this.view;

		var csize = c.getSize(true);
		var vw = csize.width;

		if(!vw || !csize.height){ // display: none?
			return;
		}

		if(g.autoHeight){
			this.view.scroller.dom.style.overflow = 'visible';
		}else{
			var smHeight = this.view.summary.getHeight();
			var hdHeight = this.view.mainHd.getHeight();

			this.view.el.setSize(csize.width, csize.height + smHeight);

			var vh = csize.height - (hdHeight) - (smHeight);

			this.view.scroller.setSize(vw, vh);
			this.view.innerHd.style.width = (vw)+'px';
		}
		if(this.view.forceFit){
			if(this.view.lastViewWidth != vw){
				this.view.fitColumns(false, false);
				this.view.lastViewWidth = vw;
			}
		}else {
			this.view.autoExpand();
		}
		this.view.onLayout(vw, vh);
	},

	doWidth : function(col, w, tw){
		var gs = this.view.getRows(), s;
		for(var i = 0, len = gs.length; i < len; i++){
			s = gs[i].childNodes[2];
			s.style.width = tw;
			s.firstChild.style.width = tw;
			s.firstChild.rows[0].childNodes[col].style.width = w;
		}
	},

	doAllWidths : function(ws, tw){
		var gs = this.view.getRows(), s, cells, wlen = ws.length;
		for(var i = 0, len = gs.length; i < len; i++){
			s = gs[i].childNodes[2];
			s.style.width = tw;
			s.firstChild.style.width = tw;
			cells = s.firstChild.rows[0].childNodes;
			for(var j = 0; j < wlen; j++){
				cells[j].style.width = ws[j];
			}
		}
	},

	doHidden : function(col, hidden, tw){
		var gs = this.view.getRows(), s, display = hidden ? 'none' : '';
		for(var i = 0, len = gs.length; i < len; i++){
			s = gs[this.view.getRows()-1].childNodes[2];
			s.style.width = tw;
			s.firstChild.style.width = tw;
			s.firstChild.rows[0].childNodes[col].style.display = display;
		}
	},

	// Note: requires that all (or the first) record in the
	// group share the same group value. Returns false if the group
	// could not be found.
	refreshSummary : function(){
		var g = this.grid, cm = g.colModel, ds = g.store, stripe = g.stripeRows;
		var colCount = cm.getColumnCount();

		if(ds.getCount() < 1){
			return "";
		}

		var cs = this.view.getColumnData();

		//var startRow = startRow || 0;
		//var endRow = typeof endRow == "undefined"? ds.getCount()-1 : endRow;

		// records to render
		//var rs = ds.getRange();

		var buf = [];
		//var data = this.calculate(rs, cs);
		var data = ds.reader.jsonData.totals;
		
		buf.push('</div>', this.renderSummary({
			data: data
		}, cs), '</div>');

		this.view.summary.update(buf.join(''));
		this.view.layout();
	},

	getSummaryNode : function(){
		return this.view.summary
	},

	showSummaryMsg : function(groupValue, msg){
		var gid = this.view.getGroupId(groupValue);
		var node = this.getSummaryNode(gid);
		if(node){
			node.innerHTML = '<div class="x-grid3-summary-msg">' + msg + '</div>';
		}
	}
});