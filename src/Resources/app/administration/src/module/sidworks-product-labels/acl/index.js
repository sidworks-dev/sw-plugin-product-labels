Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: null,
    key: 'sidworks_product_labnels',
    roles: {
        viewer: {
            privileges: ['sidworks_product_labnels:read'],
            dependencies: []
        },
        editor: {
            privileges: [
                'sidworks_product_labnels:update',
                'sidworks_product_labnels_sales_channel:create',
                'sidworks_product_labnels_sales_channel:delete'
            ],
            dependencies: []
        },
        creator: {
            privileges: [
                'sidworks_product_labnels:create',
                'sidworks_product_labnels_sales_channel:create',
                'sidworks_product_labnels_sales_channel:delete'
            ],
            dependencies: []
        },
        deleter: {
            privileges: ['sidworks_product_labnels:delete'],
            dependencies: []
        }
    }
});
