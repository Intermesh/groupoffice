/*
 * Ext JS Library 2.2.1
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

Ext.grid.GroupSummary = function(config){
    Ext.apply(this, config);
};

Ext.extend(Ext.grid.GroupSummary, Ext.util.Observable, {
    init : function(grid){
        this.grid = grid;
        this.cm = grid.getColumnModel();
        this.view = grid.getView();

        var v = this.view;
        v.doGroupEnd = this.doGroupEnd.createDelegate(this);

        v.afterMethod('onColumnWidthUpdated', this.doWidth, this);
        v.afterMethod('onAllColumnWidthsUpdated', this.doAllWidths, this);
        v.afterMethod('onColumnHiddenUpdated', this.doHidden, this);
        v.afterMethod('onUpdate', this.doUpdate, this);
        v.afterMethod('onRemove', this.doRemove, this);

        if(!this.rowTpl){
            this.rowTpl = new Ext.Template(
                '<div id="x-grid3-summary-row-'+this.grid.id+'" class="x-grid3-summary-row" style="{tstyle}">',
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

    toggleSummaries : function(visible){
        var el = this.grid.getGridEl();
        if(el){
            if(visible === undefined){
                visible = el.hasClass('x-grid-hide-summary');
            }
            el[visible ? 'removeClass' : 'addClass']('x-grid-hide-summary');
        }
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
            if(cf.summaryType || cf.summaryRenderer){
                p.value = (cf.summaryRenderer || c.renderer)(o.data[c.name], p, o);
            }else{
                p.value = '';
            }
            if(p.value == undefined || p.value === "") p.value = "&#160;";
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
                if(cf.summaryType){
                    data[c.name] = Ext.grid.GridSummary.Calculations[cf.summaryType](data[c.name] || 0, r, c.name, data);
                }
            }
        }
        return data;
    },

    doGroupEnd : function(buf, g, cs, ds, colCount){
        var data = this.calculate(g.rs, cs);
        buf.push('</div>', this.renderSummary({data: data}, cs), '</div>');
    },

    doWidth : function(col, w, tw){
        var gs = this.view.getGroups(), s;
        for(var i = 0, len = gs.length; i < len; i++){
            s = gs[i].childNodes[2];
            s.style.width = tw;
            s.firstChild.style.width = tw;
            s.firstChild.rows[0].childNodes[col].style.width = w;
        }
    },

    doAllWidths : function(ws, tw){
        var gs = this.view.getGroups(), s, cells, wlen = ws.length;
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
        var gs = this.view.getGroups(), s, display = hidden ? 'none' : '';
        for(var i = 0, len = gs.length; i < len; i++){
            s = gs[i].childNodes[2];
            s.style.width = tw;
            s.firstChild.style.width = tw;
            s.firstChild.rows[0].childNodes[col].style.display = display;
        }
    },

    // Note: requires that all (or the first) record in the 
    // group share the same group value. Returns false if the group
    // could not be found.
    refreshSummary : function(groupValue){
        return this.refreshSummaryById(this.view.getGroupId(groupValue));
    },

    getSummaryNode : function(gid){
        var g = Ext.fly(gid, '_gsummary');
        if(g){
            return g.down('.x-grid3-summary-row', true);
        }
        return null;
    },

    refreshSummaryById : function(gid){
        var g = document.getElementById(gid);
        if(!g){
            return false;
        }
        var rs = [];
        this.grid.store.each(function(r){
            if(r._groupId == gid){
                rs[rs.length] = r;
            }
        });
        var cs = this.view.getColumnData();
        var data = this.calculate(rs, cs);
        var markup = this.renderSummary({data: data}, cs);

        var existing = this.getSummaryNode(gid);
        if(existing){
            g.removeChild(existing);
        }
        Ext.DomHelper.append(g, markup);
        return true;
    },

    doUpdate : function(ds, record){
        this.refreshSummaryById(record._groupId);
    },

    doRemove : function(ds, record, index, isUpdate){
        if(!isUpdate){
            this.refreshSummaryById(record._groupId);
        }
    },

    showSummaryMsg : function(groupValue, msg){
        var gid = this.view.getGroupId(groupValue);
        var node = this.getSummaryNode(gid);
        if(node){
            node.innerHTML = '<div class="x-grid3-summary-msg">' + msg + '</div>';
        }
    }
});

Ext.ns("Ext.grid.GridSummary"); // namespace Ext.grid.GridSummary

Ext.grid.GridSummary = function(config) {
    Ext.apply(this, config);
};

Ext.extend(Ext.grid.GridSummary, Ext.util.Observable, {
  init : function(grid) {
    this.grid = grid;
    this.cm = grid.getColumnModel();
    this.view = grid.getView();
    var v = this.view;

    v.onLayout = this.onLayout; // override GridView's onLayout() method

    v.afterMethod('render', this.refreshSummary, this);
    v.afterMethod('refresh', this.refreshSummary, this);
    v.afterMethod('setSumValue', this.test, this);
    v.afterMethod('syncScroll', this.syncSummaryScroll, this);
    v.afterMethod('onColumnWidthUpdated', this.doWidth, this);
    v.afterMethod('onAllColumnWidthsUpdated', this.doAllWidths, this);
    v.afterMethod('onColumnHiddenUpdated', this.doHidden, this);
    v.afterMethod('onUpdate', this.refreshSummary, this);
    v.afterMethod('onRemove', this.refreshSummary, this);

    // update summary row on store's add / remove / clear events
    grid.store.on('add', this.refreshSummary, this);
    grid.store.on('remove', this.refreshSummary, this);
    grid.store.on('clear', this.refreshSummary, this);

    if (!this.rowTpl) {
      this.rowTpl = new Ext.Template(
        '<div class="x-grid3-summary-row x-grid3-gridsummary-row-offset">',
          '<table class="x-grid3-summary-table" border="0" cellspacing="0" cellpadding="0" style="{tstyle}">',
            '<tbody><tr>{cells}</tr></tbody>',
          '</table>',
        '</div>'
      );
      this.rowTpl.disableFormats = true;
    }
    this.rowTpl.compile();

    if (!this.cellTpl) {
      this.cellTpl = new Ext.Template(
        '<td class="x-grid3-col x-grid3-cell x-grid3-td-{id} {css}" style="{style}">',
          '<div class="x-grid3-cell-inner x-grid3-col-{id}" unselectable="on">{value}</div>',
        "</td>"
      );
      this.cellTpl.disableFormats = true;
    }
    this.cellTpl.compile();
  },

  calculate : function(rs, cs) {
    var data = {}, r, c, cfg = this.cm.config, cf;
    for (var i = 0, len = cs.length; i < len; i++) {
      c = cs[i];
      cf = cfg[i];
      data[c.name] = 0;
      for (var j = 0, jlen = rs.length; j < jlen; j++) {
        r = rs[j];
        if (cf && cf.summaryType) {
          data[c.name] = Ext.grid.GridSummary.Calculations[cf.summaryType](data[c.name] || 0, r, c.name, data);
        }
      }
    }

    return data;
  },

  onLayout : function(vw, vh) {
    if ('number' != Ext.type(vh)) { // prevent onLayout from choking when height:'auto'
      return;
    }
    // note: this method is scoped to the GridView
    if (!this.grid.getGridEl().hasClass('x-grid-hide-gridsummary')) {
      // readjust gridview's height only if grid summary row is visible
      this.scroller.setHeight(vh - this.summary.getHeight());
    }
  },

  syncSummaryScroll : function() {
    var mb = this.view.scroller.dom;
    this.view.summaryWrap.dom.scrollLeft = mb.scrollLeft;
    this.view.summaryWrap.dom.scrollLeft = mb.scrollLeft; // second time for IE (1/2 time first fails, other browsers ignore)
  },

  doWidth : function(col, w, tw) {
    var s = this.view.summary.dom;
    s.firstChild.style.width = tw;
    s.firstChild.rows[0].childNodes[col].style.width = w;
  },

  doAllWidths : function(ws, tw) {
    var s = this.view.summary.dom, wlen = ws.length;
    s.firstChild.style.width = tw;
    cells = s.firstChild.rows[0].childNodes;
    for (var j = 0; j < wlen; j++) {
      cells[j].style.width = ws[j];
    }
  },

  doHidden : function(col, hidden, tw) {
    var s = this.view.summary.dom;
    var display = hidden ? 'none' : '';
    s.firstChild.style.width = tw;
    s.firstChild.rows[0].childNodes[col].style.display = display;
  },
  putSumInfo:null,
  setSumValue : function(jsonV) {
    var cs = this.view.getColumnData();
    var buf = [], c, p = {}, last = cs.length-1;

    for (var i = 0, len = cs.length; i < len; i++) {
      c = cs[i];
      p.id = c.id; 
      p.style = c.style;
      p.css = i == 0 ? 'x-grid3-cell-first ' : (i == last ? 'x-grid3-cell-last ' : '');
      if (jsonV&&jsonV[c.name]) {
		p.value = jsonV[c.name];
      } else {
        p.value = '';
      }
      if (p.value == undefined || p.value === "") p.value = " ";
      buf[buf.length] = this.cellTpl.apply(p);
    }

    if (!this.view.summaryWrap) {
      this.view.summaryWrap = Ext.DomHelper.insertAfter(this.view.scroller, {
        tag: 'div',
        cls: 'x-grid3-gridsummary-row-inner'
      }, true);
    } else {
      this.view.summary.remove();
    }
    this.putSumInfo = this.rowTpl.apply({
      tstyle: 'width:' + this.view.getTotalWidth() + ';',
      cells: buf.join('')
    });
    this.view.summary = this.view.summaryWrap.insertHtml('afterbegin',this.putSumInfo, true);
  },
  refreshSumValue:function() {
    if (!this.view.summaryWrap) {
      this.view.summaryWrap = Ext.DomHelper.insertAfter(this.view.scroller, {
        tag: 'div',
        cls: 'x-grid3-gridsummary-row-inner'
      }, true);
    } else {
      this.view.summary.remove();
    }
    this.view.summary = this.view.summaryWrap.insertHtml('afterbegin', this.putSumInfo, true);
  },
  renderSummary : function(o, cs) {
    cs = cs || this.view.getColumnData();
    var cfg = this.cm.config;
    var buf = [], c, p = {}, cf, last = cs.length-1;

    for (var i = 0, len = cs.length; i < len; i++) {
      c = cs[i];
      cf = cfg[i];
      p.id = c.id;
      p.style = c.style;
      p.css = i == 0 ? 'x-grid3-cell-first ' : (i == last ? 'x-grid3-cell-last ' : '');
      if (cf.summaryType || cf.summaryRenderer) {
        p.value = (cf.summaryRenderer || c.renderer)(o.data[c.name], p, o);
      } else {
        p.value = '';
      }
      if (p.value == undefined || p.value === "") p.value = "&#160;";
      buf[buf.length] = this.cellTpl.apply(p);
    }

    return this.rowTpl.apply({
      tstyle: 'width:' + this.view.getTotalWidth() + ';',
      cells: buf.join('')
    });
  },

  refreshSummary : function() {
  	if(this.putSumInfo){
  		//alert(this.putSumInfo);
  		this.refreshSumValue(this.putSumInfo);
  		return;
  	}
    var g = this.grid, ds = g.store;
    var cs = this.view.getColumnData();
    var rs = ds.getRange();
    var data = this.calculate(rs, cs);
    var buf = this.renderSummary({data: data}, cs);

    if (!this.view.summaryWrap) {
      this.view.summaryWrap = Ext.DomHelper.insertAfter(this.view.scroller, {
        tag: 'div',
        cls: 'x-grid3-gridsummary-row-inner'
      }, true);
    } else {
      this.view.summary.remove();
    }
    this.view.summary = this.view.summaryWrap.insertHtml('afterbegin', buf, true);
  },

  toggleSummary : function(visible) { // true to display summary row
    var el = this.grid.getGridEl();
    if (el) {
      if (visible === undefined) {
        visible = el.hasClass('x-grid-hide-gridsummary');
      }
      el[visible ? 'removeClass' : 'addClass']('x-grid-hide-gridsummary');

      this.view.layout(); // readjust gridview height
    }
  },

  getSummaryNode : function() {
    return this.view.summary
  }
});

Ext.grid.GridSummary.Calculations = {

  'SUM' : function(v, record, field){
    return v + record.data[field]||0;
  },

	'sum' : function(v, record, field){

		return GO.util.numberFormat(GO.util.unlocalizeNumber(v) + (GO.util.unlocalizeNumber(record.data[field])||0));
	},

	'count' : function(v, record, field, data){
		return data[field+'count'] ? ++data[field+'count'] : (data[field+'count'] = 1);
	},

	'max' : function(v, record, field, data){
		var v = GO.util.unlocalizeNumber(record.data[field]);
		var max = data[field+'max'] === undefined ? (data[field+'max'] = v) : data[field+'max'];
		return GO.util.numberFormat(v > max ? (data[field+'max'] = v) : max);
	},

	'min' : function(v, record, field, data){
		var v = GO.util.unlocalizeNumber(record.data[field]);
		var min = data[field+'min'] === undefined ? (data[field+'min'] = v) : data[field+'min'];
		return GO.util.numberFormat(v < min ? (data[field+'min'] = v) : min);
	},

	'average' : function(v, record, field, data){
		var c = data[field+'count'] ? ++data[field+'count'] : (data[field+'count'] = 1);
		var t = (data[field+'total'] = ((data[field+'total']||0) + (GO.util.unlocalizeNumber(record.data[field])||0)));
		return GO.util.numberFormat(t === 0 ? 0 : t / c);
	}
}


Ext.grid.JsonSummary = Ext.extend(Ext.grid.GridSummary, {
	calculate : function(rs, cs){
		var json = this.grid.store.reader.jsonData;
		var data = {}, r, c, cfg = this.cm.config, cf;
		for(var j = 0, jlen = rs.length; j < jlen; j++){
			r = rs[j];
			for(var i = 0, len = cs.length; i < len; i++){
				c = cs[i];
				cf = cfg[i];
				if(json && json.summary){
					data[c.name]  = json.summary[c.name];
				}
			}
		}
		return data || Ext.grid.JsonSummary.superclass.calculate.call(this, rs, cs);
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

			p.value = (cf.summaryRenderer || c.renderer)(o.data[c.name], p, o);

			if(p.value == undefined || p.value === "") p.value = "-";
			buf[buf.length] = this.cellTpl.apply(p);
		}

		return this.rowTpl.apply({
			tstyle: 'width:'+this.view.getTotalWidth()+';',
			cells: buf.join('')
		});
	}
});

