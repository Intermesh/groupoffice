/* global go, Ext, dp, t, GO */

/**
 * Login window opened from RepositoryDialog for an EXISTING account. Collects
 * e-mail + password, calls the client MarketplaceRepository/login controller
 * method for the given repository URL, and on success hands the freshly issued
 * API token back to the opener via onLoggedIn(token) so it auto-fills the
 * repository dialog's token field (the server can't return the old token — it
 * only stores a hash — so login mints a new one).
 *
 * If the account isn't verified yet the server replies verifyRequired; we then
 * offer to re-send the verification e-mail (MarketplaceRepository/resendVerification).
 *
 * Utility dialog (go.Window) — not an entity CRUD dialog.
 */
go.modules.community.marketplace.LoginWindow = Ext.extend(go.Window, {
    title: t("Log in", "marketplace", "community"),
    width: dp(460),
    autoHeight: true,
    modal: true,

    // config: url (string), onLoggedIn (fn(token))
    url: null,
    onLoggedIn: Ext.emptyFn,

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
                    {xtype: 'textfield', name: 'email', fieldLabel: t("E-mail", "marketplace", "community"), vtype: 'email', allowBlank: false},
                    {xtype: 'textfield', name: 'password', inputType: 'password', fieldLabel: t("Password", "marketplace", "community"), allowBlank: false}
                ]
            }]
        });

        Ext.apply(me, {
            items: [me.formPanel],
            bbar: [
                {
                    text: t("Resend verification e-mail", "marketplace", "community"),
                    iconCls: 'ic-email',
                    handler: me.onResend,
                    scope: me
                },
                '->',
                {
                    cls: "primary",
                    text: t("Log in", "marketplace", "community"),
                    handler: me.onOk,
                    scope: me
                }
            ]
        });

        go.modules.community.marketplace.LoginWindow.superclass.initComponent.call(me);
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
            method: "MarketplaceRepository/login",
            params: {url: me.url, email: v.email, password: v.password},
            callback: function (options, success, response) {
                me.el.unmask();
                if (!success) {
                    // Generic, server-supplied message ("Invalid login credentials").
                    GO.errorDialog.show((response && response.message) || t("Invalid login credentials", "marketplace", "community"));
                    return;
                }
                if (response.verifyRequired) {
                    Ext.MessageBox.confirm(
                        t("Verify your e-mail", "marketplace", "community"),
                        t("Your account isn't verified yet. Resend the verification e-mail?", "marketplace", "community"),
                        function (btn) { if (btn === 'yes') { me.onResend(); } }
                    );
                    return;
                }
                if (response.token) {
                    me.onLoggedIn(response.token);
                }
                me.close();
            },
            scope: me
        });
    },

    /**
     * Re-send the verification e-mail for the entered address. Uniform outcome:
     * the server never reveals whether the account exists.
     *
     * @return {void}
     */
    onResend: function () {
        var me = this,
            email = me.formPanel.getForm().findField('email').getValue();

        if (!email) {
            GO.errorDialog.show(t("E-mail", "marketplace", "community"));
            return;
        }

        me.el.mask(t("Please wait...") || 'Please wait...');
        go.Jmap.request({
            method: "MarketplaceRepository/resendVerification",
            params: {url: me.url, email: email},
            callback: function (options, success, response) {
                me.el.unmask();
                if (!success) {
                    GO.errorDialog.show((response && response.message) || t("Could not send the e-mail.", "marketplace", "community"));
                    return;
                }
                Ext.MessageBox.alert(
                    t("Verify your e-mail", "marketplace", "community"),
                    t("If an account exists for that e-mail, a new verification link is on its way.", "marketplace", "community")
                );
            },
            scope: me
        });
    }
});
