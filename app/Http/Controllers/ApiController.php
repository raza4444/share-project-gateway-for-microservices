<?php

namespace App\Http\Controllers;

use App\Http\Middleware\VerifyAppId;
use App\Services\AppClientsService;
use App\Traits\ApiResponser;

/**
 * Class ApiController
 * @package App\Http\Controllers
 */
class ApiController extends Controller
{
    use ApiResponser;

    public $appClient;

    public function __construct()
    {
        $this->appClient = (new AppClientsService(request()->header(VerifyAppId::$APP_ID)))->getClient();
    }
}
