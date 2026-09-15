/* global Ext, go, dp, t */

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
                        t("Set the Stripe webhook endpoint to this URL, subscribing to checkout.session.completed and charge.refunded:", "marketplaceserver", "community")
                    ) + '<br><code>' + Ext.util.Format.htmlEncode(
                        window.location.origin + '/api/page.php/community/marketplaceserver/paymentWebhook/stripe'
                    ) + '</code></small>'
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
     * After the base populates the form from stored settings, reveal the gateway
     * fields matching the loaded selection.
     *
     * @return {void}
     */
    loadSettings: function () {
        go.modules.community.marketplaceserver.SettingsPanel.superclass.loadSettings.apply(this, arguments);
        this.syncGatewayFields();
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
            var mod = go.Modules.get('community', 'marketplaceserver');
            if (mod && mod.settings) {
                Ext.apply(mod.settings, me.getForm().getFieldValues());
            }
            go.Notifier.msg({
                iconCls: 'ic-check',
                title: t("Marketplace server", "marketplaceserver", "community"),
                html: t("Saved", "marketplaceserver", "community"),
                removeAfter: 3000
            });
        }, me);
    }
});
