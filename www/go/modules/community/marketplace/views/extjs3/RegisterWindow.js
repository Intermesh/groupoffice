/* global go, Ext, dp, t, GO */

/**
 * Self-registration window opened from RepositoryDialog. Collects account
 * details, calls the server's register endpoint (via the client
 * MarketplaceRepository/register controller method) for the given repository
 * URL, and on success hands the returned API token back to the opener via
 * onRegistered(token) so it auto-fills the repository dialog's token field.
 *
 * Utility dialog (go.Window) — not an entity CRUD dialog.
 */
go.modules.community.marketplace.RegisterWindow = Ext.extend(go.Window, {
    title: t("Register account", "marketplace", "community"),
    width: dp(460),
    autoHeight: true,
    modal: true,

    // config: url (string), onRegistered (fn(token))
    url: null,
    onRegistered: Ext.emptyFn,

    initComponent: function () {
        var me = this;

        me.formPanel = new Ext.FormPanel({
            autoHeight: true,
            border: false,
            bodyStyle: 'padding:' + dp(10) + 'px',
            items: [{
                xtype: 'fieldset',
                defaults: {anchor: '100%'},
                items: [
                    new Ext.Button({
                        hidden: true, hideMode: "offsets", type: "submit",
                        handler: function () { me.onOk(); }
                    }),
                    {xtype: 'textfield', name: 'name', fieldLabel: t("Name", "marketplace", "community"), allowBlank: false},
                    {xtype: 'textfield', name: 'email', fieldLabel: t("E-mail", "marketplace", "community"), vtype: 'email', allowBlank: false},
                    {xtype: 'textfield', name: 'password', inputType: 'password', fieldLabel: t("Password", "marketplace", "community"), allowBlank: false, minLength: 10},
                    {xtype: 'textfield', name: 'companyName', fieldLabel: t("Company", "marketplace", "community")}
                ]
            }]
        });

        Ext.apply(me, {
            items: [me.formPanel],
            buttons: [
                '->',
                {
                    cls: "primary",
                    text: t("Register account", "marketplace", "community"),
                    handler: me.onOk,
                    scope: me
                }
            ]
        });

        go.modules.community.marketplace.RegisterWindow.superclass.initComponent.call(me);
    },

    onOk: function () {
        var me = this,
            form = me.formPanel.getForm();

        if (!form.isValid()) {
            return;
        }
        var v = form.getFieldValues();

        me.el.mask(t("Please wait...") || 'Please wait...');
        go.Jmap.request({
            method: "MarketplaceRepository/register",
            params: {
                url: me.url,
                email: v.email,
                name: v.name,
                password: v.password,
                companyName: v.companyName || ''
            },
            callback: function (options, success, response) {
                me.el.unmask();
                if (!success) {
                    GO.errorDialog.show((response && response.message) || t("Registration failed", "marketplace", "community"));
                    return;
                }
                // No token is returned by design (it would leak account
                // existence, and it's inert until verified anyway). The user
                // verifies their e-mail, then signs in — login issues the token
                // that fills the repository dialog.
                Ext.MessageBox.alert(
                    t("Register account", "marketplace", "community"),
                    t("Check your e-mail to verify your account, then sign in to connect this repository.", "marketplace", "community"),
                    function () { me.close(); }
                );
            },
            scope: me
        });
    }
});
