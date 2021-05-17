<?php

namespace App\Services\Mail\Mandrill;

use App\Traits\ServiceActions;
use Illuminate\Support\Collection;

/**
 * Class MandrillMailApi
 * @package App\Services\Mail\Mandrill
 */
class MandrillMailApi
{
    use ServiceActions;

    public $merge_vars = [];
    public $global_merge_vars = [];
    public $content = [];
    public $subject;
    public $to_mail;
    public $to_name;

    /**
     * @var null
     */
    private $api_key = null;

    /**
     * MandrillMailApi constructor.
     */
    public function __construct()
    {
        $this->api_key = (env('MANDRILL_APIKEY'));
    }

    /**
     * @param string $attribute
     * @param string $value
     *
     * @return $this
     */
    public function setAttribute(string $attribute, string $value)
    {
        $this->{$attribute} = $value;

        return $this;
    }

    /**
     * @param $notifiable
     *
     * @return $this
     */
    public function addMergeVars($notifiable)
    {
        $this->merge_vars = $this->getVariables($notifiable);

        return $this;
    }

    /**
     * @return array
     */
    private function getVariables($notifiable)
    {
        $collection = new Collection;
        $hidden = $notifiable->getHidden();
        $attributes = $notifiable->getAttributes();
        $intersect = collect($attributes)->except($hidden);

        foreach ($intersect as $attribute => $value) {
            $collection->push(
                ['name' => $attribute,
                    'content' => $value
                ]);
        }

        return $collection->all();
    }

    /**
     * @param $notifiable
     *
     * @return \Illuminate\Support\Collection
     */
    private function getVariablesArray($notifiable)
    {
        $content = new Collection;
        $hidden = $notifiable->getHidden();
        $attributes = $notifiable->getAttributes();
        $nonintersect = collect($attributes)->except($hidden);

        foreach ($nonintersect as $attribute => $value) {
            $content[$attribute] = $value;
        }

        return $content;
    }

    /**
     * @param array $collection
     *
     * @return $this
     */
    public function addGlobalMergeVarData($collection = [])
    {
        foreach ($collection as $index => $item) {
            $this->global_merge_vars[] = [
                'name' => key($item),
                'content' => $item[key($item)]
            ];
        };

        return $this;
    }

    /**
     * @param $notifiable
     *
     * @return $this
     */
    public function addGlobalMergeVars($notifiable, $header = null)
    {
        if (is_null($header)) {
            $this->global_merge_vars[] = $this->getVariables($notifiable);
        } else {
            $this->global_merge_vars[] = [
                'name' => $header,
                'content' => $this->getVariablesArray($notifiable)->all()
            ];
        }

        return $this;
    }

    /**
     * @param array $collection
     * @param       $name
     *
     * @return $this
     */
    public function addGlobalMergeArray($collection = [], $name)
    {
        $this->global_merge_vars[] = [
            'name' => $name,
            'content' => $collection
        ];

        return $this;
    }

    /**
     * @param $notifiable
     *
     * @return $this
     */
    public function addContent($notifiable)
    {
        $this->notifiable = $notifiable;
        $this->content = $this->getVariables($notifiable);

        return $this;
    }
}
