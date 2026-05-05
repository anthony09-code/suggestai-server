<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    public function resolveRouteBinding($value, $field = null): ?static
    {
        return static::query()
            ->where($field ?? "id", strtolower($value))
            ->firstOrFail();
    }
}
