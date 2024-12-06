import './acl';
import enGB from './snippet/en-GB.json';

const { Module } = Shopware;

Shopware.Component.register('sidworks-product-labels-list', () => import('./page/sidworks-product-labels-list'));
Shopware.Component.register('sidworks-product-labels-detail', () => import('./page/sidworks-product-labels-detail'));

Module.register('sidworks-product-labels', {
    type: 'plugin',
    name: 'Sidworks Product Labels',
    title: 'sidworks-product-labels.general.pluginTitle',
    color: '#57D9A3',

    snippets: {
        'en-GB': enGB
    },

    routes: {
        index: {
            components: {
                default: 'sidworks-product-labels-list',
            },
            path: 'index'
        },
        create: {
            component: 'sidworks-product-labels-detail',
            path: 'create',
            meta: {
                parentPath: 'sidworks.product.labels.index'
            },
        },
        detail: {
            component: 'sidworks-product-labels-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'sidworks.product.labels.index',
            },
            props: {
                default(route) {
                    return { labelId: route.params.id };
                },
            },
        },
    },

    navigation: [
        {
            id: 'sidworks-product-labels-module',
            path: 'sidworks.product.labels.index',
            parent: 'sw-catalogue',
            label: 'sidworks-product-labels.general.pluginTitle',
            position: 10
        }
    ]
});
