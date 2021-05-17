<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\APIUsers;
use App\Models\UserRole;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:migrate';

    /**
     * @var integer
     */
    private $defaultDataLimit = 500;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * each Role Values
     * @var array
     */
    
     private $role = [
        "superadmin" => 1,
        "admin" => 1,
        "agent" => 2,
        "pro" => 3,
        "owner" => 1,
        "customer" => 4,
        "seo" => 6

    ];

    private $userIds = [];
    private $userEmails = [];
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->userIds = User::pluck('id')->toArray();
        $this->userEmails = User::pluck('email')->toArray();
    
        $this->migrate('customer');
        $this->migrate('pro');
        $this->migrate('agent');
        $this->migrate('owner');
        $this->migrate('seo');
        $this->migrate('admin');
        $this->migrate('superadmin');
    }

    /**
     * used to migrate normalized data from API USER to USER table
     * @param string $type
     * @return void
     */
    private function migrate(string $type)
    {
        $customerChunksCount = $this->createChunksArray(APIUsers::where('type', '=', $type)->count());

        foreach ($customerChunksCount  as $skipCount) {
            sleep(2);
            $skipCount = "";
            $data =  APIUsers::where('type', '=', $type)->skip($skipCount)->take($this->defaultDataLimit)->get()->toArray();
            if ($data && count($data) > 0) {
                $this->createBulkInsert($data, $type);
            }
        }
    }

    /**
     * create chunks array
     *
     * @param int $totalCount
     * @return array
     */
    private function createChunksArray(int $totalCount)
    {
        $array = [];
        $array[] = 0;
        while (true) {
            if (($array[count($array) - 1] + $this->defaultDataLimit) >= $totalCount) {
                break;
            } else {
                $array[] = $array[count($array) - 1] + $this->defaultDataLimit;
            }
        }
        return $array;
    }

    /**
     * create bulk insert of user
     *
     * @param array $apiUsers
     * @param string $type
     * @return void
     */
    private function createBulkInsert(array $apiUsers = [], string $type)
    {

        $payload = array(
            'type' => $type,
            'user_ids' =>   $this->userIds,
            'date' => Carbon::parse(Carbon::now())->format('Y-m-d H:i:s'),
            'user_emails' => $this->userEmails 
        );

        $users =  collect($apiUsers)->map(function ($user) use ($payload) {
            if ($user['id'] !== 0 && !in_array($user['id'], $payload['user_ids']) &&  !in_array($user['email'], $payload['user_emails'])) {
                return array(
                    'id' => $user['id'],
                    'name' => $user['name'],
                    "email" => $user['email'],
                    "password" => $user['password'],
                    "phone_number" => $user['telephone'],
                    "email_verified_at" => $payload['date'],
                    "uuid" => $user['uuid'],
                    "email_verification_token" => rand(100000, 9999999),
                    "created_at" => $payload['date'],
                    "userRole" => array("user_id" => $user['id'], "role_id" => $this->role[$payload['type']])
                );
            }
        });
        $users = $users->filter();

        $roles =  collect($users)->map(function ($user) {
            return $user['userRole'];
        });
        $userData = [];
        if ($roles && count($roles) > 0) {
            $userData = collect($users)->transform(function ($user) {
                unset($user['userRole']);
                return $user;
            });
        }
        $userData = $userData ? $userData->toArray() : [];
        $roles  = $roles ? $roles->toArray() : [];
        try {
            if (count($userData) > 0) {
                User::insert($userData);
            }
            if (count($roles) > 0) {
                UserRole::insert($roles);
            }
        } catch (\Exception $e) {
            abort($e->getMessage(), 422);
        }
    }
}
