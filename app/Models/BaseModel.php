<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    const CREATED_AT = 'dibuat_pada';

    const UPDATED_AT = 'diperbarui_pada';

    protected $guarded = [];

    public function getCreatedAtColumn()
    {
        return 'dibuat_pada';
    }

    public function getUpdatedAtColumn()
    {
        return 'diperbarui_pada';
    }
}
