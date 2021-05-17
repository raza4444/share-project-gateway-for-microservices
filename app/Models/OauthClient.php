<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OauthClient
 * @package App\Models
 */
class OauthClient extends Model
{
    protected $keyType = 'uuid';

    protected $table = 'oauth_clients';
}
