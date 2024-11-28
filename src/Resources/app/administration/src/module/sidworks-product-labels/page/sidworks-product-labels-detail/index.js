const {Component, Mixin} = Shopware;
import template from './sidworks-product-labels-detail.html.twig';

Component.register('sidworks-product-labels-detail', {
    template,

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            item: null,
            repository: null
        };
    },

    methods: {
        onSave() {
            this.repository.save(this.item, Shopware.Context.api);
        },
        getItem() {
            this.repository.get(this.$route.params.id, Shopware.Context.api).then((entity) => {
                this.item = entity;
            });
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('sidworks_product_labels');
        this.getItem();
    },
});
