import template from './sidworks-product-labels-list.html.twig';

const { Criteria } = Shopware.Data;

export default {
    template,

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            labelRepository: null,
            labelRepositoryItems: null
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    methods: {
        getList() {
            this.labelRepository
                .search(new Criteria(), Shopware.Context.api)
                .then((result) => {
                    this.labelRepositoryItems = result;
                });
        },

        onChangeLanguage(languageId) {
            Shopware.State.commit('context/setApiLanguageId', languageId)
            this.getList();
        }
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
        this.labelRepository = this.repositoryFactory.create('sidworks_product_labels');
        this.getList();
    }
};
