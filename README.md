# Sidworks Product Labels for Shopware 6
This plugin adds custom product labels to the storefront of Shopware 6. You can attach multiple labels to multiple products by Product groups or custom product selection

## Images
![Image1](docs/s1.png)
![Image2](docs/s2.png)
![Image3](docs/s3.png)

## Installation
```bash
composer require sidworks/sw-plugin-product-labels
bin/console plugin:refresh
bin/console plugin:install --activate SidworksProductLabels
```

## Todo
- Add option to use {{ product.variable }} in content of label
- Make scheduler work
- Bulk delete in admin
