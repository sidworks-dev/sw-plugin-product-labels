<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Core\Content\ProductLabels;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class ProductLabelsEntity extends Entity
{
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getProductStreamId(): ?string
    {
        return $this->productStreamId;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function getTextColor(): ?string
    {
        return $this->textColor;
    }
}
