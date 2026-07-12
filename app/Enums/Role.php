<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case TenantAdmin = 'tenant_admin';
    case Agent = 'agent';
    case Customer = 'customer';
}
