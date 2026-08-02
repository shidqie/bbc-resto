<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    const CREATED_AT = 'dibuat_pada';

    const UPDATED_AT = 'diperbarui_pada';
}
