<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;

/**
 * Class ServiceRoutes
 * @package App\Models
 */
class ServiceRoutes extends Model
{
    protected $table = 'service_routes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'service_id',
        'tags',
        'summary',
        'type',
        'path',
        'params',
        'security',
        'produces',
        'scope',
    ];

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->params = json_encode($model->params);
            $model->uuid = (string)Uuid::generate(4);
        });
        self::retrieved(function ($model) {
            $model->params = json_decode($model->params, true);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service() {
        return $this->belongsTo(Services::class, 'service_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children() {
        return $this->hasMany(ServiceRoutes::class, 'parent_id', 'id');
    }
}
