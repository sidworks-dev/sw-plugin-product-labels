import template from './sidworks-product-labels-detail.html.twig';

const { Mixin } = Shopware;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

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
            labelRepository: null
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
            'productStreamId',
            'content',
            'backgroundColor',
            'textColor'
        ])
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
                    this.showErrorNotification();
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
        onChangeLanguage(languageId) {
            Shopware.State.commit('context/setApiLanguageId', languageId)
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
        },
    },

    created() {
        this.labelRepository = this.repositoryFactory.create('sidworks_product_labels');
        this.salesChannelRepository = this.repositoryFactory.create('sales_channel');

        this.getLabel();
    }
};
