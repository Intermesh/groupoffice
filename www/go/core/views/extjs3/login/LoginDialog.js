/* global go, Ext */

go.login.LoginDialog = Ext.extend(go.Window, {

	closable: false,
	resizable: false,
	draggable: false,
	cls: "go-login-dialog",
	maximized: false,
	
	minWidth : 40,
	minHeight: 40,
	width: dp(480),
	height: GO.settings.config.logoutWhenInactive > 0 ? dp(300) : dp(356),
	layout:'card',
	title: t("Login required"),

	activeItem: 0,
	
	initComponent: function() {

		Ext.apply(this,{

			listeners: {
				scope:this,
				add: function(panel, cmp, index) {
					cmp.on('success', function() {
						this.next();
					}, this);
					
					cmp.on('cancel', function() {
						this.reset();
					}, this);
				}
			}		
		});
		
		go.login.LoginDialog.superclass.initComponent.call(this);

		this.add(this.userNamePanel = new go.login.UsernamePanel());
		for(var i=0; i< go.AuthenticationManager.panels.length; i++){
			this.add(go.AuthenticationManager.panels[i].panel);
		}
	},


	firstSigninButtonAdded: false,


	addSignInButton: function (btn) {

		const pnl = this.userNamePanel.items.first().items.first();

		if(!this.firstSigninButtonAdded) {
			this.firstSigninButtonAdded = true;
			this.height += dp(48);

			pnl.insert(0, {
				xtype: "box",
				autoEl: "hr"
			})
		}

		pnl.insert(0, btn);

		this.height += dp(72);
	},
	
	next : function(index){

		var current = this.items.findIndex('id', this.getLayout().activeItem.id),
		next = (typeof index !== 'undefined') ? index : current+1;
		
		//skip panel if not required for user		
		//console.log(this.wizard.items.itemAt(next).id);
		if(next > 0) {			
			
			var nextItem = this.items.itemAt(next);

			if(!nextItem) {
				//next is called when authentication is complete and there are no panels left. Should we call next() in this case?
				return;
			}

			while(next < this.items.length && go.AuthenticationManager.userAuthenticators.indexOf(nextItem.id) == -1) {
				next++;
				this.next(next);
			}
		}
 
		this.getLayout().setActiveItem(next);
		this.focus();
	},
	
	reset : function(){

		this.items.each(function(item, index, length){
			item.reset();
		},this);
		this.next(0); 
	},
		
	focus: function() {
		// timeout is needed for autofill in chrome. Otherwise the label does not move to the top of the field somehow
		// I guess the onautofillstart animation does not run in that case.

		setTimeout(() => {
			// If it's the username panel then set the username field active
			var i = this.getLayout().activeItem;
			if(!i) {
				return;
			}
			if (i.rendered) {
				i.focus();
			} else {
				i.on('render', function () {
					i.focus();
				}, this, {single: true});
			}
		},500);
		
	}
	
	
});
