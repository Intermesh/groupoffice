/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: overrides.js 22456 2018-03-06 15:42:05Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//Default timeout 3 minutes.
Ext.override(Ext.data.Connection, {
	timeout: 30000//180000
});

Ext.override(Ext.form.TimeField, {
	width: dp(160)
});

/**
 * Density Independend pixel calculation
 * 
 * This function returns precise numbers. When using with ext it's sometimes 
 * necessary to round them. For example in HBox and VBox layouts.
 * 
 * @type Number
 */
GO.util.density = GO.util.isMobileOrTablet() ? 160 : 140;
function dp(size) {
	size = ((size * GO.util.density) / 160);
	if(size > 20) {
		return Math.ceil(size);
	} else {
		return size;
	}
}
/*
 *When upgrading extjs don't forget to check htmleditor overrides in E-mail composer
 */

(function(){
  
  //add module and package to components so they are aware of the module they belong to.
	//go.module and go.package are defined in Extjs3.loadGoExt()
  var origExtend = Ext.extend;

  Ext.extend = function() {
    var cls = origExtend.apply(this, arguments);
    cls.prototype.module = go.module;
    cls.prototype.package = go.package;

    return cls;
  }
})();



(function() {

	var componentInitComponent = Ext.Component.prototype.initComponent;

	Ext.override(Ext.Component, {



		/**
		 * For GOUI
		 */
		fire: function() {
			Ext.Component.prototype.fireEvent.apply(this, arguments);
		},

		initComponent : function() {
			componentInitComponent.call(this);			

			if(this.entityStore) {
				this.initEntityStore();
			}
		},
		
		initEntityStore : function() {
			if(Ext.isString(this.entityStore)) {
				this.entityStore = go.Db.store(this.entityStore);
				if(!this.entityStore) {
					throw "Invalid 'entityStore' property given to component"; 
				}
			}
			
			this.on("afterrender", function() {
				this.entityStore.on('changes',this.onChanges, this);		
			}, this);

			this.on('beforedestroy', function() {
				this.entityStore.un('changes', this.onChanges, this);
			}, this);
		},

		/**
		 * Fires when items are added, changed or destroyed in the entitystore.
		 * 
		 * @param {go.data.EntityStore} entityStore
		 * @param {int[]} added
		 * @param {int[]} changed
		 * @param {int[]} destroyed
		 * @returns {void}
		 */
		onChanges : function(entityStore, added, changed, destroyed) {		

		},

		//Without this override findParentByType doesn't work if you don't Ext.reg() all your components
		 getXTypes : function(){
					var tc = this.constructor;
					if(!tc.xtypes){
							var c = [], sc = this;
							while(sc){
									c.unshift(sc.constructor.xtype);
									sc = sc.constructor.superclass;
							}
							tc.xtypeChain = c;
							tc.xtypes = c.join('/');
					}
					return tc.xtypes;
			}
	});
})();

Ext.override(Ext.form.TextArea,{
	insertAtCursor: function(v) {
		var document_id = this.getEl().id;
		var text_field = document.getElementById(document_id);
		var startPos = text_field.selectionStart;
		var endPos = text_field.selectionEnd;
		text_field.value = text_field.value.substring(0, startPos) + v + text_field.value.substring(endPos, text_field.value.length); 
		this.el.focus();
		text_field.setSelectionRange(endPos+v.length, endPos+v.length);
	},
	origAfterRender : Ext.form.TextArea.prototype.afterRender,
	afterRender : function() {
		this.origAfterRender();

		if (this.grow) {
			// debugger;
			setTimeout(() => {
				if(!this.isDestroyed) {
					this.autoSize();
				}
			})
		}
	},

	/**
	 * Automatically grows the field to accomodate the height of the text up to the maximum field height allowed.
	 * This only takes effect if grow = true, and fires the {@link #autosize} event if the height changes.
	 * COMMENTED OUT: This is causing the effect in ticket #202021014
	 */
	// autoSize: function(){
	// 	if(!this.grow || !this.textSizeEl){
	// 		return;
	// 	}
	// 	this.el.dom.style.overflowY = 'hidden';
	// 	var changed = false;
	// 	if (this.el.dom.offsetHeight > this.growMin) {
	// 		this.el.dom.style.height = this.growMin + "px";
	// 		changed = true;
	// 	}
	//
	// 	var height = Math.min(this.el.dom.scrollHeight, this.growMax);
	// 	if (height > this.growMin) {
	// 		height += dp(8);
	// 		this.el.setHeight(height);
	// 		changed = true;
	// 	}
	//
	// 	if (changed) {
	// 		//this.fireEvent('grow', this);
	// 		this.fireEvent("autosize", this, height);
	// 	}
	// },
	growMin : dp(48),
	height: dp(48)
});

Ext.override(Ext.form.TextField,{
	
	//Added check for ENTER key. because this code prevented form submission
	filterKeys : function(e){

		if(e.ctrlKey){
				return;
		}
		var k = e.getKey();

		if(k == e.ENTER) {
			return;
		}

		if(Ext.isGecko && (e.isNavKeyPress() || k == e.BACKSPACE || (k == e.DELETE && e.button == -1))){
				return;
		}
		var cc = String.fromCharCode(e.getCharCode());
		if(!Ext.isGecko && e.isSpecialKey() && !cc){
				return;
		}
		if(!this.maskRe.test(cc)){
				e.stopEvent();
		}
	}

});

Ext.override(Ext.form.FieldSet, {
	onRender : function(ct, position){
		if(!this.el){
			this.el = document.createElement('div');
			this.el.id = this.id;
			if (this.title || this.header || this.checkboxToggle) {
				this.el.appendChild(document.createElement('legend')).className = this.baseCls + '-header';
			}
		}

		Ext.form.FieldSet.superclass.onRender.call(this, ct, position);

		if(this.checkboxToggle){
			var o = typeof this.checkboxToggle == 'object' ?
				this.checkboxToggle :
				{tag: 'input', type: 'checkbox', name: this.checkboxName || this.id+'-checkbox'};
			this.checkbox = this.header.insertFirst(o);
			this.checkbox.dom.checked = !this.collapsed;
			this.mon(this.checkbox, 'click', this.onCheckClick, this);
		}
	}
});



Ext.override(Ext.form.BasicForm,{
	submit : function(options){
			options = options || {};
			if(this.standardSubmit){
					var v = options.clientValidation === false || this.isValid();
					if(v){
							var el = this.el.dom;
							if(this.url){
									el.action = this.url;
							}
							el.submit();
					}
					return v;
			}
			var submitAction = String.format('{0}submit', this.api ? 'direct' : '');
			this.doAction(submitAction, options);
			return this;
	},

	/**
	 * Sets all fields to "not" dirty.
	 */
	trackReset : function() {
		this.items.each(i => {
			i.originalValue = i.getValue();
			i.dirty = false; //MS: for form group and possibly other components
			if(i.setNotDirty) {
				i.setNotDirty(false);
			}
		});
	},
	
	setValuesOrig: Ext.form.BasicForm.prototype.setValues,
	/**
	 * Retrieves the fields in the form as a set of key/value pairs, using the {@link Ext.form.Field#getValue getValue()} method.
	 * If multiple fields exist with the same name they are returned as an array.
	 * 
	 * Use submit: false on fields you don't wish to include here.
	 * 
	 * @param {Boolean} dirtyOnly (optional) True to return only fields that are dirty.
	 * @return {Object} The values in the form
	 */
	getFieldValues: function (dirtyOnly) {
		var o = {},
						n,
						key,
						val,
						me = this;

		var fn = function (f) {
			if (dirtyOnly !== true || f.isDirty()) {
			
				if (f.getXType() == 'compositefield' || f.getXType() == 'checkboxgroup') {
					f.items.each(fn);
					return true;
				}

				if(f.submit === false || f.disabled === true) {
					return true;
				}

				n = f.getName();
				key = o[n];
				if (f.getXType() == 'numberfield') {
					var oldServerFormats = f.serverFormats;
					f.serverFormats = false; // this will post number as number
					val = f.getValue();
					f.serverFormats = oldServerFormats;
				} else {
					val = f.getValue();
				} 
				
				
				if(Ext.isDate(val)) {
					val = val.serialize();
				}				

				if (Ext.isDefined(key)) {
					if (Ext.isArray(key)) {
						o[n].push(val);
					} else {
						o[n] = [key, val];
					}
				} else {
					o[n] = val;
				}
			}
		};

		this.items.each(fn);

		return go.util.splitToJson(o);
	},
	
	setValues: function (values) {		
		values = this.joinValues(values);		
	  return this.setValuesOrig(values);		
	},
	
	joinValues : function(root, v, prefix) {
		if(!Ext.isDefined(v)) {
			v = root;
		}
		if(!Ext.isObject(v) || Ext.isDate(v[name])){
			return v;
		}

		if(!prefix) {
			prefix = "";
		}

		for(var name in v) {
			root[prefix + name] = this.joinValues(root, Ext.isDefined(v[name]) ? v[name] : null, prefix + name + ".");		
		}		
		return v;		
	}

});

Ext.override(Ext.grid.ColumnModel,{
	getOrgColumnHeader:function(b){
		return this.config[b].orgHeader||this.config[b].header;
	}	
});

Ext.override(Ext.grid.Column,{
	renderer:function(value, metaData, record, rowIndex, colIndex, store){
		//console.log(this);
		if(this.editor && !this.dontAddEditClass)
			metaData.css='go-editable-col';
		return value;
	}	
});

Ext.override(Ext.form.TriggerField,{
	
	origUpdateEditState : Ext.form.TriggerField.prototype.updateEditState,
	
	updateEditState: function(){
		
		this.origUpdateEditState();
		
		if(this.rendered){
			if(!this.readOnly && !this.editable) {
				this.el.addClass('x-triggerfield-selectonly');
			} else {
				this.el.removeClass('x-triggerfield-selectonly');
			}
		}
	},



	 getTriggerWidth: function(){
		 return 0;
	 }
});

Ext.override(Ext.form.TwinTriggerField, {
	initComponent : function(){
        Ext.form.TwinTriggerField.superclass.initComponent.call(this);

        this.triggerConfig = {
            tag:'span', cls:'x-form-twin-triggers', cn:[
            {tag: "button", type: "button",tabindex: "-1", cls: "x-form-trigger " + this.trigger1Class},
            {tag: "button", type: "button",tabindex: "-1", cls: "x-form-trigger " + this.trigger2Class}
        ]};
    },
	 getTriggerWidth: function(){
		 return 0;
	 }
});

Ext.override(Ext.grid.GroupingView, {
	/**
	 * Overridden because sometims we use Objects in the store (Relations) and groupMode = value doesn't work with that.
	 */
	groupMode: "display"
});

Ext.override(Ext.data.GroupingStore,{
	clearGrouping : function(){
        this.groupField = false;

        if(this.remoteGroup){
            if(this.baseParams){
                delete this.baseParams.groupBy;
                delete this.baseParams.groupDir;
            }
            var lo = this.lastOptions;
            if(lo && lo.params){
                delete lo.params.groupBy;
                delete lo.params.groupDir;
            }
						
						//added this to prevent store to request data when state is initalized in the construct
						if(this.lastOptions) {
							this.reload();
						}
        }else{
            this.sort();
            this.fireEvent('datachanged', this);
        }
    }
});


Ext.override(Ext.form.CompositeField, {
	origFocus : Ext.form.CompositeField.prototype.focus,
	focus : function() {
		var first = this.items.find(function(item) {
			return item.isFormField && !item.disabled && item.isVisible();
		});
		if(first) {
			first.focus();
		} else {
			this.origFocus();
		}
	}
});

/*
 * When idValuePair is true on a form field it will assume that the value is
 * <valueField>:<displayField>. It will transform the value for correct display.
 */
Ext.override(Ext.FormPanel,{
	initComponent : Ext.FormPanel.prototype.initComponent.createSequence(function(){

		this.on('actioncomplete', function(form, action){
			if(action.type=='load'){
				form.items.each(function(field){
					//check if this field is a tree select
					if(field.idValuePair){
						var v = field.getValue();
						if(!GO.util.empty(v)){
							v=v.split(':');
							if(v.length==2){
								field.setRawValue(v[1]);
							}
						}
					}
				});
			}
		});
	}),
	
	origFocus : Ext.FormPanel.prototype.focus,
	focus : function() {
		var focFn = function() {
			if(!GO.util.isMobileOrTablet()) {
				var firstField = this.getForm().items.find(function (item) {
					if (!item.disabled && item.isVisible() && go.util.empty(item.getValue()))
						return true;
				});


				//Don't focus on an invalid field or it will loose the invalide state on blur
				if(firstField && !firstField.activeError) {// && firstField.isValid && firstField.isValid()) {
					firstField.focus();
					return;
				} 
			}

			this.origFocus();
		};
		
		focFn.defer(200, this);
		
	},
	
	// prevents adding form fields that are part of custom form field components like the combobox of go.form.Chips for example.
	processAdd : function(c){
			if(this.isField(c)){
					this.form.add(c);

			}else if(c.findBy){
					this.applySettings(c);
					this.form.add.apply(this.form, this.findFieldsInComponent(c));
			}
	},
	
	findFieldsInComponent : function(comp) {
		var m = [];
		comp.cascade(function(c){
				if(this.isField(c)) {
						m.push(c);
						//don't cascade into form fields.
						return (c.getXType() == 'compositefield' || c.getXType() == 'checkboxgroup' || c.getXType() == "radiogroup" || c.getXType() == "formcoontainer" || c.getXType() == "formgroup"); //don't cascade into form fields
				}
		}, this);
		return m;
	}
});

Ext.override(Ext.slider.MultiSlider, {
	moveThumb: function(index, v, animate){
        var thumb = this.thumbs[index].el;

        if(!animate || this.animate === false){
            thumb.setLeft(v);
        }else{
            thumb.shift({left: v, stopFx: true, duration:.35});
        }
		  // This line was added to create a fill track
		  this.focusEl.setStyle({transform: 'scaleX('+(this.thumbs[index].value/100)+')'});
    }
});

Ext.override(Ext.tree.TreeNodeUI, {
	renderElements : function(n, a, targetNode, bulkRender){
        // add some indent caching, this helps performance when rendering a large tree
        this.indentMarkup = n.parentNode ? n.parentNode.ui.getChildIndent() : '';

        var cb = Ext.isBoolean(a.checked),
            nel,
            href = this.getHref(a.href),
            buf = ['<li class="x-tree-node"><div ext:tree-node-id="',n.id,'" class="x-tree-node-el x-tree-node-leaf x-unselectable ', a.cls,'" unselectable="on">',
            '<span class="x-tree-node-indent">',this.indentMarkup,"</span>",
            '<span class="x-tree-ec-icon x-tree-elbow"></span>',
            '<span style="background-image:url(', a.icon || this.emptyIcon, ');" class="x-tree-node-icon',(a.icon ? " x-tree-node-inline-icon" : ""),(a.iconCls ? " "+a.iconCls : ""),'" unselectable="on"></span>',
            cb ? ('<span class="x-tree-node-cb"><input type="checkbox" ' + (a.checked ? 'checked="checked" /></span>' : '/></span>')) : '',
            '<a hidefocus="on" class="x-tree-node-anchor" href="',href,'" tabIndex="1" ',
             a.hrefTarget ? ' target="'+a.hrefTarget+'"' : "", '><span unselectable="on">',n.text,"</span></a></div>",
            '<ul class="x-tree-node-ct" style="display:none;"></ul>',
            "</li>"].join('');

        if(bulkRender !== true && n.nextSibling && (nel = n.nextSibling.ui.getEl())){
            this.wrap = Ext.DomHelper.insertHtml("beforeBegin", nel, buf);
        }else{
            this.wrap = Ext.DomHelper.insertHtml("beforeEnd", targetNode, buf);
        }

        this.elNode = this.wrap.childNodes[0];
        this.ctNode = this.wrap.childNodes[1];
        var cs = this.elNode.childNodes;
        this.indentNode = cs[0];
        this.ecNode = cs[1];
        this.iconNode = cs[2];
        var index = 3;
        if(cb){
            this.checkbox = cs[3].childNodes[0];
            // fix for IE6
            this.checkbox.defaultChecked = this.checkbox.checked;
            index++;
        }
        this.anchor = cs[index];
        this.textNode = cs[index].firstChild;
    },
	 getChildIndent : function(){
        if(!this.childIndent){
            var buf = [],
                p = this.node;
            while(p){
                if(!p.isRoot || (p.isRoot && p.ownerTree.rootVisible)){
                    if(!p.isLast()) {
                        buf.unshift('<span class="x-tree-elbow-line"></span>');
                    } else {
                        buf.unshift('<span class="x-tree-icon"></span>');
                    }
                }
                p = p.parentNode;
            }
            this.childIndent = buf.join("");
        }
        return this.childIndent;
    }
});

Ext.override(Ext.grid.GridView, {
	doRender : function(columns, records, store, startRow, colCount, stripe) {
		let templates = this.templates,
			cellTemplate = templates.cell,
			rowTemplate = templates.row,
			last = colCount - 1,
			// buffers
			rowBuffer = [],
			colBuffer = [],
			rowParams,
			meta = {},
			len = records.length,
			alt,
			column,
			record, rowIndex;
		//build up each row's HTML
		for (let j = 0; j < len; j++) {
			let tstyle = 'width:' + this.getTotalWidth() + ';', rowCFStyle = false;

			record    = records[j];
			colBuffer = [];

			rowIndex = j + startRow;

			if(!rowCFStyle) {
				rowCFStyle = this.getRowCFStyle(record, columns);
				if(rowCFStyle) {
					tstyle += rowCFStyle;
				}
			}

			//build up each column's HTML
			for (let i = 0; i < colCount; i++) {
				column = columns[i];

				meta.id    = column.id;
				meta.css   = i === 0 ? 'x-grid3-cell-first ' : (i == last ? 'x-grid3-cell-last ' : '');
				meta.attr  = meta.cellAttr = '';
				meta.style = column.style;

				let v = this.encodeGridValue(store, column, record);
				try {
					meta.value = column.renderer.call(column.scope, v, meta, record, rowIndex, i, store);
				} catch(e) {
					console.error(e);
				}

				if (Ext.isEmpty(meta.value)) {
					meta.value = '&#160;';
				}

				if (this.markDirty && record.dirty && typeof record.modified[column.name] != 'undefined') {
					meta.css += ' x-grid3-dirty-cell';
				}

				colBuffer[colBuffer.length] = cellTemplate.apply(meta);
			}

			alt = [];
			//set up row striping and row dirtiness CSS classes
			if (stripe && ((rowIndex + 1) % 2 === 0)) {
				alt[0] = 'x-grid3-row-alt';
			}

			if (record.dirty) {
				alt[1] = ' x-grid3-dirty-row';
			}
			rowParams = {tstyle: tstyle};
			rowParams.cols = colCount;

			if (this.getRowClass) {
				alt[2] = this.getRowClass(record, rowIndex, rowParams, store);
			}

			rowParams.alt   = alt.join(' ');
			rowParams.cells = colBuffer.join('');

			rowBuffer[rowBuffer.length] = rowTemplate.apply(rowParams);
		}

		return rowBuffer.join('');
	},

	getRowCFStyle: function(record, columns) {
		for (let i = 0, l = columns.length; i < l; i++) {
			const column = columns[i];
			const val = record.data[column.name]

			if(!go.util.empty(val) && typeof column.scope.rowRenderer === "function") {
				return column.scope.rowRenderer(val);
			}
		}
		return false;
	},

	origOnLoad: Ext.grid.GridView.prototype.onLoad,
	
	//emptyText: 	'<i>add_circle_outline</i><p>' +t("No items to display") + '</p>',


	onLoad: function (store, records, options) {
		
		if(options.add) {
			return;
		}
		this.origOnLoad.call(this, store, records, options);
	}
});

Ext.override(Ext.grid.CheckboxSelectionModel, {
	width: dp(40),
});

Ext.override(Ext.layout.ToolbarLayout, {
	triggerWidth: dp(50)
});

/**
 * Fixed issue were some fonts came 1 pixel short and would wrap to the second line
 */
Ext.override(Ext.Element, {
	getTextWidth : function(text, min, max){
      return (Ext.util.TextMetrics.measure(this.dom, Ext.value(text, this.dom.innerHTML, true)).width+1).constrain(min || 0, max || 1000000);
   },

	_bgColor: undefined,

	getBackgroundColor() {
		if(!this._bgColor) {
			this._bgColor = this._getInheritedBackgroundColor(this.dom);
		}
		return this._bgColor;
	},

	_getInheritedBackgroundColor: function(el) {
		// get default style for current browser
		var defaultStyle = this._getDefaultBackground() // typically "rgba(0, 0, 0, 0)"

		// get computed color for el
		var backgroundColor = window.getComputedStyle(el).backgroundColor

		// if we got a real value, return it
		if (backgroundColor != defaultStyle) return backgroundColor

		// if we've reached the top parent el without getting an explicit color, return default
		if (!el.parentElement) return defaultStyle

		// otherwise, recurse and try again on parent element
		return this._getInheritedBackgroundColor(el.parentElement)
	},

	_getDefaultBackground: function() {
		// have to add to the document in order to use getComputedStyle
		var div = document.createElement("div")
		document.head.appendChild(div)
		var bg = window.getComputedStyle(div).backgroundColor
		document.head.removeChild(div)
		return bg
	},
});

/* password vtype */ 
Ext.apply(Ext.form.VTypes, {    
	password : function(val, field) {
		if (field.initialPassField) {
			var pwd = Ext.getCmp(field.initialPassField);
			return (val == pwd.getValue());
		}
		return true;
	},
	passwordText : t("The passwords didn't match")
});

 

/*
 * Localization
 */
Ext.MessageBox.buttonText.yes = t("Yes");
Ext.MessageBox.buttonText.no = t("No");
Ext.MessageBox.buttonText.ok = t("Ok");
Ext.MessageBox.buttonText.cancel = t("Cancel");


/*
* Print elements
*/
var noBoxAdjust = Ext.isStrict ? {
    select:1
} : {
    input:1, select:1, textarea:1
};
Ext.isBorderBox = true;
Ext.override(Ext.Element, {
	/**
     * @cfg {string} printCSS The file path of a CSS file for printout.
     */
	printCSS: ''
	/**
     * @cfg {Boolean} printStyle Copy the style attribute of this element to the print iframe.
     */
	,
	printStyle: false
	/**
     * @property {string} printTitle Page Title for printout. 
     */
	,
	printTitle: document.title

	/**
     * Prints this element.
     * 
     * @param config {object} (optional)
     */
	,
	isBorderBox : function(){
        return true;//this.isStyle('box-sizing', 'border-box') || Ext.isBorderBox || Ext.isForcedBorderBox || noBoxAdjust[(this.dom.tagName || "").toLowerCase()];
   },
	print: function(config) {

		config = config || {};

		Ext.apply(this, config);
        
		var el = Ext.get(this.id).dom;
		var strAttr = '';
		var strFormat;

		//Copy attributes from this element.
		for (var i = 0; i < el.attributes.length; i++) {
			if (Ext.isEmpty(el.attributes[i].value) || el.attributes[i].value.toLowerCase() != 'null') {
				strFormat = Ext.isEmpty(el.attributes[i].value)? '{0}="true" ': '{0}="{1}" ';
				if (this.printStyle? this.printStyle: el.attributes[i].name.toLowerCase() != 'style')
					strAttr += String.format(strFormat, el.attributes[i].name, el.attributes[i].value);
			}
		}
        
		for(var i=0;i<document.styleSheets.length;i++)
		{
			this.printCSS+='<link rel="stylesheet" type="text/css" href="'+document.styleSheets[i].href+'"/>';
		}

		this.printCSS+='<style>body{overflow:visible !important;}</style>';
		var html = "<div " + strAttr+">" + el.innerHTML + "</div>";
		if(config.title) {
			// set document title for saving to PDF
			const oldTitle = document.title;

			// Replace characters not valid for file names
			document.title = config.title.replace(':', '.').replace(/[/\\?%*|"<>]+/g, '-');

			window.addEventListener("afterprint" , function(){
				document.title = oldTitle;
			}, {once: true});
		}

		go.print(html);
	}
});

Ext.override(Ext.Component, {
	printEl: function(config) {
		this.el.print(Ext.isEmpty(config)? this.initialConfig: config);
	}
	,
	printBody: function(config) {
		this.body.print(Ext.isEmpty(config)? this.initialConfig: config);
	}
});

Ext.override(Ext.Container, {
	bufferResize: 500
});


Ext.encode = Ext.util.JSON.encode = function(json){
  return JSON.stringify(json);
}

/*
 * Catch JSON parsing errors and show error dialog
 * @type 
 */
Ext.decode = Ext.util.JSON.decode = function(jsonStr){
	try{
		var json = eval("(" + jsonStr + ')');
		if(json && json.redirectToLogin) {
			console.warn("Redirecting to login because access is denied");  
			go.Router.login();
		}
		
		return json;
	}
	catch (e)
	{

		switch(jsonStr.trim())
		{
			case 'NOTLOGGEDIN':
				//document.location=BaseHref;
			break;

			case 'UNAUTHORIZED':
				Ext.Msg.alert(t("Unauthorized"), t("You don't have permission to perform this action"));
			break;

			default:	
				console.error(jsonStr);
				jsonStr += '<br /><br />Ext.decode exception occurred';
				GO.errorDialog.show(t("An error occurred on the webserver. Contact your system administrator and supply the detailed error from the server log."));
				break;
		}
	}
};

Ext.apply(Ext.form.VTypes, {
    emailAddress:  function(v) {
		// Validate international email addresses as exhaustively as possible
	    // All props for the RegExp below go to Andreas Wik: https://awik.io/international-email-address-validation-javascript/
		const email = /^(?!\.)((?!.*\.{2})[a-zA-Z0-9\u0080-\u00FF\u0100-\u017F\u0180-\u024F\u0250-\u02AF\u0300-\u036F\u0370-\u03FF\u0400-\u04FF\u0500-\u052F\u0530-\u058F\u0590-\u05FF\u0600-\u06FF\u0700-\u074F\u0750-\u077F\u0780-\u07BF\u07C0-\u07FF\u0900-\u097F\u0980-\u09FF\u0A00-\u0A7F\u0A80-\u0AFF\u0B00-\u0B7F\u0B80-\u0BFF\u0C00-\u0C7F\u0C80-\u0CFF\u0D00-\u0D7F\u0D80-\u0DFF\u0E00-\u0E7F\u0E80-\u0EFF\u0F00-\u0FFF\u1000-\u109F\u10A0-\u10FF\u1100-\u11FF\u1200-\u137F\u1380-\u139F\u13A0-\u13FF\u1400-\u167F\u1680-\u169F\u16A0-\u16FF\u1700-\u171F\u1720-\u173F\u1740-\u175F\u1760-\u177F\u1780-\u17FF\u1800-\u18AF\u1900-\u194F\u1950-\u197F\u1980-\u19DF\u19E0-\u19FF\u1A00-\u1A1F\u1B00-\u1B7F\u1D00-\u1D7F\u1D80-\u1DBF\u1DC0-\u1DFF\u1E00-\u1EFF\u1F00-\u1FFFu20D0-\u20FF\u2100-\u214F\u2C00-\u2C5F\u2C60-\u2C7F\u2C80-\u2CFF\u2D00-\u2D2F\u2D30-\u2D7F\u2D80-\u2DDF\u2F00-\u2FDF\u2FF0-\u2FFF\u3040-\u309F\u30A0-\u30FF\u3100-\u312F\u3130-\u318F\u3190-\u319F\u31C0-\u31EF\u31F0-\u31FF\u3200-\u32FF\u3300-\u33FF\u3400-\u4DBF\u4DC0-\u4DFF\u4E00-\u9FFF\uA000-\uA48F\uA490-\uA4CF\uA700-\uA71F\uA800-\uA82F\uA840-\uA87F\uAC00-\uD7AF\uF900-\uFAFF\.!#$%&'*+-/=?^_`{|}~\-\d]+)@(?!\.)([a-zA-Z0-9\u0080-\u00FF\u0100-\u017F\u0180-\u024F\u0250-\u02AF\u0300-\u036F\u0370-\u03FF\u0400-\u04FF\u0500-\u052F\u0530-\u058F\u0590-\u05FF\u0600-\u06FF\u0700-\u074F\u0750-\u077F\u0780-\u07BF\u07C0-\u07FF\u0900-\u097F\u0980-\u09FF\u0A00-\u0A7F\u0A80-\u0AFF\u0B00-\u0B7F\u0B80-\u0BFF\u0C00-\u0C7F\u0C80-\u0CFF\u0D00-\u0D7F\u0D80-\u0DFF\u0E00-\u0E7F\u0E80-\u0EFF\u0F00-\u0FFF\u1000-\u109F\u10A0-\u10FF\u1100-\u11FF\u1200-\u137F\u1380-\u139F\u13A0-\u13FF\u1400-\u167F\u1680-\u169F\u16A0-\u16FF\u1700-\u171F\u1720-\u173F\u1740-\u175F\u1760-\u177F\u1780-\u17FF\u1800-\u18AF\u1900-\u194F\u1950-\u197F\u1980-\u19DF\u19E0-\u19FF\u1A00-\u1A1F\u1B00-\u1B7F\u1D00-\u1D7F\u1D80-\u1DBF\u1DC0-\u1DFF\u1E00-\u1EFF\u1F00-\u1FFF\u20D0-\u20FF\u2100-\u214F\u2C00-\u2C5F\u2C60-\u2C7F\u2C80-\u2CFF\u2D00-\u2D2F\u2D30-\u2D7F\u2D80-\u2DDF\u2F00-\u2FDF\u2FF0-\u2FFF\u3040-\u309F\u30A0-\u30FF\u3100-\u312F\u3130-\u318F\u3190-\u319F\u31C0-\u31EF\u31F0-\u31FF\u3200-\u32FF\u3300-\u33FF\u3400-\u4DBF\u4DC0-\u4DFF\u4E00-\u9FFF\uA000-\uA48F\uA490-\uA4CF\uA700-\uA71F\uA800-\uA82F\uA840-\uA87F\uAC00-\uD7AF\uF900-\uFAFF\-\.\d]+)((\.([a-zA-Z\u0080-\u00FF\u0100-\u017F\u0180-\u024F\u0250-\u02AF\u0300-\u036F\u0370-\u03FF\u0400-\u04FF\u0500-\u052F\u0530-\u058F\u0590-\u05FF\u0600-\u06FF\u0700-\u074F\u0750-\u077F\u0780-\u07BF\u07C0-\u07FF\u0900-\u097F\u0980-\u09FF\u0A00-\u0A7F\u0A80-\u0AFF\u0B00-\u0B7F\u0B80-\u0BFF\u0C00-\u0C7F\u0C80-\u0CFF\u0D00-\u0D7F\u0D80-\u0DFF\u0E00-\u0E7F\u0E80-\u0EFF\u0F00-\u0FFF\u1000-\u109F\u10A0-\u10FF\u1100-\u11FF\u1200-\u137F\u1380-\u139F\u13A0-\u13FF\u1400-\u167F\u1680-\u169F\u16A0-\u16FF\u1700-\u171F\u1720-\u173F\u1740-\u175F\u1760-\u177F\u1780-\u17FF\u1800-\u18AF\u1900-\u194F\u1950-\u197F\u1980-\u19DF\u19E0-\u19FF\u1A00-\u1A1F\u1B00-\u1B7F\u1D00-\u1D7F\u1D80-\u1DBF\u1DC0-\u1DFF\u1E00-\u1EFF\u1F00-\u1FFF\u20D0-\u20FF\u2100-\u214F\u2C00-\u2C5F\u2C60-\u2C7F\u2C80-\u2CFF\u2D00-\u2D2F\u2D30-\u2D7F\u2D80-\u2DDF\u2F00-\u2FDF\u2FF0-\u2FFF\u3040-\u309F\u30A0-\u30FF\u3100-\u312F\u3130-\u318F\u3190-\u319F\u31C0-\u31EF\u31F0-\u31FF\u3200-\u32FF\u3300-\u33FF\u3400-\u4DBF\u4DC0-\u4DFF\u4E00-\u9FFF\uA000-\uA48F\uA490-\uA4CF\uA700-\uA71F\uA800-\uA82F\uA840-\uA87F\uAC00-\uD7AF\uF900-\uFAFF]){2,63})+)$/i;
		return email.test(v);
    },
    emailAddressText: Ext.form.VTypes.emailText,
    emailAddressMask: /[a-z0-9_\.\-\'@\+\&\/\\]/i
});

Ext.override(Ext.grid.GridView,{
		/**
     * @private
     * Renders the header row using the 'header' template. Does not inject the HTML into the DOM, just
     * returns a string.
     * @return {String} Rendered header row
     */
    renderHeaders : function() {
        var colModel   = this.cm,
            templates  = this.templates,
            headerTpl  = templates.hcell,
            properties = {},
            colCount   = colModel.getColumnCount(),
            last       = colCount - 1,
            cells      = [],
            i, cssCls;
        
        for (i = 0; i < colCount; i++) {
            if (i == 0) {
                cssCls = 'x-grid3-cell-first ';
            } else {
                cssCls = i == last ? 'x-grid3-cell-last ' : '';
            }
            
            properties = {
                id     : colModel.getColumnId(i),
                value  : colModel.getColumnHeader(i) || '',
                style  : this.getColumnStyle(i, true),
                css    : cssCls,
                tooltip: this.getColumnTooltip(i)
            };
            
			delete properties.istyle;

            cells[i] = headerTpl.apply(properties);
        }
        
        return templates.header.apply({
            cells : cells.join(""),
            tstyle: String.format("width: {0};", this.getTotalWidth())
        });
    }
	}
);
	
Ext.override(Ext.form.Checkbox, {
	setBoxLabel: function(boxLabel){
		this.boxLabel = boxLabel;
		if(this.rendered){
			this.wrap.child('.x-form-cb-label').update(boxLabel);
		}
	}
});


Ext.override(Ext.layout.ToolbarLayout ,{
		fitToSize : function(target) {
        if (this.container.enableOverflow === false) {
            return;
        }

        var width       = target.dom.clientWidth,
            tableWidth  = target.dom.firstChild.offsetWidth,
            clipWidth   = width - this.triggerWidth,
            lastWidth   = this.lastWidth || 0,

            hiddenItems = this.hiddenItems,
            hasHiddens  = hiddenItems.length != 0,
            isLarger    = width >= lastWidth;

        this.lastWidth  = width;

        if (tableWidth > width || (hasHiddens && isLarger)) {
            var items     = this.container.items.items,
                len       = items.length,
                loopWidth = 0,
                item;

            for (var i = 0; i < len; i++) {
                item = items[i];

                if (!item.isFill) {
                    loopWidth += this.getItemWidth(item);
                    if (loopWidth > clipWidth) {
                        if ((!item.hidden || !item.xtbHidden)) {
                            this.hideItem(item);
                        }
                    } else if (item.xtbHidden) {
                        this.unhideItem(item);
                    }
                }
            }
        }

        
        hasHiddens = hiddenItems.length != 0;

        if (hasHiddens) {
            this.initMore();

            if (!this.lastOverflow) {
                this.container.fireEvent('overflowchange', this.container, true);
                this.lastOverflow = true;
            }
        } else if (this.more) {
            this.clearMenu();
            this.more.destroy();
            delete this.more;

            if (this.lastOverflow) {
                this.container.fireEvent('overflowchange', this.container, false);
                this.lastOverflow = false;
            }
        }
    }
	}
);

Ext.override(Ext.menu.Item ,{
	getTemplateArgs: function() {
		return {
			id: this.id,
			cls: this.itemCls + (this.menu ?  ' x-menu-item-arrow' : '') + (this.cls ?  ' ' + this.cls : ''),
			href: this.href || '#',
			hrefTarget: this.hrefTarget,
			icon: this.icon || Ext.BLANK_IMAGE_URL,
			iconCls: this.iconCls || '',
			text: this.itemText||this.text||'&#160;',
			altText: this.altText || '',
			iconStyle: this.iconStyle || ''
		};
	},
	origOnRender: Ext.menu.Item.prototype.onRender,
	onRender : function(container, position){
		this.origOnRender.call(this, container, position);

		//tpl has been overridden and there's no img tag anymore. Without this setIconCls doesn't work.
		this.iconEl = this.iconEl = this.el.child('span.x-menu-item-icon');
	}

});

Ext.menu.Item.prototype.itemTpl = new Ext.XTemplate(
	'<a id="{id}" class="{cls} x-unselectable" hidefocus="true" unselectable="on" href="{href}"',
	'<tpl if="hrefTarget">',
	' target="{hrefTarget}"',
	'</tpl>',
	'>',
	'<span style="{iconStyle}" class="x-menu-item-icon {iconCls}"></span>',
	'<span class="x-menu-item-text">{text:raw}</span>',
	'</a>'
);
Ext.layout.MenuLayout.prototype.itemTpl = new Ext.XTemplate(
	'<li id="{itemId}" class="{itemCls}">',
	'<tpl if="needsIcon">',
	'<span style="{iconStyle}" class="{iconCls}"><tpl if="icon"><img alt="{altText}" src="{icon}" /></tpl></span>',
	'</tpl>',
	'</li>'
);


Ext.override(Ext.Panel, {
	border: false,
	animCollapse: false,
	panelInitComponent : Ext.Panel.prototype.initComponent,
	
	initComponent : function() {
		
		if(GO.util.isMobileOrTablet()) {
			if(this.split) {
				this.split = false;
				this.cls = this.cls || "";
				this.cls += ' go-mobile-split';
			}
		}
		
		this.panelInitComponent.call(this);

		this.toolsMenu();
	},



	toolsMenu : function() {

		if(!this.tools) {
			return;
		}

		this.toolsMenus = {};

		this.tools.forEach((tool) => {

			if (tool.menu) {

				tool.handler = function(event, toolEl, panel) {
					if (!panel.toolsMenus[tool.id]) {
						panel.toolsMenus[tool.id] = new Ext.menu.Menu({
							items: tool.menu
						})
					}
					panel.toolsMenus[tool.id].ownerCt = panel;
					panel.toolsMenus[tool.id].show(toolEl, 'tl-bl?');
				}
			}
		});


		this.on('destroy', () => {
			for(let id in this.toolsMenus) {
				this.toolsMenus[id].destroy();
			}
		});
	},
	
	stateEvents: ['collapse', 'expand'],
	getState: function () {
		return {
			collapsed: this.collapsed
		};
	}

});

Ext.override(Ext.form.Field, {
	validateOnBlur: false,
	fieldInitComponent : Ext.form.Field.prototype.initComponent,
	
	initComponent : function() {
		
		if(this.hint) {
			var fieldHelp = new Ext.ux.FieldHelp(this.hint);
			this.plugins = this.plugins || [];
			this.plugins.push(fieldHelp);
			delete this.hint;
		}
		
		
		this.fieldInitComponent.call(this);
	},
	
	setFieldLabel: function(label){
		if(this.rendered){
			this.label.update(label);
		} else {
			this.fieldLabel = label;
		}
	},

	//For GOUI compat
	isModified: function() {
		return this.isDirty();
	},
//For GOUI compat
	trackReset: function() {
		this.dirty = false;
		this.originalValue = this.getValue();
		if(this.setNotDirty) {
			this.setNotDirty();
		}
	}
});

Ext.override(Ext.form.Hidden, {
	getValue: function() {
		return this.value;
	}
});

Ext.util.Format.dateRenderer = function(format) {
		return function(v) {
				return GO.util.dateFormat(v);
		};
};
				
				
Ext.override(Ext.form.CompositeField, {
	submit: false //don't submit with form.getFieldValue()
});

Ext.override(Ext.form.DisplayField, {
	submit: false //don't submit with form.getFieldValue()
});

Ext.override(Ext.form.DateField, {
	getValue : function(){
        return this.parseDate(Ext.form.DateField.superclass.getValue.call(this)) || null;
    }
});

Ext.override(Ext.TabPanel, {
	
	origHideTabStripItem: Ext.TabPanel.prototype.hideTabStripItem,
	origUnhideTabStripItem: Ext.TabPanel.prototype.unhideTabStripItem,
	
	hideTabStripItem : function(item){
		if(!this.rendered) {
			this.on('afterrender', function() {
				this.origHideTabStripItem(item);
			}, this, {single: true});
		} else
		{
			this.origHideTabStripItem(item);
		}
	},


	unhideTabStripItem : function(item){
		if(!this.rendered) {
			this.on('afterrender', function() {
				this.origUnhideTabStripItem(item);
			}, this, {single: true});
		} else
		{
			this.origUnhideTabStripItem(item);
		}
	},
});

Ext.override(Ext.KeyNav, {
	forceKeyDown: true // Required for Firefox 67	
});


//USed by old gridpanel deleteselected to keep scroll position
// Also used by scrollloader in new framework
Ext.override(Ext.grid.GridView, {
	scrollToTopOnLoad: true,
	onLoad : function(store, records, o){
			if (this.scrollToTopOnLoad && !o.keepScrollPosition){
				if (Ext.isGecko) {
						if (!this.scrollToTopTask) {
								this.scrollToTopTask = new Ext.util.DelayedTask(this.scrollToTop, this);
						}
						this.scrollToTopTask.delay(1);
				} else {				
						this.scrollToTop();
				}
			}
			this.scrollToTopOnLoad=true;			
	}
});



if(GO.util.isMobileOrTablet()) {
	Ext.override(Ext.Container, {
		labelAlign: "top"
	});
}