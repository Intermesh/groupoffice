/** 
 * RPM Solutions UK Ltd
 * Using aspell and pspell to enbale spell checking.
 * 
 * @Code By RPM Solutions UK Ltd
 * @author Shaun Forsyth <shaun@rpm-solutions.co.uk>
 */

GO.plugins.HtmlEditorSpellCheck = function(emailComposer) {
    
    this.EmailComposer = emailComposer;
    
    /*Ext.apply(this, config);*/
    
    this.init = function(htmlEditor) {
        this.editor = htmlEditor;
        this.editor.on('render', this.onRender, this);
    };
    
    this.addEvents({'insert' : true});
}

Ext.extend(GO.plugins.HtmlEditorSpellCheck, Ext.util.Observable, {
	onRender :  function() {
	  if (!Ext.isSafari) {

				var langs = [];
				var lang;
				for(var i=0;i<GO.Languages.length;i++)
				{
					lang = {
						text:GO.Languages[i][1],
						lang:GO.Languages[i][0],
						handler:function(item){
							this.spellcheck(item.lang);
						},
						scope:this
					};
					if(GO.settings.language==lang.lang){
						langs.unshift(lang,'-');
					}else
					{
						langs.push(lang);
					}
				}

        this.editor.tb.add({
            itemId : 'htmlSpellCheck',
            cls : 'x-btn-icon go-edit-spellcheck',
            enableToggle: false,
            scope: this,            
			menu:{
				items:langs
			},
            clickEvent:'mousedown',
            tabIndex:-1,
			overflowText: GO.lang.spellcheck,
            tooltip:{title:GO.lang.spellcheck, text:GO.lang.spellcheckdetails}
        });
    }
	},
	
	spellcheck : function (lang) {
		if (this.EmailComposer.getName() == 'htmlbody') {
			var self = this;
			Ext.Ajax.request({
				url: GO.url("core/spellcheck"),
				success: function (result,request){
					self.processResults(result,request,self);
				},
				failure: this.problem,
				params: {tocheck: this.editor.getValue(),lang: lang}
			});
			//This should be non blocking.. the user attempted a spell check
			this.editor.SpellCheck = true;
		}else{
			//Only Supports HTML, Plain doesn't have a tool bar
		}
	},
	
	processResults : function(result, request,self){
		var jsonData = Ext.util.JSON.decode(result.responseText);
		if (jsonData.errorcount == 0){
			Ext.Msg.alert(GO.lang.spellcheck,GO.lang.spellcheckNoError);
		}else{
			self.showSpellChecker(jsonData.errorcount,jsonData.text,self);
		}
	},
	
	showSpellChecker : function (errors, text, self){
		
		/*htmlobj = Ext.DomHelper.createDom(text);*/
		
		var PanelTitle = GO.lang.spellcheckNoErrors.replace(/\{1\}/ig,errors);
		
		self.textarea = new Ext.Panel({
					title: PanelTitle,
					region: 'north',
					cls : 'go-form-panel x-spell-checker',
					autoScroll : true,
					html: text
					});
		
		self.wnd = new Ext.Window({
			width: 600,
			height: 400,
			modal : true,
			closeAction: 'close',
			closable : true,
			layout:'fit',
			title: GO.lang.spellcheck,
			items : [self.textarea],
			buttons: [
				{
					text:GO.lang.cmdSave,
					handler: function(){
						self.UpdateEditorValue(self);
					}
				},
				{
					text: GO.lang.cmdCancel,
					handler: function(){
							self.closeWindow(self);
						}
				}]
		});
		self.wnd.show();
		
		Ext.select('span.spelling',self.textarea).on('click',function (e) {self.showSuggestions(e,this);});
		
	},
	
	UpdateEditorValue: function (self){
		//There has to be a better way to do this.
		var html = this.textarea.el.dom.childNodes[1].childNodes[0].innerHTML;
		
		//Remove left over spans from words which were not corrected
		var Pattern = new RegExp('<span [\\s\\S]*?class=(")?spelling(")?[\\s\\S]*?>(\\w+)[\\s\\S]*?<\/span>','mig');
		
		//html = this.decode(html);
		html = html.replace(Pattern,'$3 ');
		
		self.editor.setValue(html);
		self.closeWindow(self);
	},
	
	closeWindow: function (self){
		self.wnd.destroy();	
	},
	
	showSuggestions : function (e,self){
		//e is the event used for xy
		//self is the span that was clicked
		//this (not understanding scope and how it works this time!) is the spelling object
		var items = self.getElementsByTagName('li');
		var menuitems = new Array();
		var speller = this;
		for (var i = 0; i < items.length; i++){
			var word = items[i].innerHTML;
			menuitems.push (
				new Ext.Action({
					text: word,
					handler: function (ev,target) {speller.replaceSpelling(this.text,self);}
				})
			);
		}
		
		this.Suggestions = new Ext.menu.Menu({
						items: menuitems									 
		});
		
		this.Suggestions.showAt(e.getXY());
		
	},
	
	replaceSpelling : function (word,self){		
		var replaceobject;
		//IE doesn't treat whitespace as dom elements like firefox and chrome.
		if (Ext.isIE && self.getAttribute('ieAfterObject') == ' '){
			replaceobject = document.createTextNode(word+' ');	
		}else{
			replaceobject = document.createTextNode(word);
		}
		self.parentNode.replaceChild(replaceobject,self);
		this.Suggestions.destroy();
	},
	
	problem : function (result,request) {
		Ext.Msg.show({
			title: GO.lang.spellcheck,
			msg: GO.lang.spellcheckServerError,
			buttons: Ext.Msg.OK,
			icon: Ext.Msg.WARNING
		});	
	}
});