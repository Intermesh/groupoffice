/* global go, Ext */

go.login.LoginDialog = Ext.extend(go.Window, {

	closable: false,
	resizable: false,
	draggable: false,

//	methods: [],
	
	initComponent: function() {

//		this.topPanel = new Ext.Panel({
//			id:'login-top-panel',
//			region: 'north',
//			items:[
//				this.logoComp = new Ext.BoxComponent({cls: "go-app-logo"})
////				this.welcomeComp = new Ext.BoxComponent({
////					autoEl: 'p',
////					cls: "login-user-welcome",
////					setWelcomeMessage: function(name){
////						var curHr = (new Date()).getHours();
////						var html = (curHr < 12) ? t('Good morning') : 
////								  (curHr < 18) ? t('Good afternoon') : 
////													t('Good evening');
////						this.getEl().update(html+' '+name);
////					},
////					clearWelcomeMessage: function(){
////						this.getEl().update('');
////					}
////				}),
////				this.avatarComp = new Ext.BoxComponent({
////					autoEl: 'img',
////					cls: "login-avatar user-img",
////					hidden: true,
////					setImageUrl: function(url){
////						// TODO: Enable this when url is OK
////						//this.getEl().dom.src = url;
////					},
////					clearAvatar: function(){
////						this.setImageUrl('');
////						this.setVisible(false);
////					}
////				})
//			]
//		});

		this.wizard = new Ext.Panel({
			//deferredRender:true,
			layout: 'card',
			activeItem: 1,
			items: [
				this.forgotPanel = new go.login.ForgotPanel(),
				this.userNamePanel = new go.login.UsernamePanel()
			],
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: this.onSpecialKey,
				scope:this
			}]
		});
		
		for(var i=0; i< go.AuthenticationManager.panels.length; i++){
			this.wizard.add(go.AuthenticationManager.panels[i].panel);
		}

		Ext.apply(this,{
			buttonAlign: 'left',
			width: dp(480),
			height: dp(260),
			layout:'fit',
			title: t("Login required"),
			items: [new Ext.form.FieldSet({
					region:'center',
					items:[this.wizard]
				})],
			buttons:[
				this.resetButton = new Ext.Button({
					text: t("Cancel"),
					hidden:true,
					handler: this.reset,
					scope:this
				}),
				this.forgotBtn = new Ext.Button({
					text: t("Forgot username?"),
					handler: this.showForgot,
					scope:this
				}),
				'->',
				this.nextButton = new Ext.Button({
					text: t("Next"),
					handler: this.submitPanel,
					scope:this
				})
			]
		});
		
		go.login.LoginDialog.superclass.initComponent.call(this);
	},
	next : function(index){
		
		var current = this.wizard.items.findIndex('id', this.wizard.getLayout().activeItem.id),
		next = (typeof index !== 'undefined') ? index : current+1;
		
		//skip panel if not required for user
		//console.log(this.wizard.items.itemAt(next).id);
		if(next > 1) {			
			
			var nextItem = this.wizard.items.itemAt(next);
			
			if(!nextItem) {
				//next is called when authentication is complete and there are no panels left. Should we call next() in this case?
				return;
			}
			
			while(next < this.wizard.items.length && go.AuthenticationManager.userMethods.indexOf(nextItem.id) == -1) {
				next++;
			}			
		}
 
		this.wizard.getLayout().setActiveItem(next);

		switch(next) {
			case 0: // forgot password
				this.nextButton.setText(t('Send'));
				this.resetButton.setVisible(true);
				this.forgotBtn.setVisible(false);
				break
			case 1: // username
				this.nextButton.setText(t('Next'));
				this.forgotBtn.setText(t('Forgot username or password?'));
				this.forgotBtn.setVisible(true);
				this.resetButton.setVisible(false);
				break;
			default: // first auth method
				this.resetButton.setVisible(true);				
				this.nextButton.setText(t('Login'));
				break;
		}

		this.focus();
	},
	
	reset : function(){

//		this.welcomeComp.clearWelcomeMessage();
//		this.avatarComp.clearAvatar();
		this.wizard.items.each(function(item, index, length){
			item.reset();
		},this);
		this.next(1); 
	},
	
	showForgot: function() {
		this.next(0);
	},
	
	focus: function() {
		// If it's the username panel then set the username field active
		var i = this.wizard.getLayout().activeItem;
		if(i.rendered) {
			i.focus();
		} else
		{
			i.on('render', function() {
				i.focus();
			}, this, {single: true});
		}
		
	},
	
	onSpecialKey : function(key, e) {
		e.preventDefault();	
		this.submitPanel();
	},
	
	submitPanel : function() {
		if(!this.wizard.getLayout().activeItem.getForm().isValid()){
			return;
		}
		this.wizard.getLayout().activeItem.submit(this)
	}
});
