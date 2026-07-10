import template from './sidworks-product-labels-detail.html.twig';

const { Mixin } = Shopware;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();
const { Criteria } = Shopware.Data;

export default {
    template,

    inject: [
        'repositoryFactory',
        'context',
        'acl',
    ],

    mixins: [
        Mixin.getByName('placeholder'),
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

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier),
        };
    },

    data() {
        return {
            isLoading: false,
            processSuccess: false,
            label: null,
            labelRepository: null,
            salesChannelRepository: null,
            productRepository: null,
            selectedProductIds: []
        };
    },

    computed: {
        identifier() {
            return this.placeholder(this.label, 'name');
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
        },

        ...mapPropertyErrors('label', [
            'name',
            'salesChannelIds',
            'content',
            'link',
            'linkNewTab',
            'productStreamId',
            'backgroundColor',
            'textColor'
        ]),

        productCriteria() {
            const criteria = new Criteria(1, 25);
            return criteria;
        },
    },

    methods: {
        getLabel() {
            const criteria = new Criteria();

            if (this.labelId || this.label?.id) {
                this.labelRepository
                    .get(this.labelId || this.label?.id, Shopware.Context.api, criteria)
                    .then((label) => {
                        this.label = label;

                        if (!Array.isArray(this.label.selectedProducts)) {
                            this.label.selectedProducts = [];
                        }

                        if (this.label.linkNewTab === null || this.label.linkNewTab === undefined) {
                            this.label.linkNewTab = false;
                        }
                    });
            } else {
                this.label = this.labelRepository.create(Shopware.Context.api);
                this.label.linkNewTab = false;
            }
        },

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
                }).catch(() => {
                    this.isLoading = false;
                    this.showErrorNotification();
                });
        },

        onCancel() {
            this.$router.push({ name: 'sidworks.product.labels.index' });
        },

        saveFinish() {
            this.processSuccess = false;
        },

        onChangeLanguage(languageId) {
            Shopware.State.commit('context/setApiLanguageId', languageId);
            this.getLabel();
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        abortOnLanguageChange() {
            this.getLabel();
            this.isLoading = false;
            this.processSuccess = false;
        },

        showErrorNotification() {
            this.createNotificationError({
                message: this.$tc('global.notification.notificationSaveErrorMessageRequiredFieldsInvalid'),
            });
        }
    },

    created() {
        this.labelRepository = this.repositoryFactory.create('sidworks_product_labels');
        this.salesChannelRepository = this.repositoryFactory.create('sales_channel');
        this.productRepository = this.repositoryFactory.create('product');

        this.getLabel();
    }
};
