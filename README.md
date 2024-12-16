# Sidworks Product Labels for Shopware 6
This plugin adds custom product labels to the storefront of Shopware 6

## Installation
```bash
composer require sidworks/sw-plugin-product-labels
bin/console plugin:refresh
bin/console plugin:install --activate SidworksProductLabels
```

## Todo
### Administration
- Determine label position in administration
- Add ACL
- Use snippets
- Make changing empty label language work

### Storefront
- When label has position, make sure to position them correctly
- Add option to use {{ product.variable }} in content of label
- Add enable on product page, category
