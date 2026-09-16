<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\SuperAdminFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['email', 'password_hash'])]
#[Hidden(['password_hash'])]
class SuperAdmin extends Authenticatable
{
    use HasFactory;

    protected static function newFactory(): SuperAdminFactory
    {
        return SuperAdminFactory::new();
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }
}
