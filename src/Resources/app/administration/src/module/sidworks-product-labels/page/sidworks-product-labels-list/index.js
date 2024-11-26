import template from './sidworks-product-labels-list.html.twig';

const { Component } = Shopware;

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
    },

    created() {

    }
})
