import template from './sidworks-product-labels-detail.html.twig';

const {Mixin} = Shopware;

export default {
    template,

    inject: [
        'repositoryFactory'
    ],
    mixins: [
        Mixin.getByName('notification')
    ],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel',
    },

    data() {
        return {
            isLoading: false,
            processSuccess: false,
            entity: null,
            repository: null
        };
    },

    computed: {
        identifier() {
            return this.placeholder('HIER', 'name');
        },

        tooltipSave() {
            const systemKey = this.$device.getSystemKey();

            return {
                message: `${systemKey} + S`,
                appearance: 'light',
            };
        },

        tooltipCancel() {
            return {
                message: 'ESC',
                appearance: 'light',
            };
        }
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

        onCancel() {
            this.$router.push({ name: 'sidworks.product.labels.index' });
        },

        saveFinish() {
            this.processSuccess = false;
        },

        getEntity() {
            let id = this.$route.params.id;
            if (id) {
                this.repository
                    .get(this.$route.params.id, Shopware.Context.api)
                    .then((entity) => {
                        this.entity = entity;
                        this.entityId = entity.id;
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
};
