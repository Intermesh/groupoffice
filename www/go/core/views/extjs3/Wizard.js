go.Wizard = Ext.extend(go.Window, {
	width: dp(500),
	height: dp(440),
	nextItem : null,
	previousItem : null,
	layout: "card",
	modal: true,
	initComponent : function() {

		this.backButton = new Ext.Button({
			text: t("Back"),
			handler: this.back,
			scope: this,
			disabled: true
		});
		
		this.continueButton = new Ext.Button({
			text: t("Continue"),
			handler: this.continue,
			scope: this
		});
		
		this.bbar = [this.backButton, '->', this.continueButton];
	
		go.Wizard.superclass.initComponent.call(this);
		
		this.addEvents('continue', 'back', 'finish');
		
		this.on('afterrender', function() {
			this.setActiveItem(0);
		}, this);
		
	},

	setActiveItem: function(item) {
		this.getLayout().setActiveItem(item);	
		item = this.getLayout().activeItem;
		
		var index = this.items.indexOf(item);
		this.nextItem = this.items.itemAt(index + 1);
		this.previousItem = this.items.itemAt(index - 1);
		this.backButton.setDisabled(!this.previouItem);		
		this.continueButton.setText(this.nextItem ? t("Continue") : t("Finish"));
		this.focus();
	},
	
	back: function() {
		this.setActiveItem(this.previousItem);		
		this.fireEvent('back', this, this.getLayout().activeItem);
	},
	
	continue : function() {
		var activeItem = this.getLayout().activeItem;
		
		if(activeItem.isValid && !activeItem.isValid()) {
			return false;
		}
		
		if(activeItem.onContinue && activeItem.onContinue(this) === false) {
			return false;
		}
		
		if(!this.nextItem) {
			this.fireEvent('finish', this, this.getLayout().activeItem);			
		} else {
			this.setActiveItem(this.nextItem);	
			this.fireEvent('continue', this, this.previousItem, this.getLayout().activeItem);
		}
	},
	
	focus: function () {
		this.getLayout().activeItem.focus();		
	}
	
});
