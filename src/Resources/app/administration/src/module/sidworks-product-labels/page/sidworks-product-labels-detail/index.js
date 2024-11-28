const {Component, Mixin} = Shopware;
import template from './sidworks-product-labels-detail.html.twig';

Component.register('sidworks-product-labels-detail', {
    template,

    inject: [
        'repositoryFactory'
    ],
    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
            processSuccess: false,
            item: null,
            repository: null
        };
    },

    methods: {
        onSave() {
            this.isLoading = true;

            this.repository.save(this.item, Shopware.Context.api).then(() => {
                this.getItem();
                this.isLoading = false;
                this.processSuccess = true;
            }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: exception.toString(),
                    autoClose: true,
                });
            });
        },

        saveFinish() {
            this.processSuccess = false;
        },

        getItem() {
            let id = this.$route.params.id;
            if (id) {
                this.repository.get(id, Shopware.Context.api).then((entity) => {
                    this.item = entity;
                });
            } else {
                this.item = this.repository.create(Shopware.Context.api);
            }
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('sidworks_product_labels');
        this.getItem();
    }
});
