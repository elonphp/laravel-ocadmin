<?php

namespace App\Enums;

enum OrganizationIdentity: string
{
    case Dealer = 'dealer';
    case Customer = 'customer';
    case Supplier = 'supplier';

    public function label(): string
    {
        return __('enums.organization_identity.' . $this->value);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
