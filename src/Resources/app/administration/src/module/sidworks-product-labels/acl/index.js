Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: null,
    key: 'sidworks_product_labels',
    roles: {
        viewer: {
            privileges: ['sidworks_product_labels:read'],
            dependencies: []
        },
        editor: {
            privileges: [
                'sidworks_product_labels:update',
                'sidworks_product_labels_sales_channel:create',
                'sidworks_product_labels_sales_channel:delete'
            ],
            dependencies: []
        },
        creator: {
            privileges: [
                'sidworks_product_labels:create',
                'sidworks_product_labels_sales_channel:create',
                'sidworks_product_labels_sales_channel:delete'
            ],
            dependencies: []
        },
        deleter: {
            privileges: ['sidworks_product_labels:delete'],
            dependencies: []
        }
    }
});
