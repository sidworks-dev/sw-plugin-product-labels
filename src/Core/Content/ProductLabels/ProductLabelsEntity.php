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

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive($value)
    {
        return $this->active = $value;
    }

    public function getFromDateTime()
    {
        return $this->fromDateTime;
    }

    public function getToDateTime()
    {
        return $this->toDateTime;
    }

    public function getTextColor(): ?string
    {
        return $this->textColor;
    }
}
