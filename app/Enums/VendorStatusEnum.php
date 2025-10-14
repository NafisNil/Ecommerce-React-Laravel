<?php

namespace App\Enums;

enum VendorStatusEnum : string
{
    case Approved = 'approved';
    case Pending = 'pending';
    case Suspended = 'suspended';

}
