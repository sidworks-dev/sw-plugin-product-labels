//import './page/sidworks-product-labels-create';
//import './page/sidworks-product-labels-detail';
import './page/sidworks-product-labels-list';
import enGB from './snippet/en-GB.json';

const { Module } = Shopware;

Module.register('sidworks-product-labels', {
    type: 'plugin',
    name: 'Sidworks Product Labels',
    title: 'sidworks-product-labels.general.pluginTitle',
    color: '#57D9A3',

    snippets: {
        'en-GB': enGB
    },

    routes: {
        list: {
            component: 'sidworks-product-labels-list',
            path: 'list'
        },
        /*detail: {
            component: 'sidworks-product-labels-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'sidworks.product.labels.list'
            }
        },
        create: {
            component: 'sidworks-product-labels-create',
            path: 'create',
            meta: {
                parentPath: 'sidworks.product.labels.list'
            }
        }*/
    },

    navigation: [
        {
            id: 'sidworks-product-labels-module',
            path: 'sidworks.product.labels.list',
            parent: 'sw-catalogue',
            label: 'sidworks-product-labels.general.pluginTitle',
            position: 10
        }
    ]
});
