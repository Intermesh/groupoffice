go.Modules.register("community", "marketplace", {

    title: t("Marketplace", "marketplace", "community"),

    entities: [
        {name: "MarketplaceRepository"}
    ],

    systemSettingsPanels: [
        "go.modules.community.marketplace.SystemSettingsPanel"
    ]
});
