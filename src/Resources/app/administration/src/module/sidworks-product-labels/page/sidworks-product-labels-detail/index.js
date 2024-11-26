const { Component, Context } = Shopware;
import template from './sidworks-product-labels-detail.html.twig';

Component.register('sidworks-product-labels-detail', {
    template,

    inject: ['repositoryFactory', 'conditionDataProviderService'],

    data() {
        return {
            entity: null,
            repository: null,
            conditionContext: {},
        };
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent() {
            // Fetch your entity
            this.repository = this.repositoryFactory.create('customer');
            this.entity = await this.repository.get(this.$route.params.id, Context.api);

            // Setup the condition context (e.g., for your specific entity)
            this.conditionContext = {
                entity: this.entity,
                parentCondition: null
            };
        },
    },
});
