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
            entity: null,
            repository: null
        };
    },

    methods: {
        onSave() {
            this.isLoading = true;

            this.repository.save(this.entity, Shopware.Context.api).then(() => {
                this.getEntity();
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

        getEntity() {
            let id = this.$route.params.id;
            if (id) {
                this.repository.get(id, Shopware.Context.api).then((entity) => {
                    this.entity = entity;
                });
            } else {
                this.entity = this.repository.create(Shopware.Context.api);
            }
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('sidworks_product_labels');
        this.getEntity();
    }
});
