go.menu.EmojiMenu = Ext.extend(Ext.menu.Menu, {

	enableScrolling : false,

	hideOnClick : true,

	cls : 'x-emoji-menu',
	width: dp(240),
	/** 
	 * @cfg {String} paletteId
	 * An id to assign to the underlying color palette. Defaults to <tt>null</tt>.
	 */
	paletteId : null,
    
   initComponent : function(){
		 
		var emojiPanel = new Ext.extend(Ext.Component, {
			emojis: {'people':
				["😁","😂","😃","😄","😅","😆","😉","😊","😋","😌","😍",
				 "😏","😒","😓","😔","😖","😘","😚","😜","😝","😞","😠",
				 "😡","😢","😣","😤","😥","😨","😩","😪","😫","😭","😰",
				 "😱","😲","😳","😵","😷", 
				 // cats
				 "😸","😹","😺","😻","😼","😽",
				 "😾","😿","🙀", 
				 // monkey
				 "🙈","🙉","🙊",
				 //bodyparts
				 "👀","👂","👃","👄","👅","👆","👇","👈","👉","👊","👋",
				 "👌","👍","👎","👏","👐","💪","✊","✋","✌","🙌","🙏",
				 // zzz
				 "💤","💰","💼","📁","💿","📄","📅","📋","📎","📒","📖",
				 "📝","📠","📞","📦","📧","📷","🔊","🔍","🔔","🔗","🔖",
				 "🕐","📍","🐒","🏠","🏢","🏁","🎧","🎉","🎈","🎄","🎂",
				 "🍰","🎁","🍺","🍷","☕","❤️"
				]},
			initComponent : function(){
				emojiPanel.superclass.initComponent.call(this);
				this.addEvents('select');
				if(this.handler){
					 this.on('select', this.handler, this.scope, true);
				}    
			},
			onRender: function(container, position) {
				this.autoEl = {
					tag: 'div',
					cls: this.itemCls
				};
				emojiPanel.superclass.onRender.call(this, container, position);
				var t = this.tpl || new Ext.XTemplate(
					'<tpl for="people">'+
					'<tpl for="."><em>{.}</em></tpl>'+
					'</tpl>'
				);
				t.overwrite(this.el, this.emojis);
				this.mon(this.el, 'click', this.handleClick, this, {delegate: 'em'});
			},
			handleClick : function(e, t){
				e.preventDefault();
				if(!this.disabled){
					var emoji = t.innerText;
					this.fireEvent('select', this, emoji);
				}
			}
		});
		Ext.apply(this, {
			 plain: true,
			 showSeparator: false,
			 items: this.emojis = new emojiPanel()
		});
		this.emojis.purgeListeners();
		go.menu.EmojiMenu.superclass.initComponent.call(this);

		this.relayEvents(this.emojis, ['select']);
		this.on('select', this.menuHide, this);
		if(this.handler){
			 this.on('select', this.handler, this.scope || this);
		}
    },

    menuHide : function(){
        if(this.hideOnClick){
            this.hide(true);
        }
    }
});
Ext.reg('emojimenu', go.menu.EmojiMenu);
