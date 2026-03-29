<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class EmailVerification extends Model
{
    protected string $table = 'email_verifications';
}
