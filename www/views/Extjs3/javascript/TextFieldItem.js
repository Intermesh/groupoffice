Ext.menu.SearchFieldItem = Ext.extend(Ext.menu.BaseItem, {

    hideOnClick: false,

    itemCls: "x-menu-textfield",

    constructor: function (config) {
        Ext.menu.SearchFieldItem.superclass.constructor.call(this, config);

        this.addEvents('keyup', 'keydown', 'keypress', 'clear');
    },

    // private
    onRender: function (container) {
        var me = this;

        Ext.menu.SearchFieldItem.superclass.onRender.apply(me, arguments);

        me.textFieldContainer = new Ext.Container({
            renderTo: me.id,
            layout: 'fit',
            items: [
                me.textField = new Ext.form.TriggerField({
                    cls: this.itemCls,
                    hideLabels: true,
                    enableKeyEvents: true,
                    placeholder: me.placeholder || t("Search") + "...",
                    triggerClass: 'x-form-clear-trigger',
                    onTriggerClick: function () {
                        me.textField.setValue("");
                        me.fireEvent('clear');
                    }
                }),
            ]
        });

        this.textFieldContainer.setWidth(this.parentMenu.ul.getWidth());
        this.container.addClass('x-menu-textfield-item');

        this.textField.on('keyup', function (field, e) {
            this.fireEvent('keyup', field, e);
        }, this);

        this.textField.on('keydown', function (field, e) {
            this.fireEvent('keydown', field, e);
        }, this);

        this.textField.on('keypress', function (field, e) {
            this.fireEvent('keypress', field, e);
        }, this);
    }
});