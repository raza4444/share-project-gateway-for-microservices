<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Services
 * @package App\Models
 */
class Services extends Model
{
    protected $table = 'services';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hostname',
        'url',
        'throttling',
        'secure',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function routes() {
        return $this->hasMany(ServiceRoutes::class, 'service_id', 'id');
    }
}
