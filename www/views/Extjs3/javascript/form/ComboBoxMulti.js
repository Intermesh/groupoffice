/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ComboBoxMulti.js 22070 2018-01-05 15:13:12Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
/**
 * @class GO.form.ComboBoxMulti
 * @extends GO.form.ComboBox
 * Adds freeform multiselect and duplicate entry prevention to the standard combobox
 * @constructor
 * Create a new ComboBoxMulti.
 * @param {Object} config Configuration options
 */
GO.form.ComboBoxMulti = function(config){
	
	config = config || {};

	Ext.apply(this, config);


	if(this.textarea) {
		config.defaultAutoCreate = {
			tag: "textarea",
			autocomplete: "off",
			autocorrect: "off",
			autocapitalize: "off",
			spellcheck: "false"
		};
	}
	 //config.height = dp(24);
	GO.form.ComboBoxMulti.superclass.constructor.call(this, config);


	if(this.textarea) {
		this.on('render', function () {
			//this.syncHeight();
			this.getEl().on('input', function (e) {
				//this.syncHeight();
			}, this);
		}, this);
	}
};

Ext.extend(GO.form.ComboBoxMulti, GO.form.ComboBox, {
		hideTrigger: true,
		queryDelay: 500,
		typeAhead: false,
		/**
		 * @cfg {String} sep is used to separate text entries
		 */
		sep: ',',
		textarea: false,

		initComponent: function() {
			this.textSizeEl = Ext.DomHelper.append(document.body, {
				tag: "pre", cls: "x-form-grow-sizer"
			});

			GO.form.ComboBoxMulti.superclass.initComponent.call(this);
		},

		getParams: function (q) {
			//override to add q filter for JMAP API
			this.store.baseParams.filter = this.store.baseParams.filter || {};
			this.store.baseParams.filter.text = q;

			var p = GO.form.ComboBoxMulti.superclass.getParams.call(this, q);
			//delete p[this.queryParam];

			return p;
		},

		growMin: dp(32),
		growMax: dp(120),

		growAppend : '&#160;\n&#160;',

		grow: true,

		autoSize: function(){
			if(!this.grow || !this.textSizeEl || !this.el){
				return;
			}
			var el = this.el,
				v = Ext.util.Format.htmlEncode(el.dom.value),
				ts = this.textSizeEl,
				h;

			Ext.fly(ts).setWidth(this.el.getWidth());
			if(v.length < 1){
				v = "";
			}else{
				// v += this.growAppend;
				// if(Ext.isIE){
				// 	v = v.replace(/\n/g, '&#160;<br />');
				// }
			}
			ts.innerHTML = v;

			h = Math.min(this.growMax, Math.max(ts.offsetHeight, this.growMin));
			if(h != this.lastHeight){
				this.lastHeight = h;
				this.el.setHeight(h);
				this.fireEvent("autosize", this, h);
			}
		},

		getCursorPosition: function () {

			if (document.selection) { // IE
				var r = document.selection.createRange();
				if (!r)
					return false;

				var d = r.duplicate();

				if (!this.el.dom)
					return false;

				d.moveToElementText(this.el.dom);
				d.setEndPoint('EndToEnd', r);
				return d.text.length;
			} else {
				return this.el.dom.selectionEnd;
			}
		},

		getActiveRange: function () {
			var s = this.sep;
			var p = this.getCursorPosition();
			var v = this.getRawValue();
			var left = p;
			while (left > 0 && v.charAt(left) != s) {
				--left;
			}
			if (left > 0) {
				left++;
			}
			return {
				left: left,
				right: p
			};
		},

		getActiveEntry: function () {
			var r = this.getActiveRange();
			return this.getRawValue().substring(r.left, r.right).trim();//.replace(/^s+|s+$/g, '');
		},

		replaceActiveEntry: function (value) {
			var r = this.getActiveRange();
			var v = this.getRawValue();
			if (this.preventDuplicates && v.indexOf(value) >= 0) {
				return;
			}
			var pad = (this.sep == ' ' ? '' : ' ');
			this.setValue(v.substring(0, r.left) + (r.left > 0 ? pad : '') + value + this.sep + pad + v.substring(r.right));

			var p = r.left + value.length + 2 + pad.length;
			// this.selectText.defer(200, this, [p, p]);
		},

		// setValue: function (v) {
		// 	GO.form.ComboBoxMulti.superclass.setValue.call(this, v);
		// 	this.syncHeight();
		// },

		onSelect: function (record, index) {
			if (this.fireEvent('beforeselect', this, record, index) !== false) {
				var value = Ext.util.Format.htmlDecode(record.data[this.valueField || this.displayField]);
				this.replaceActiveEntry(value);
				this.collapse();
				this.fireEvent('select', this, record, index);
			}
		},

		getValue : function() {
			return this.getRawValue();
		},

		initQuery: function () {
			if (this.getEl().id === document.activeElement.id) {
				this.doQuery(this.getActiveEntry());
			}
		},

		onLoad: function () {
			if (!this.hasFocus) {
				return;
			}
			if (this.store.getCount() > 0 || this.listEmptyText) {
				this.expand();
				this.restrictHeight();
				if (this.lastQuery == this.allQuery) {

					if (this.autoSelect !== false && !this.selectByValue(this.value, true)) {
						this.select(0, true);
					}
				} else {
					if (this.autoSelect !== false) {
						this.selectNext();
					}
					if (this.typeAhead && this.lastKey != Ext.EventObject.BACKSPACE && this.lastKey != Ext.EventObject.DELETE) {
						this.taTask.delay(this.typeAheadDelay);
					}
				}
			} else {
				this.collapse();
			}

		}
	});
