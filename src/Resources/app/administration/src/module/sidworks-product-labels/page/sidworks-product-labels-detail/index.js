import template from './sidworks-product-labels-detail.html.twig';

const {Mixin} = Shopware;

export default {
    template,

    inject: [
        'repositoryFactory',
        'acl',
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel',
    },

    props: {
        labelId: {
            type: String,
            required: false,
            default: null
        }
    },

    data() {
        return {
            isLoading: false,
            processSuccess: false,
            label: null,
            labelRepository: null
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

            return this.labelRepository
                .save(this.label, Shopware.Context.api)
                .then(() => {
                    if (!this.labelId) {
                        this.$router.push({
                            name: 'sidworks.product.labels.detail',
                            params: {
                                id: this.label.id
                            }
                        });
                    }
                    this.getLabel();
                    this.isLoading = false;
                    this.processSuccess = true;
                }).catch((exception) => {
                    this.isLoading = false;
                    if (this.label.name && this.label.name.length) {
                        this.createNotificationError({
                            title: 'Error',
                            message: exception
                        });
                    }
                });
        },

        onCancel() {
            this.$router.push({name: 'sidworks.product.labels.index'});
        },

        saveFinish() {
            this.processSuccess = false;
        },

        getLabel() {
            if (this.labelId || this.label?.id) {
                this.labelRepository
                    .get(this.labelId || this.label?.id, Shopware.Context.api)
                    .then((label) => {
                        this.label = label;
                    });
            } else {
                this.label = this.labelRepository.create(Shopware.Context.api);
            }
        },
    },

    created() {
        this.labelRepository = this.repositoryFactory.create('sidworks_product_labels');
        this.salesChannelRepository = this.repositoryFactory.create('sales_channel');

        this.getLabel();
    }
};
