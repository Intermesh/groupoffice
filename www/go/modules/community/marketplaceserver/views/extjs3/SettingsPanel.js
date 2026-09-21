/* global Ext, go, GO, dp, t */

/**
 * Marketplace server configuration — the package this marketplace serves and
 * the Group-Office version branches offered when publishing a release.
 * (The RS256 signing keypair is generated automatically on install.)
 *
 * Shown as a manager-only tab INSIDE the module (reachable from the module
 * menu), not in System Settings, so admins manage the catalog and its config
 * in one place. It still reuses go.systemsettings.Panel's load/save wiring
 * (afterRender -> loadSettings; onSubmit -> Module.set), driven by its own
 * Save button instead of the System Settings dialog.
 */
go.modules.community.marketplaceserver.SettingsPanel = Ext.extend(go.systemsettings.Panel, {
    // This panel is instantiated manually as an in-module tab (MainPanel), NOT
    // registered via `systemSettingsPanels`, so the framework does not inject
    // package/module for us — we set them, matching go.Modules.register(
    // "community", "marketplaceserver"). go.systemsettings.Panel resolves the
    // module via go.Modules.get(this.package, this.module) for its load/save
    // (loadSettings, onSubmit) and manage-permission check.
    package: 'community',
    module: 'marketplaceserver',
    title: t("Settings"),
    labelWidth: dp(180),
    autoScroll: true,
    bodyStyle: 'padding:8px',

    initComponent: function () {
        var me = this;

        // Whether the Stripe secrets are already stored (booleans exposed by the
        // Settings model — the secrets themselves are never sent to the browser),
        // so the password fields can show a "leave blank to keep" hint.
        var mod = go.Modules.get('community', 'marketplaceserver');
        me.stripeSecretConfigured = !!(mod && mod.settings && mod.settings.stripeSecretConfigured);
        me.stripeWebhookConfigured = !!(mod && mod.settings && mod.settings.stripeWebhookConfigured);

        // Gateway combo + the Stripe-only fields, kept as instances so the fields
        // can be shown only while Stripe is the selected gateway.
        me.gatewayCombo = new Ext.form.ComboBox({
            hiddenName: 'paymentGateway',
            fieldLabel: t("Payment gateway", "marketplaceserver", "community"),
            store: new Ext.data.ArrayStore({
                fields: ['id', 'label'],
                data: [['', t("None (no online purchasing)", "marketplaceserver", "community")], ['stripe', 'Stripe']]
            }),
            valueField: 'id',
            displayField: 'label',
            mode: 'local',
            triggerAction: 'all',
            editable: false,
            forceSelection: true,
            anchor: '100%',
            listeners: {
                select: function () { me.syncGatewayFields(); },
                scope: me
            }
        });

        me.stripeFieldsCt = new Ext.Container({
            layout: 'form',
            hidden: true,                 // shown only while Stripe is the selected gateway
            hideMode: 'offsets',
            labelWidth: dp(180),
            defaults: {anchor: '100%'},
            items: [
                {
                    xtype: 'textfield',
                    inputType: 'password',
                    name: 'stripeSecretKey',
                    autoComplete: false,
                    fieldLabel: t("Stripe secret key", "marketplaceserver", "community"),
                    emptyText: me.stripeSecretConfigured
                        ? t("Configured — leave blank to keep", "marketplaceserver", "community")
                        : 'sk_live_…'
                },
                {
                    xtype: 'textfield',
                    inputType: 'password',
                    name: 'stripeWebhookSecret',
                    autoComplete: false,
                    fieldLabel: t("Stripe webhook signing secret", "marketplaceserver", "community"),
                    emptyText: me.stripeWebhookConfigured
                        ? t("Configured — leave blank to keep", "marketplaceserver", "community")
                        : 'whsec_…'
                },
                {
                    xtype: 'box',
                    style: 'padding:4px 0 0',
                    html: '<small>' + Ext.util.Format.htmlEncode(
                        t("Set the Stripe webhook endpoint to this URL:", "marketplaceserver", "community")
                    ) + '<br><code>' + Ext.util.Format.htmlEncode(me.webhookUrl()) + '</code><br>' +
                        Ext.util.Format.htmlEncode(
                            t("Subscribe it to these events:", "marketplaceserver", "community")
                        ) + '<br><code>' + me.stripeEvents().map(function (ev) {
                            return Ext.util.Format.htmlEncode(ev);
                        }).join('<br>') + '</code></small>'
                }
            ]
        });

        me.tbar = [{
            text: t("Save"),
            cls: 'primary',
            iconCls: 'ic-save',
            handler: me.onSave,
            scope: me
        }];

        me.items = [{
            xtype: 'fieldset',
            defaults: {anchor: '100%'},
            items: [
                {
                    xtype: 'textfield',
                    name: 'packageName',
                    fieldLabel: t("Package", "marketplaceserver", "community")
                },
                me.branchChips = new go.modules.community.marketplaceserver.BranchChips({
                    fieldLabel: t("Supported Group-Office branches", "marketplaceserver", "community")
                }),
                {
                    xtype: 'box',
                    style: 'padding:4px 0 0',
                    html: '<small>' + Ext.util.Format.htmlEncode(
                        t("Comma-separated list, e.g. 6.8,25,26. A module release targets one branch.", "marketplaceserver", "community")
                    ) + '</small>'
                }
            ]
        }, {
            xtype: 'fieldset',
            title: t("Self-registration", "marketplaceserver", "community"),
            defaults: {anchor: '100%'},
            items: [
                {
                    xtype: 'xcheckbox',
                    name: 'registrationEnabled',
                    fieldLabel: t("Allow self-registration", "marketplaceserver", "community"),
                    hideLabel: false
                },
                {
                    xtype: 'box',
                    style: 'padding:4px 0 0',
                    html: '<small>' + Ext.util.Format.htmlEncode(
                        t("When enabled, users of the Group-Office marketplace client can create an account on this server.", "marketplaceserver", "community")
                    ) + '</small>'
                }
            ]
        }, {
            xtype: 'fieldset',
            title: t("Licensing", "marketplaceserver", "community"),
            defaults: {anchor: '100%'},
            items: [
                {
                    xtype: 'numberfield',
                    name: 'seatActivityDays',
                    allowDecimals: false,
                    minValue: 1,
                    maxValue: 30,
                    fieldLabel: t("Seat release after (days)", "marketplaceserver", "community")
                },
                {
                    xtype: 'box',
                    style: 'padding:4px 0 0',
                    html: '<small>' + Ext.util.Format.htmlEncode(
                        t("Days without a check-in after which an instance frees its seat, so staging/migration/failover release seats automatically. Kept short (1–30).", "marketplaceserver", "community")
                    ) + '</small>'
                }
            ]
        }, {
            xtype: 'fieldset',
            title: t("Payments", "marketplaceserver", "community"),
            defaults: {anchor: '100%'},
            items: [
                me.gatewayCombo,
                me.stripeFieldsCt
            ]
        }, {
            xtype: 'fieldset',
            title: t("Security", "marketplaceserver", "community"),
            defaults: {anchor: '100%'},
            items: [
                {
                    xtype: 'textfield',
                    name: 'trustedProxies',
                    fieldLabel: t("Trusted proxy IPs", "marketplaceserver", "community")
                },
                {
                    xtype: 'box',
                    style: 'padding:4px 0 0',
                    html: '<small>' + Ext.util.Format.htmlEncode(
                        t("Comma-separated reverse-proxy IPs allowed to set X-Forwarded-For. Leave empty unless this server is behind a proxy — otherwise the rate limiter uses the direct connection IP.", "marketplaceserver", "community")
                    ) + '</small>'
                }
            ]
        }];

        go.modules.community.marketplaceserver.SettingsPanel.superclass.initComponent.call(me);
    },

    /**
     * Show the Stripe credential fields only while Stripe is the selected gateway.
     *
     * @return {void}
     */
    syncGatewayFields: function () {
        if (!this.stripeFieldsCt || !this.gatewayCombo) {
            return;
        }
        this.stripeFieldsCt.setVisible(this.gatewayCombo.getValue() === 'stripe');
        if (this.rendered) {
            this.doLayout();
        }
    },

    /**
     * The public webhook endpoint of THIS install. Built from the configured
     * full_url (which includes any subdirectory the install is mounted under);
     * window.location.origin would drop that subpath and hand the admin a URL
     * that 404s.
     *
     * @return {String}
     */
    webhookUrl: function () {
        var base = (GO.settings && GO.settings.config && GO.settings.config.full_url)
            || (window.location.origin + '/');
        if (base.substr(-1) !== '/') {
            base += '/';
        }
        return base + 'api/page.php/community/marketplaceserver/paymentWebhook/stripe';
    },

    /**
     * Every Stripe event StripeGateway::mapEvent() acts on. Keep in sync with it —
     * an event the admin does not subscribe to is silently never delivered.
     *
     * @return {Array}
     */
    stripeEvents: function () {
        return [
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded',
            'charge.refunded',
            'charge.dispute.closed',
            'customer.subscription.deleted'
        ];
    },

    /**
     * After the base populates the form from stored settings, reveal the gateway
     * fields matching the loaded selection.
     *
     * @return {void}
     */
    loadSettings: function () {
        go.modules.community.marketplaceserver.SettingsPanel.superclass.loadSettings.apply(this, arguments);
        this.syncGatewayFields();
    },

    /**
     * Same as go.systemsettings.Panel#onSubmit, but a save the server refused
     * (a validation error, a setting pinned in config.php) is reported as a
     * failure. The base class passes `success` for any JMAP response, so the
     * panel said "Saved" and kept the rejected values.
     *
     * @param {Function} cb called with (panel, success)
     * @param {Object} scope
     * @return {void}
     */
    onSubmit: function (cb, scope) {
        var me = this,
            module = go.Modules.get(me.package, me.module),
            values = me.getForm().getFieldValues(true),
            p = {update: {}};

        if (Object.keys(values).length === 0) {
            setTimeout(function () { cb.call(scope, me, true); }, 0);
            return;
        }

        p.update[module.id] = {settings: values};
        go.Db.store("Module").set(p).then(function (response) {
            var failed = response.notUpdated && response.notUpdated[module.id];
            if (failed) {
                var errors = failed.validationErrors ? Object.values(failed.validationErrors).map(function (e) {
                    return e.description;
                }) : [];
                GO.errorDialog.show(errors.length ? errors.join("\n") : (failed.description || t("Error")));
                cb.call(scope, me, false);
                return;
            }
            cb.call(scope, me, true);
        }).catch(function (error) {
            GO.errorDialog.show(error);
            cb.call(scope, me, false);
        });
    },

    onSave: function () {
        var me = this;
        me.onSubmit(function (panel, success) {
            if (!success) {
                return;
            }
            // Keep the in-memory settings fresh so the Release dialog's branch
            // list (and any other consumer of module.settings) reflects the
            // change without a full page reload.
            var values = me.getForm().getFieldValues(),
                // A blank secret field means "keep the stored one" (the setters
                // return early on ''), so it must not be applied over anything —
                // and a filled one must not be cached in the browser at all: the
                // server never sends these back, only the *Configured booleans.
                storedSecret = !!values.stripeSecretKey,
                storedWebhook = !!values.stripeWebhookSecret,
                mod = go.Modules.get('community', 'marketplaceserver');
            delete values.stripeSecretKey;
            delete values.stripeWebhookSecret;
            if (mod && mod.settings) {
                Ext.apply(mod.settings, values);
                if (storedSecret) {
                    mod.settings.stripeSecretConfigured = true;
                }
                if (storedWebhook) {
                    mod.settings.stripeWebhookConfigured = true;
                }
            }
            me.afterSecretsSaved(storedSecret, storedWebhook);
            go.Notifier.msg({
                iconCls: 'ic-check',
                title: t("Marketplace server", "marketplaceserver", "community"),
                html: t("Saved", "marketplaceserver", "community"),
                removeAfter: 3000
            });
        }, me);
    },

    /**
     * Clear the secret inputs after a successful save and flip their hint to
     * "Configured", so the panel shows the same state a reload would: the value
     * is stored server-side, the browser is not holding on to it.
     *
     * @param {Boolean} storedSecret a new secret key was submitted
     * @param {Boolean} storedWebhook a new webhook signing secret was submitted
     * @return {void}
     */
    afterSecretsSaved: function (storedSecret, storedWebhook) {
        var me = this,
            configured = t("Configured — leave blank to keep", "marketplaceserver", "community");

        [['stripeSecretKey', storedSecret], ['stripeWebhookSecret', storedWebhook]].forEach(function (pair) {
            if (!pair[1]) {
                return;
            }
            var field = me.getForm().findField(pair[0]);
            if (field) {
                field.setValue('');
                field.emptyText = configured;
                if (field.rendered) {
                    field.applyEmptyText();
                }
            }
        });

        if (storedSecret) {
            me.stripeSecretConfigured = true;
        }
        if (storedWebhook) {
            me.stripeWebhookConfigured = true;
        }
    }
});
