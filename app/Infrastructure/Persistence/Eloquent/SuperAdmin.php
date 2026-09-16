<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['email', 'password_hash'])]
#[Hidden(['password_hash'])]
class SuperAdmin extends Authenticatable
{
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }
}
