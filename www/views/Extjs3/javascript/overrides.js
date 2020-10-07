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
	timeout: 180000
});

/**
 * Density Independend pixel calculation
 * 
 * This function returns precise numbers. When using with ext it's sometimes 
 * necessary to round them. For example in HBox and VBox layouts.
 * 
 * @type Number
 */
GO.util.density = 160; // set in Theme
function dp(size) {
	return ((size * GO.util.density) / 160);
};
/*
 *When upgrading extjs don't forget to check htmleditor overrides in E-mail composer
 */

(function(){
  
  //add module and package to components so they are aware of the module they belong to.
	//go.module and go.package are defined in default_scripts.inc.php 
  var origExtend = Ext.extend;

  Ext.extend = function() {
    var cls = origExtend.apply(this, arguments);
    //console.log(go.module);
    cls.prototype.module = go.module;
    cls.prototype.package = go.package;

    return cls;
  }
})();



(function() {

	var componentInitComponent = Ext.Component.prototype.initComponent;

	Ext.override(Ext.Component, {  
			

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
		 * @param {Object[]} added
		 * @param {Object[]} changed
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
				this.autoSize();
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
	
	//Added check for ENTER key. Becuase this code prevented form submission
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

		var keys, converted = {}, currentJSONlevel;

		for (var key in o) {

			keys = key.split('.');

			currentJSONlevel = converted;

			for (var i = 0; i < keys.length; i++) {
				if (i === (keys.length - 1)) {
					currentJSONlevel[keys[i]] = o[key];
				} else
				{
					currentJSONlevel[keys[i]] = currentJSONlevel[keys[i]] || {};
					currentJSONlevel = currentJSONlevel[keys[i]];
				}
			}

		}
				
		return converted;
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

	onResize: function(w, h){
		Ext.form.TriggerField.superclass.onResize.call(this, w, h);
		var tw = this.getTriggerWidth();
		if(Ext.isNumber(w)){
			this.el.setWidth(w - tw);
		}
		this.wrap.setWidth(w + tw);
	},
	
	 onRender : function(ct, position){
        this.doc = Ext.isIE ? Ext.getBody() : Ext.getDoc();
        Ext.form.TriggerField.superclass.onRender.call(this, ct, position);

        this.wrap = this.el.wrap({cls: 'x-form-field-wrap x-form-field-trigger-wrap'});
        this.trigger = this.wrap.createChild(this.triggerConfig ||
                {tag: "button", type: "button", tabindex: "-1", cls: "x-form-trigger " + this.triggerClass});
        this.initTrigger();
        if(!this.width){
            this.wrap.setWidth(this.el.getWidth()+this.getTriggerWidth());
        }
        this.resizeEl = this.positionEl = this.wrap;
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
						if(this.lastOptions)
							this.reload();
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

/*testing
Ext.TaskMgr.start({
	run: function(){
		document.title=GO.hasFocus ? 'Focus' : 'No focus';
	},
	interval: 1000
});*/

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
					if (!item.disabled && item.isVisible())
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
	width: dp(32),
});

Ext.override(Ext.layout.ToolbarLayout, {
	triggerWidth: dp(40)
});

/**
 * Fixed issue were some fonts came 1 pixel short and would wrap to the second line
 */
Ext.override(Ext.Element, {
	getTextWidth : function(text, min, max){
      return (Ext.util.TextMetrics.measure(this.dom, Ext.value(text, this.dom.innerHTML, true)).width+1).constrain(min || 0, max || 1000000);
   }
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
 * Fix for loosing pasted value in HTML editor

Ext.override(Ext.form.HtmlEditor, {
	getValue : function() {
		this.syncValue();
		return Ext.form.HtmlEditor.superclass.getValue.call(this);
	}
}); */



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
        return this.isStyle('box-sizing', 'border-box') || Ext.isBorderBox || Ext.isForcedBorderBox || noBoxAdjust[(this.dom.tagName || "").toLowerCase()];
   },
	print: function(config) {

		config = config || {};

		Ext.apply(this, config);
        
		var el = Ext.get(this.id).dom;
		// var c = document.getElementById('printcontainer');
		// var iFrame = document.getElementById('printframe');
        
		var strTemplate = '<HTML><HEAD>{0}<TITLE>{1}</TITLE></HEAD><BODY onload="{2}" style="background-color:white;"><div style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:99;"></div>{3}</BODY></HTML>';
		var strAttr = '';
		var strFormat;
		var strHTML;
        
		//Get rid of the old crap so we don't copy it
		//to our iframe
		// if (iFrame != null) c.removeChild(iFrame);
		// if (c != null) el.removeChild(c);
        
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

		var html = el.innerHTML;
		if(config.title)
			html = '<h1 style="margin-left:5px;font-size:16px;margin:10px 5px;">'+config.title+'</h1>'+html;
        
		//Build our HTML document for the iframe
		// strHTML = String.format(
		// 	strTemplate
		// 	, Ext.isEmpty(this.printCSS)? '#': this.printCSS
		// 	, this.printTitle
		// 	, Ext.isIE? 'document.execCommand(\'print\');': 'window.print();'
		// 	, html
		// 	);

      go.print(html);
		// var popup = window.open('about:blank');
		// if (!popup.opener) popup.opener = self
		// popup.document.write(strHTML);
		// popup.document.close();
		// popup.focus();
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
			document.location.href = BaseHref;
			return;
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
		var email = /^[_a-z0-9\-+\&\']+(\.[_a-z0-9\-+\&\']+)*@[a-z0-9\-]+(\.[a-z0-9\-]+)*(\.[a-z]{2,100})$/i;
        return email.test(v);
    },
    emailAddressText: Ext.form.VTypes.emailText,
    emailAddressMask: /[a-z0-9_\.\-\'@\+\&]/i
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
            
						//disable padding right in GO theme because it looks ugly
            // if (GO.settings.theme!='Group-Office' && colModel.config[i].align == 'right') {
            //     properties.istyle = 'padding-right: 16px;';
            // } else {
                delete properties.istyle;
            // }
            
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


/**
 * Override of Ext.removeNode function
 * 
 * Added "&& !Ext.isIE9" to this function because it is also not working on IE9
 * 
 * <p>Removes this element from the document, removes all DOM event listeners, and deletes the cache reference.
 * All DOM event listeners are removed from this element. If {@link Ext#enableNestedListenerRemoval} is
 * <code>true</code>, then DOM event listeners are also removed from all child nodes. The body node
 * will be ignored if passed in.</p>
 * @param {HTMLElement} node The node to remove
 * @method
 */
// Ext.removeNode = Ext.isIE && !Ext.isIE8 && !Ext.isIE9 && !Ext.isIE10 ? function() {
//
//	var d;
//	return function(n) {
//		if (n && n.tagName != 'BODY') {
//			(Ext.enableNestedListenerRemoval) ? Ext.EventManager.purgeElement(n, true) : Ext.EventManager.removeAll(n);
//			d = d || document.createElement('div');
//			d.appendChild(n);
//			d.innerHTML = '';
//			delete Ext.elCache[n.id];
//		}
//	};
//}() : function(n) {
//	if (n && n.parentNode && n.tagName != 'BODY') {
//		(Ext.enableNestedListenerRemoval) ? Ext.EventManager.purgeElement(n, true) : Ext.EventManager.removeAll(n);
//		n.parentNode.removeChild(n);
//		delete Ext.elCache[n.id];
//	}
//};


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
go.DrawIcon = function(values,cls) {
	if(values.iconCls.substring(0,3) === 'ic-') {
		return '<i style="'+values.iconStyle+'" class="icon">'+values.iconCls.substring(3).split('-').join('_')+'</i>';
	}
	return '<span style="'+values.iconStyle+'" class="x-menu-item-icon '+values.iconCls+'"></span>';
}

Ext.menu.Item.prototype.itemTpl = new Ext.XTemplate(
	'<a id="{id}" class="{cls} x-unselectable" hidefocus="true" unselectable="on" href="{href}"',
		 '<tpl if="hrefTarget">',
			  ' target="{hrefTarget}"',
		 '</tpl>',
	 '>',
		'{[go.DrawIcon(values, "x-menu-item-icon") ]}',
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

// Not needed and breaks rss feed reader
//Ext.override(Ext.data.Field, {
//	dateFormat: "c" //from server
//});

Ext.override(Ext.DatePicker, {
	origHandleMouseWheel : Ext.DatePicker.prototype.handleMouseWheel,
	handleMouseWheel: function(e) {
		e.stopEvent();
		if(!Ext.isMac) {
			this.origHandleMouseWheel(e);
		}
	}
});

Ext.override(Ext.Panel, {
	border: false,
	animCollapse: false,
	panelInitComponent : Ext.Panel.prototype.initComponent,
	
	initComponent : function() {
		
		if(GO.util.isMobileOrTablet()) {
			this.split = false;			
		}
		
		this.panelInitComponent.call(this);
	},
	
	stateEvents: ['collapse', 'expand'],
	getState: function () {
		return {
			collapsed: this.collapsed
		};
	}

});

Ext.override(Ext.form.Field, {
	fieldInitComponent : Ext.form.Field.prototype.initComponent,
	
	initComponent : function() {
		
		if(this.hint) {
			var fieldHelp = new Ext.ux.FieldHelp(this.hint);
			this.plugins = this.plugins || [];
			this.plugins.push(fieldHelp);
		}
		
		
		this.fieldInitComponent.call(this);
	},
	
	setFieldLabel: function(label){
		if(this.rendered){
			this.label.update(label+':');
		} else {
			this.fieldLabel = label;
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