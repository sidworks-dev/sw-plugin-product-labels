import template from './sidworks-product-labels-list.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sidworks-product-labels-list', {
    template,

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            repository: null,
            repositoryItems: null
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        columns() {
            return [{
                property: 'name',
                dataIndex: 'name',
                label: this.$t('sidworks-product-labels.list.name'),
                routerLink: 'sidworks.product.labels.detail',
                allowResize: true,
                primary: true
            }, {
                property: 'active',
                label: this.$t('sidworks-product-labels.list.active'),
                inlineEdit: 'boolean',
                allowResize: true,
                align: 'center',
            }];
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('customer');

        this.repository
            .search(new Criteria(), Shopware.Context.api)
            .then((result) => {
                this.repositoryItems = result;
            });
    }
})
