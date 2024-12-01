<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Core\Content\ProductLabels;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class ProductLabelsEntity extends Entity
{
    public function getProductStreamId(): ?string
    {
        return $this->productStreamId;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}
