/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: overrides.js 21602 2017-10-31 13:05:55Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/*
 *When upgrading extjs don't forget to check htmleditor overrides in E-mail composer
 */

Ext.override(Ext.Panel,{
	initComponent : Ext.Panel.prototype.initComponent.createSequence(function(){
		if(this.region){
			if(this.cls)
				this.cls += ' ';
			else
				this.cls = '';
			
			this.cls += 'go-border-'+this.region;
		}
	})
});

Ext.override(Ext.form.TextArea,{
	insertAtCursor: function(v) {
		var document_id = this.getEl().id;
		var text_field = document.getElementById(document_id);
		var startPos = text_field.selectionStart;
		var endPos = text_field.selectionEnd;
		text_field.value = text_field.value.substring(0, startPos) + v + text_field.value.substring(endPos, text_field.value.length); 
		this.el.focus();
		text_field.setSelectionRange(endPos+v.length, endPos+v.length);
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
	 onRender : function(ct, position){
        this.doc = Ext.isIE ? Ext.getBody() : Ext.getDoc();
        Ext.form.TriggerField.superclass.onRender.call(this, ct, position);

        this.wrap = this.el.wrap({cls: 'x-form-field-wrap x-form-field-trigger-wrap'});
        this.trigger = this.wrap.createChild(this.triggerConfig ||
                {tag: "button", type: "button", cls: "x-form-trigger " + this.triggerClass});
        this.initTrigger();
        if(!this.width){
            this.wrap.setWidth(this.el.getWidth()+this.trigger.getWidth());
        }
        this.resizeEl = this.positionEl = this.wrap;
    }
});

Ext.override(Ext.form.TwinTriggerField, {
	initComponent : function(){
        Ext.form.TwinTriggerField.superclass.initComponent.call(this);

        this.triggerConfig = {
            tag:'span', cls:'x-form-twin-triggers', cn:[
            {tag: "button", type: "button", cls: "x-form-trigger " + this.trigger1Class},
            {tag: "button", type: "button", cls: "x-form-trigger " + this.trigger2Class}
        ]};
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
	})
});

Ext.override(Ext.grid.GridView, {
    scrollToTopOnLoad: true,
    onLoad : function(){
        if (this.scrollToTopOnLoad){
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


/*
 * Scroll menu when higher then the screen is
 *
 */

//Ext.override(Ext.menu.Menu, {
//	showAt : function(xy, parentMenu, /* private: */_e){
//		this.parentMenu = parentMenu;
//		if(!this.el){
//			this.render();
//		}
//		if(_e !== false){
//			this.fireEvent("beforeshow", this);
//			xy = this.el.adjustForConstraints(xy);
//		}
//		this.el.setXY(xy);
//
//		//this.el.applyStyles('height: auto;');
//
//		// get max height from body height minus y cordinate from this.el
//		var maxHeight = Ext.getBody().getHeight() - xy[1];
//		// store orig element height
//		if (!this.el.origHeight) {
//			this.el.origHeight = this.el.getHeight();
//		}
//		// if orig height bigger than max height
//		if (this.el.origHeight > maxHeight) {
//			// set element with max height and apply scrollbar
//			this.el.setHeight(maxHeight);
//			this.el.applyStyles('overflow-y: auto;');
//		} else {
//		// set the orig height
//		//this.el.setHeight(this.el.origHeight);
//		}
//
//		this.el.show();
//		this.hidden = false;
//		this.focus();
//		this.fireEvent("show", this);
//	}
//});


/*
* for Ubuntu new wave theme
*/

Ext.override(Ext.grid.GridView, {
	scrollOffset:20
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
	passwordText : GO.lang.passwordMatchError
});

 
/**
 * Keep window in viewport and no shadows by default for IE performance
 */

Ext.Window.override({
	//shadow : false,
	constrainHeader : true,
	animCollapse : false
});

/*
 * Localization
 */
Ext.MessageBox.buttonText.yes = GO.lang['cmdYes'];
Ext.MessageBox.buttonText.no = GO.lang['cmdNo'];
Ext.MessageBox.buttonText.ok = GO.lang['cmdOk'];
Ext.MessageBox.buttonText.cancel = GO.lang['cmdCancel'];


/*
 * Fix for loosing pasted value in HTML editor

Ext.override(Ext.form.HtmlEditor, {
	getValue : function() {
		this.syncValue();
		return Ext.form.HtmlEditor.superclass.getValue.call(this);
	}
}); */

//only present when logged in.
if(GO.settings.date_format){
	Ext.override(Ext.DatePicker, {
		startDay: parseInt(GO.settings.first_weekday)
	});
	
	Ext.override(Ext.form.DateField, {
		format: GO.settings.date_format,
		startDay: parseInt(GO.settings.first_weekday)
	});

	Ext.override(Ext.form.DateField, {
		format: GO.settings.date_format,
		altFormats:GO.settings.date_format+"|"+GO.settings.date_format.replace("Y","y")
	});
}

/*
* Print elements
*/
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
	print: function(config) {

		config = config || {};

		Ext.apply(this, config);
        
		var el = Ext.get(this.id).dom;
		var c = document.getElementById('printcontainer');
		var iFrame = document.getElementById('printframe');
        
		var strTemplate = '<HTML><HEAD>{0}<TITLE>{1}</TITLE></HEAD><BODY onload="{2}" style="background-color:white;"><div style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:99;"></div>{3}</BODY></HTML>';
		var strAttr = '';
		var strFormat;
		var strHTML;
        
		//Get rid of the old crap so we don't copy it
		//to our iframe
		if (iFrame != null) c.removeChild(iFrame);
		if (c != null) el.removeChild(c);
        
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
		strHTML = String.format(
			strTemplate
			, Ext.isEmpty(this.printCSS)? '#': this.printCSS
			, this.printTitle
			, Ext.isIE? 'document.execCommand(\'print\');': 'window.print();'
			, html
			);
        
		var popup = window.open('about:blank');
		if (!popup.opener) popup.opener = self
		popup.document.write(strHTML);
		popup.document.close();
		popup.focus();
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


/*
 * Catch JSON parsing errors and show error dialog
 * @type 
 */
Ext.decode = Ext.util.JSON.decode = function(jsonStr){
	try{
		var json = eval("(" + jsonStr + ')');
		if(json && json.redirectToLogin)
			document.location.href=BaseHref;
		
		return json;
	}
	catch (e)
	{

		switch(json.trim())
		{
			case 'NOTLOGGEDIN':
				document.location=BaseHref;
			break;

			case 'UNAUTHORIZED':
				Ext.Msg.alert(GO.lang['strUnauthorized'], GO.lang['strUnauthorizedText']);
			break;

			default:	
				json += '<br /><br />Ext.decode exception occurred';
				GO.errorDialog.show(GO.lang.serverError+'<br /><br />'+jsonStr);
				break;
		}
	}
};


/*
 * Don't position tooltip outside the screen


Ext.override(Ext.ToolTip,{

	adjustPosition : function(x, y){
    // keep the position from being under the mouse
    var ay = this.targetXY[1], h = this.getSize().height;
    if(this.constrainPosition && y <= ay && (y+h) >= ay){
        y = ay-h-5;
    }
    
    var body = Ext.getBody();
    var bodyHeight = body.getHeight();
    var tipSize = this.getSize();
    
    if(y+tipSize.height>bodyHeight)
    {
    	y=bodyHeight-tipSize.height-5;
    }
    
    if(y<0)
    {
    	y=5;
    }
    
    return {x : x, y: y};
  }    
}); */



Ext.apply(Ext.form.VTypes, {
    emailAddress:  function(v) {
		var email = /^[_a-z0-9\-+\&\']+(\.[_a-z0-9\-+\&\']+)*@[a-z0-9\-]+(\.[a-z0-9\-]+)*(\.[a-z]{2,100})$/i;
        return email.test(v);
    },
    emailAddressText: Ext.form.VTypes.emailText,
    emailAddressMask: /[a-z0-9_\.\-\'@\+\&]/i
});



Ext.override(
	Ext.grid.GridView,{
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
            if (GO.settings.theme!='Group-Office' && colModel.config[i].align == 'right') {
                properties.istyle = 'padding-right: 16px;';
            } else {
                delete properties.istyle;
            }
            
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
 Ext.removeNode = Ext.isIE && !Ext.isIE8 && !Ext.isIE9 && !Ext.isIE10 ? function() {

	var d;
	return function(n) {
		if (n && n.tagName != 'BODY') {
			(Ext.enableNestedListenerRemoval) ? Ext.EventManager.purgeElement(n, true) : Ext.EventManager.removeAll(n);
			d = d || document.createElement('div');
			d.appendChild(n);
			d.innerHTML = '';
			delete Ext.elCache[n.id];
		}
	};
}() : function(n) {
	if (n && n.parentNode && n.tagName != 'BODY') {
		(Ext.enableNestedListenerRemoval) ? Ext.EventManager.purgeElement(n, true) : Ext.EventManager.removeAll(n);
		n.parentNode.removeChild(n);
		delete Ext.elCache[n.id];
	}
};


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

Ext.override(Ext.KeyNav, {
	forceKeyDown: true // Required for Firefox 67	
});