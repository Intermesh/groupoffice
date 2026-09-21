go.Modules.register("community", "marketplaceserver", {
    title: t("Marketplace server", "marketplaceserver", "community"),
    iconCls: 'ic-store',

    initModule: function () {
        this.addPanel(go.modules.community.marketplaceserver.MainPanel);
    },

    entities: [
        {name: "MarketplaceServerProduct"},
        {
            name: "MarketplaceServerRelease",
            relations: {
                product: {store: "MarketplaceServerProduct", fk: "productId"}
            }
        },
        {
            name: "MarketplaceServerCustomer",
            relations: {user: {store: "User", fk: "userId"}}
        },
        {
            name: "MarketplaceServerApiToken",
            relations: {customer: {store: "MarketplaceServerCustomer", fk: "customerId"}}
        },
        {
            name: "MarketplaceServerEntitlement",
            relations: {
                customer: {store: "MarketplaceServerCustomer", fk: "customerId"},
                product: {store: "MarketplaceServerProduct", fk: "productId"}
            }
        },
        {
            name: "MarketplaceServerActivity",
            relations: {
                customer: {store: "MarketplaceServerCustomer", fk: "customerId"},
                product: {store: "MarketplaceServerProduct", fk: "productId"}
            }
        }
    ]
});
