GO.form.HtmlEditor = function (config) {
	config = config || {};

	Ext.applyIf(config, {
		border: false,
		enableFont: false,
		headingsMenu: true
	});

	config.plugins = config.plugins || [];

	if (!Ext.isArray(config.plugins)) {
		config.plugins = [config.plugins];
	}

	var spellcheckInsertPlugin = new GO.plugins.HtmlEditorSpellCheck(this);
	var wordPastePlugin = new Ext.ux.form.HtmlEditor.Word();
	var hrPlugin = new Ext.ux.form.HtmlEditor.HR();
	var ioDentPlugin = new Ext.ux.form.HtmlEditor.IndentOutdent();
	var ssScriptPlugin = new Ext.ux.form.HtmlEditor.SubSuperScript();
	var rmFormatPlugin = new Ext.ux.form.HtmlEditor.RemoveFormat();
	var imageResizePlugin = new GO.plugins.HtmlEditorImageResize();
	var tablePlugin = new Ext.ux.form.HtmlEditor.NEWTablePlugin();


	if (GO.settings.pspellSupport) {
		config.plugins.unshift(spellcheckInsertPlugin);
	}

	config.plugins.unshift(
					ioDentPlugin,
					rmFormatPlugin,
					// wordPastePlugin,
					hrPlugin,
					ssScriptPlugin,
					imageResizePlugin,
					tablePlugin
					);

	if(config.headingsMenu) {
		var headingMenu = new Ext.ux.form.HtmlEditor.HeadingMenu();
		config.plugins.unshift(headingMenu);
	}

	GO.form.HtmlEditor.superclass.constructor.call(this, config);
};

Ext.extend(GO.form.HtmlEditor, Ext.form.HtmlEditor, {

	iframePad:dp(8),

	toolbarHidden: false,

	headingsMenu: true,

	enableSendShortcut : true,

	defaultLinkValue : 'https:/'+'/',


	initComponent: function() {
		GO.form.HtmlEditor.superclass.initComponent.apply(this);

		if(go.User) {
			this.enableSendShortcut = go.User.enableSendShortcut;
		}

		this.buttonTips['strikeThrough']= {
			title: 'Strike through',
				text: 'Strike through the selected text.',
				cls: 'x-html-editor-tip'
		};

		if(this.grow && !this.height) {
			this.height = this.growMinHeight;
		}

		// this.on('afterrender', function() {
		// 	if(this.grow && this.growMinHeight <= dp(46)) {
		// 		this.tb.hide();
		// 	}
		// }, this);
		this.on('initialize', function(){

			if(this.grow) {
				this.doGrow();
				this.on("sync", this.doGrow, this);
			}
		},this);

		if(this.enableSendShortcut) {
			this.on('activate', function () {
				this.registerSubmitKey();
			}, this);
		}
	},

	emptyTextRegex: '<span[^>]+[^>]*>{0}<\/span>',
	emptyTextTpl: '<span style="color:#ccc;">{0}</span>',
	emptyText: '',

	blankText : 'This field is required',

	allowBlank: true,

	getErrors: function(value) {

		var errors = Ext.form.HtmlEditor.superclass.getErrors.apply(this, arguments);

		value = Ext.isDefined(value) ? value : this.getValue();

		value = Ext.util.Format.stripTags(value).trim().replace(/\u200B/g,'');

		if (!this.allowBlank && value.length < 1) {
			errors.push(this.blankText);
		}

		return errors;
	},

	markInvalid : function(msg){

		if (this.rendered && !this.preventMark) {
			msg = msg || this.invalidText;

			var mt = this.getMessageHandler();
			if(mt){
				mt.mark(this, msg);
			}else if(this.msgTarget){
				this.el.addClass(this.invalidClass);
				var t = Ext.getDom(this.msgTarget);
				if(t){
					t.innerHTML = msg;
					t.style.display = this.msgDisplay;
				}
			}
		}

		this.setActiveError(msg);
	},

	clearInvalid : function(){

		if (this.rendered && !this.preventMark) {
			this.el.removeClass(this.invalidClass);
			var mt = this.getMessageHandler();
			if(mt){
				mt.clear(this);
			}else if(this.msgTarget){
				this.el.removeClass(this.invalidClass);
				var t = Ext.getDom(this.msgTarget);
				if(t){
					t.innerHTML = '';
					t.style.display = 'none';
				}
			}
		}

		this.unsetActiveError();
	},


	registerSubmitKey: function() {
		var doc = this.getDoc();
		if (Ext.isGecko){
			Ext.EventManager.on(doc, {
				keydown: this.fireSubmit,
				scope: this
			});
		}

		if (Ext.isIE || Ext.isWebKit || Ext.isOpera) {
			Ext.EventManager.on(doc, 'keydown', this.fireSubmit,
				this);
		}
	},

	fireSubmit : function(e) {
		if (e.ctrlKey && Ext.EventObject.ENTER == e.getKey()) {
			e.preventDefault();
			this.fireEvent('ctrlenter',this);
			return false;
		}
	},

	initEditor: function () {

		GO.form.HtmlEditor.superclass.initEditor.call(this);

		this.addEvents({attach: true});

		//Following is needed when using animateTarget in windows. But since that doesn't perform well we should look at using css transitions instead of js animations
		var doc = this.getEditorBody();

		doc.addEventListener('paste', this.onPaste.createDelegate(this));
		doc.addEventListener('drop', this.onDrop.createDelegate(this));
		doc.addEventListener('keyup', this.onKeyUp.createDelegate(this));
		// this.on('beforesync', this.onInput, this);

		//Fix for Tooltips in the way of email #276
		doc.addEventListener("mouseenter", function() {
			setTimeout(function() {
				Ext.QuickTips.getQuickTip().hide();
			}, 500);

		});

		go.ActivityWatcher.registerDocument(doc);

		//other browsers are already registered in parent function
		if(Ext.isGecko) {
			Ext.EventManager.on(doc, 'keydown', this.fixKeys, this);
		}

		if(Ext.isChrome && navigator.appVersion.indexOf("Chrome/96.") > -1) {
			console.warn("Disable spell check because it's slow on Chrome v96.")

			doc.spellcheck = false;
		}

	},

	debounceTimeout : null,

	onKeyUp : function(e) {

		//Only run on enter, space or tab
		if(!this.debounceTimeout && e.keyCode != 13 && e.keyCode != 32 && e.keyCode != 9) {
			return;
		}

		this.scheduleAutoLink();
	},

	scheduleAutoLink : function() {
		clearTimeout(this.debounceTimeout);
		this.debounceTimeout = setTimeout( () => {
			clearTimeout(this.debounceTimeout);
			this.debounceTimeout = undefined;
			this.convertUrisToAnchors();
		}, 500);
	},


	convertUrisToAnchors: function() {
		const walk = (node) => {

			if(node.nodeType == Node.ELEMENT_NODE && node.tagName == "A") {
				// don't traverse into anchor tags
				return;
			}

			//walk nodes recursively
			node.childNodes.forEach(walk);

			if(node.nodeType == Node.TEXT_NODE) {
				if(node == this.getDoc().getSelection().anchorNode) {
					return;
				}

				if (node.textContent && node.textContent.indexOf("http") > -1) {
					const anchored = this.replaceUriWithAnchor(node.textContent);
					if (anchored != node.textContent) {
						const tmp = document.createElement("span");
						tmp.innerHTML = anchored;
						node.replaceWith(tmp);
					}
				}
			}
		}

		walk(this.getEditorBody());
	},

	replaceUriWithAnchor : function(html) {
		// Regular expression to match URIs that are not inside anchor tags
		const uriRegex = /(https?:\/\/[^\s]+|ftp:\/\/[^\s]+)/ig;

		// Replace matched URIs with anchor tags
		return html.replace(uriRegex, (url) => {
			return `<a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a>`;
		});
	},

	onDrop: function(e) {
		if(!e.dataTransfer.files) {
			return;
		}

		//prevent browser from navigating to dropped file
		e.preventDefault();

		//make sure editor has focus
		this.focus();

		//this is needed if the editor has not been activated yet.
		this.updateToolbar();

		Array.from(e.dataTransfer.files).forEach(function (file) {
			go.Jmap.upload(file, {
				scope: this,
				success: function (response) {
					var imgEl = null;
					if (file.type.match(/^image\//)) {
						var domId = Ext.id(),
							img = '<img style="max-width: 100%" id="' + domId + '" src="' + go.Jmap.downloadUrl(response.blobId) + '" alt="' + file.name + '" />';
						this.insertAtCursor(img);
						imgEl = this.getDoc().getElementById(domId);

						if(imgEl) {
							imgEl.addEventListener("load", () => {
								const width = imgEl.offsetWidth, height = imgEl.offsetHeight;
								imgEl.setAttribute('style', `max-width: 100%;height:auto;aspect-ratio: ${width} / ${height};`);
							});
						}
					}

					this.fireEvent('attach', this, response, file, imgEl);
				}
			});
		}, this);


	},

	onPaste: function (e) {
		var clipboardData = e.clipboardData, me = this;
		if (clipboardData.items) {
			//Chrome /safari has clibBoardData.items
			for (var i = 0, l = clipboardData.items.length; i < l; i++) {
				var item = clipboardData.items[i];

				//Some times clipboard data holds multiple versions. When copy pasting from excel you get html, plain text and an image.
				//We prefer to use the html in that case so we exit if found.
				if (item.type == 'text/html') {
					e.preventDefault();
					item.getAsString((s) => {
						//convert style in the head to an inline style tag
						const inlined = go.util.convertStyleToInline(s);
						this.execCmd("insertHTML", inlined);
						// this.insertAtCursor(inlined);
					});
					return;
				}

				if (item.kind == "file" && item.type.match(/^image\//)) {
					
					e.preventDefault();
					var reader = new FileReader();
					reader.onload = function (event) {
						me.handleImage(event.target.result);
					}
					reader.readAsDataURL(item.getAsFile());
				}
			}
		} else
		{
			//Firefox
			if (-1 === Array.prototype.indexOf.call(clipboardData.types, 'text/plain')) {					
				this.findImageInEditor();
			}
		}


	},
	
	findImageInEditor: function () {
		var el = this.getDoc();

		var images = el.getElementsByTagName('img');
		var timespan = Math.floor(1000 * Math.random());
		for (var i = 0, len = images.length; i < len; i++) {
			images[i]["_paste_marked_" + timespan] = true;
		}
		setTimeout(function () {
			var newImages = el.getElementsByTagName('img');
			for (var i = 0, len = newImages.length; i < len; i++) {
				if (!newImages[i]["_paste_marked_" + timespan]) {
					this._handleImage(newImages[i].src);
					newImages[i].remove();
				}
			}

		}, 1);
	},
	
	insertImage : function(src) {
		var domId = Ext.id(), img = '<img style="max-width: 100%" id="' + domId + '" src="' + src + '" alt="pasted image" />';
		this.insertAtCursor(img);
		
		return  this.getDoc().getElementById(domId);		
	},

	handleImage: function (src) {



		var dataURLtoBlob = function (dataURL, sliceSize) {
			var b64Data, byteArray, byteArrays, byteCharacters, byteNumbers, contentType, i, m, offset, slice, _ref;
			if (sliceSize == null) {
				sliceSize = 512;
			}
			if (!(m = dataURL.match(/^data\:([^\;]+)\;base64\,(.+)$/))) {
				return null;
			}
			_ref = m, m = _ref[0], contentType = _ref[1], b64Data = _ref[2];
			byteCharacters = atob(b64Data);
			byteArrays = [];
			offset = 0;
			while (offset < byteCharacters.length) {
				slice = byteCharacters.slice(offset, offset + sliceSize);
				byteNumbers = new Array(slice.length);
				i = 0;
				while (i < slice.length) {
					byteNumbers[i] = slice.charCodeAt(i);
					i++;
				}
				byteArray = new Uint8Array(byteNumbers);
				byteArrays.push(byteArray);
				offset += sliceSize;
			}
			return new Blob(byteArrays, {
				type: contentType
			});
		};

		var loader, me = this;
		loader = new Image();
		loader.onload = function () {
			var blob, canvas, ctx, dataURL;
			canvas = document.createElement('canvas');
			canvas.width = loader.width;
			canvas.height = loader.height;
			ctx = canvas.getContext('2d');
			ctx.drawImage(loader, 0, 0, canvas.width, canvas.height);
			dataURL = null;
			try {
				dataURL = canvas.toDataURL('image/png');
				blob = dataURLtoBlob(dataURL);
			} catch (_error) {
			}

			if (dataURL) {
				var file = new File([blob], "pasted-image." + blob.type.substring(6),{type: blob.type});

				var imgEl = me.insertImage(BaseHref + "views/Extjs3/themes/" + go.User.theme + "/img/loading-spinner.gif");
				imgEl.setAttribute("style", "width: 80px; height: 80px");

				go.Jmap.upload(file, {
					success: function(response) {
						imgEl.setAttribute("src", go.Jmap.downloadUrl(response.blobId));
						imgEl.setAttribute('style', `max-width: 100%;height:auto;aspect-ratio: ${loader.width} / ${loader.height};`);
						me.fireEvent('attach', me, response.blobId, file, imgEl);
					}
				});

			}
		};

		return loader.src = src;
	},

	/**
	 * Executes a Midas editor command directly on the editor document.
	 * For visual commands, you should use {@link #relayCmd} instead.
	 * <b>This should only be called after the editor is initialized.</b>
	 * @param {String} cmd The Midas command
	 * @param {String/Boolean} value (optional) The value to pass to the command (defaults to null)
	 */
	execCmd: function (cmd, value) {
		var doc = this.getDoc();

		if (cmd === 'createlink' && Ext.isGecko) {
			// If firefox is used, then manually add the "a" tag to the text
			var t = this.getSelectedText();
			if (t.length < 1) {
				value = '<a href="' + value + '" target="_blank">' + value + "</a>";
				this.insertAtCursor(value);
			}
		}

		doc.execCommand(cmd, false, value === undefined ? null : value);

		this.syncValue();
	},

	getSelectedText: function () {

		var frame = this.iframe;
		var frameWindow = frame.contentWindow;
		var frameDocument = frameWindow.document;

		if (frameDocument.getSelection)
			return frameDocument.getSelection().toString();
		else if (frameDocument.selection)
			return frameDocument.selection.createRange().text;
	},

	setValue: function (value) {

		if (this.win && Ext.isChrome && this.activated) {

			//set cursor position on top
			var range = this.win.document.createRange();
			range.setStart(this.win.document.body, 0);
			range.setEnd(this.win.document.body, 0);

			var sel = this.win.document.getSelection();

			if(sel) {
				sel.removeAllRanges();
				sel.addRange(range);
			}
		}
		GO.form.HtmlEditor.superclass.setValue.call(this, value);
	},

	/**
	 * Automatically grow field with content
	 */
	grow: false,

	/**
	 * Minimum height for field
	 */
	growMinHeight: dp(46),

	/**
	 * Maximum height for field
	 */
	growMaxHeight: dp(480),

	doGrow : function() {
		var body = this.getEditorBody();

		body.style.height = 'auto';
		body.style.display = 'inline-block';

		body.style.minHeight =  this.growMinHeight + "px";
		body.style.padding = window.getComputedStyle(this.el.dom).padding;
		body.style.boxSizing = "border-box";
		body.style.width = "100%";
		body.style.lineHeight = dp(20) + "px";

		var h =  Math.max(this.growMinHeight, body.offsetHeight + dp(20)); // 400  max height

		// if(h > dp(48)) {
		// 	this.tb.show();
		// 	//workaround for combo
		// 	if(this.tb.items.itemAt(0).wrap) {
		// 		this.tb.items.itemAt(0).wrap.dom.style.width = "100px";
		// 	}
		// 	this.tb.doLayout();
		// } else {
		// 	this.tb.hide();
		// }

		h +=  this.tb.el.getHeight();

		if(this.growMaxHeight && h > this.growMaxHeight) {
			h = this.growMaxHeight;
		}

		this.setHeight(h);

	},

	onFirstFocus: function () {

		this.initPunctuationCorrection();

		this.activated = true;
		this.disableItems(this.readOnly);
		if (Ext.isGecko) {
			try {
				this.execCmd('useCSS', true);
				this.execCmd('styleWithCSS', false);
				this.execCmd('insertBrOnReturn', false);
				this.execCmd('enableObjectResizing', true);
			} catch (e) {
				console.warn(e);
			}
		}
		this.fireEvent('activate', this);
	},
	createToolbar: Ext.form.HtmlEditor.prototype.createToolbar.createSequence(function (editor) {
		this.tb.enableOverflow = true;
	}),

	initPunctuationCorrection: function () {
		if (GO.settings.auto_punctuation != 1)
			return;

		var me = this;
		var doc = me.getDoc();

		Ext.EventManager.on(doc, 'keydown', me.correctPunctuation, me);
	},

	lastKey: false,
	capNext: true,

	correctPunctuation: function (ev) {
		ev = ev.browserEvent;
		if (!this.capNext) {
			if(ev.key === 'Enter' || (ev.key === ' ' && ['.', '!', '?'].indexOf(this.lastKey) !== -1)) {
				this.capNext = true;
			}
		} else {
			if (ev.key.match(/^[a-z]$/)) {
				ev.preventDefault();
				this.insertAtCursor(ev.key.toUpperCase());
				this.capNext = false;
			} else if (ev.key != ' ' && ev.key != 'Enter') {
				this.capNext = false;
			}
		}
		this.lastKey = ev.key;
	},

	fixKeys: function (e) { // load time branching for fastest keydown performance

				var k = e.getKey(), doc;
				if(
					Ext.isWebKit && e.shiftKey && k == e.ENTER && (doc = this.getDoc()) &&
					(doc.queryCommandState('insertorderedlist') || doc.queryCommandState('insertunorderedlist'))
				) {
					e.stopEvent();
					this.execCmd('InsertHtml',Ext.isGecko ? '<br />' : '<br /><br />');
					this.deferFocus();
				} else if (k == e.TAB) {
					e.preventDefault();
					doc = this.getDoc();
					if (doc.queryCommandState('insertorderedlist') || doc.queryCommandState('insertunorderedlist')) {
						this.execCmd(e.shiftKey ? 'outdent' : 'indent');
					}else {
						this.execCmd('InsertText', '\t');
					}
					this.deferFocus();
				}


	},

	//Overwritten to fix font size bug in chrome
	adjustFont: function (btn) {
		var adjust = btn.getItemId() == 'increasefontsize' ? 1 : -1,
						doc = this.getDoc(),
						v = parseInt(doc.queryCommandValue('FontSize') || 2, 10);
		if (Ext.isAir) {


			if (v <= 10) {
				v = 1 + adjust;
			} else if (v <= 13) {
				v = 2 + adjust;
			} else if (v <= 16) {
				v = 3 + adjust;
			} else if (v <= 18) {
				v = 4 + adjust;
			} else if (v <= 24) {
				v = 5 + adjust;
			} else {
				v = 6 + adjust;
			}
			v = v.constrain(1, 6);
		} else {
			v = Math.max(1, v + adjust);
		}
		this.execCmd('FontSize', v);
	},

	createLink: function () {
		var url = prompt(this.createLinkText, this.defaultLinkValue);
		if (url && url != 'https:/' + '/') {
			if (Ext.isSafari) {
				this.execCmd("createlink", url);
				this.updateToolbar();
			} else {
				// this.relayCmd("createlink", url);
				let t = this.getSelectedText();
				if (t.length < 1) {
					t = url;
				}
				this.insertAtCursor('<a href="' + url + '" target="_blank">' + t + "</a>");
			}
		}
	},

	setDesignMode : function(readOnly){
		this.getEditorBody().contentEditable = readOnly;
   },
	
	onResize : function(w, h){
	  Ext.form.HtmlEditor.superclass.onResize.apply(this, arguments);
	  if(this.el && this.iframe){
			if(Ext.isNumber(w)){
				 var aw = w - this.wrap.getFrameWidth('lr');
				 this.el.setWidth(aw);
				 this.tb.setWidth(aw);
				 this.iframe.style.width = Math.max(aw, 0) + 'px';
			}
			if(Ext.isNumber(h)){
				 var ah = h - this.wrap.getFrameWidth('tb') - this.tb.el.getHeight();
				 this.el.setHeight(ah);
				 this.iframe.style.height = Math.max(ah, 0) + 'px';
				 var bd = this.getEditorBody();
				 if(bd){
					 bd.style.height = Math.max((ah - (this.iframePad*2)), 0) + 'px';
				 }
			}
		}
   }

});

Ext.reg('xhtmleditor', GO.form.HtmlEditor);
