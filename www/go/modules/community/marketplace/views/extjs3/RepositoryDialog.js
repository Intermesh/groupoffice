/* global go, Ext, dp, t, GO */

/**
 * Add/edit a marketplace Repository.
 *
 * `token` is write-only (Repository::setToken() no-ops on an empty string,
 * see model/Repository.php) — it is never populated on load/edit, blank means
 * "keep the stored token". `publicKey` is a hidden field that round-trips
 * with the entity; it is never hand-edited, only ever set from the server's
 * `validate` response. `keyMismatch` is a hidden (visually) xcheckbox so a
 * successful re-validate can clear it on save — NOT an Ext.form.Hidden, which
 * would round-trip a boolean as the DOM string "false" (truthy in PHP).
 *
 * Pre-save validate round trip: go.form.Dialog's onBeforeSubmit() hook is called
 * SYNCHRONOUSLY right before submit() checks its return value, so it cannot await
 * an async go.Jmap.request(). Instead this dialog overrides the Save button's
 * handler (initButtons() with Ext.applyIf): it calls the custom
 * `MarketplaceRepository/validate` method FIRST, and only calls the base
 * `this.submit()` once validate() resolves successfully and has populated the
 * name/publicKey (and cleared keyMismatch) form fields. This is required:
 *
 *   - On CREATE: there is nothing to save yet without the server confirming
 *     the URL/token are reachable+valid and handing back the repository's
 *     name + pinned public key.
 *   - On EDIT, only when the user typed a new token (rotating credentials,
 *     or re-confirming after a keyMismatch signing-key rotation — the ONLY
 *     way the client can prove a signing key change is legitimate is by
 *     asking the operator to re-enter a working API token, since the stored
 *     token is encrypted + never sent to the browser).
 *
 * Plain edits (name/url tweaks, no new token) submit directly — no need to
 * re-validate a connection that already works.
 */
go.modules.community.marketplace.RepositoryDialog = Ext.extend(go.form.Dialog, {
    entityStore: "MarketplaceRepository",
    title: t("Repository", "marketplace", "community"),
    titleField: 'name',
    redirectOnSave: false, // settings entity — no detail route to navigate to
    width: dp(800),
    height: dp(500),

    initFormItems: function () {
        var me = this;

        return [{
            xtype: 'fieldset',
            defaults: {anchor: '100%'},
            items: [
                me.keyMismatchBox = new Ext.BoxComponent({
                    hidden: true,
                    style: 'padding: 0 0 10px 0',
                    html: '<div style="color: var(--hue-orange);">' +
                        Ext.util.Format.htmlEncode(t("The repository's signing key changed. Re-confirm it to resume syncing.", "marketplace", "community")) +
                        ' ' + Ext.util.Format.htmlEncode(t("Enter the API token again and save to re-confirm the new key.", "marketplace", "community")) +
                        '</div>'
                }),
                {
                    xtype: 'textfield',
                    name: 'name',
                    fieldLabel: t("Name", "marketplace", "community"),
                    readOnly: true,
                    emptyText: t("Determined automatically when validated", "marketplace", "community")
                },
                {
                    xtype: 'textfield',
                    name: 'url',
                    fieldLabel: t("Repository URL", "marketplace", "community"),
                    allowBlank: false
                },
                me.tokenField = new Ext.form.TextField({
                    name: 'token',
                    inputType: 'password',
                    fieldLabel: t("API token", "marketplace", "community"),
                    emptyText: t("Leave blank to keep unchanged", "marketplace", "community"),
                    allowBlank: true
                }),
                new Ext.form.Hidden({name: 'publicKey'}),
                new Ext.form.Hidden({name: 'package'}),
                {
                    // NOTE: a boolean is carried here as an xcheckbox, NOT an
                    // Ext.form.Hidden — Hidden fields round-trip through a DOM
                    // text value, so setValue(false) would submit the STRING
                    // "false", which PHP treats as truthy. xcheckbox is GO's
                    // established boolean field (see ProductDialog.js's
                    // `active`); it just stays hidden from the user.
                    xtype: 'xcheckbox',
                    name: 'keyMismatch',
                    hidden: true,
                    checked: false
                }
            ]
        }];
    },

    initButtons: function () {
        Ext.applyIf(this, {
            buttons: [
                this.registerButton = new Ext.Button({
                    text: t("Register account", "marketplace", "community"),
                    iconCls: 'ic-person-add',
                    handler: this.onRegisterClick,
                    scope: this
                }),
                this.loginButton = new Ext.Button({
                    text: t("Log in", "marketplace", "community"),
                    iconCls: 'ic-lock-open',
                    handler: this.onLoginClick,
                    scope: this
                }),
                '->',
                this.saveButton = new Ext.Button({
                    cls: "primary",
                    text: t("Save"),
                    handler: this.onSaveClick,
                    scope: this
                })
            ]
        });
    },

    /**
     * Open the self-registration window for the entered repository URL. On
     * success it writes the returned API token straight into this dialog's token
     * field (the "opener" auto-fill), so the operator never copy-pastes a token.
     *
     * @return {void}
     */
    onRegisterClick: function () {
        var me = this,
            url = me.formPanel.getForm().findField('url').getValue();

        if (!url) {
            GO.errorDialog.show(t("Repository URL", "marketplace", "community"));
            return;
        }

        new go.modules.community.marketplace.RegisterWindow({
            url: url,
            onRegistered: function (token) {
                if (token) {
                    me.tokenField.setValue(token);
                }
            }
        }).show();
    },

    /**
     * Open the login window for the entered repository URL. On success it writes
     * the freshly issued API token into this dialog's token field (same
     * "opener" auto-fill as registration).
     *
     * @return {void}
     */
    onLoginClick: function () {
        var me = this,
            url = me.formPanel.getForm().findField('url').getValue();

        if (!url) {
            GO.errorDialog.show(t("Repository URL", "marketplace", "community"));
            return;
        }

        new go.modules.community.marketplace.LoginWindow({
            url: url,
            onLoggedIn: function (token) {
                if (token) {
                    me.tokenField.setValue(token);
                }
            }
        }).show();
    },

    onLoad: function (entityValues) {
        go.modules.community.marketplace.RepositoryDialog.superclass.onLoad.call(this, entityValues);

        if (this.keyMismatchBox) {
            this.keyMismatchBox.setVisible(!!entityValues.keyMismatch);
        }
    },

    /**
     * Save button handler. Runs the validate() round trip first when needed
     * (see class docblock), otherwise submits directly.
     *
     * @return {void}
     */
    onSaveClick: function () {
        var me = this,
            isNew = !me.currentId,
            token = me.tokenField.getValue();

        if (!me.isValid()) {
            me.showFirstInvalidField();
            return;
        }

        if (isNew || token) {
            me.doValidateThenSubmit();
            return;
        }

        me.doSubmit();
    },

    doSubmit: function () {
        this.submit().catch(function (error) {
            GO.errorDialog.show(error);
        });
    },

    /**
     * Call the custom validate() controller method with the current url/token
     * form values; on success populate name/publicKey/keyMismatch from the
     * response and proceed to the normal entity submit.
     *
     * @return {void}
     */
    doValidateThenSubmit: function () {
        var me = this,
            url = me.formPanel.getForm().findField('url').getValue(),
            token = me.tokenField.getValue();

        me.actionStart();
        go.Jmap.request({
            method: "MarketplaceRepository/validate",
            params: {url: url, token: token},
            callback: function (options, success, response) {
                me.actionComplete();

                if (!success) {
                    GO.errorDialog.show((response && response.message) || t("Invalid token or unreachable repository.", "marketplace", "community"));
                    return;
                }
                if (!response.writable) {
                    // Non-blocking: writability is only needed to AUTO-INSTALL
                    // downloads. Adding the repository, browsing the catalog and
                    // managing licences all work regardless — so warn and continue
                    // rather than refusing the save.
                    go.Notifier.flyout({
                        title: t("Repository", "marketplace", "community"),
                        description: t("Added, but this server can't auto-install modules: its go/modules folder isn't writable by the web server. Grant write access or install downloads manually.", "marketplace", "community"),
                        time: 8000
                    });
                }

                me.formPanel.getForm().findField('name').setValue(response.name);
                me.formPanel.getForm().findField('publicKey').setValue(response.publicKey);
                me.formPanel.getForm().findField('package').setValue(response.package);
                me.formPanel.getForm().findField('keyMismatch').setValue(false);
                if (me.keyMismatchBox) {
                    me.keyMismatchBox.hide();
                }

                me.doSubmit();
            },
            scope: me
        });
    }
});
