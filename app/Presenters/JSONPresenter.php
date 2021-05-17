<?php

namespace App\Presenters;

use App\Exceptions\DataFormatException;
use Illuminate\Http\Response;

/**
 * Class JSONPresenter
 * @package App\Presenters
 */
class JSONPresenter implements PresenterContract
{
    /**
     * @param $input
     * @return array
     */
    public static function safeDecode($input) {
        // Fix for PHP's issue with empty objects
        $input = preg_replace('/{\s*}/', "{\"EMPTY_OBJECT\":true}", $input);

        return json_decode($input, true);
    }

    /**
     * @param array|object $input
     * @return string
     */
    public static function safeEncode($input) {
        return preg_replace('/{"EMPTY_OBJECT"\s*:\s*true}/', '{}', json_encode($input, JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param array|string $input
     * @param $code
     * @return Response
     * @throws DataFormatException
     */
    public function format($input, $code)
    {
//        dd($input);
        if (empty($input) && ! is_array($input)) return new Response(null, $code);

        $serialized = is_array($input) ? $this->formatArray($input) : $this->formatString($input);

        return new Response($serialized, $code, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param $input
     * @return string
     * @throws DataFormatException
     */
    private function formatString($input)
    {
        if (!$this->isJson($input)){
            var_dump($input); die;
        }
        $decoded = self::safeDecode($input);
        if ($decoded === null) throw new DataFormatException('Unable to decode input');
        return $decoded;
//        return $this->formatArray($decoded);
    }

    private function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * @param array|mixed $input
     * @return string
     */
    private function formatArray($input)
    {
        $output = [];

        if (is_array($input) && isset($input['error']) && is_string($input['error'])) {
            $output['errors'] = [ $input['error'] ];
            unset($input['error']);
        }

        if (is_array($input) && isset($input['errors']) && is_array($input['errors'])) {
            $output['errors'] = $input['errors'];
            unset($input['errors']);
        }

        if (is_array($input) && isset($input['links']) && is_array($input['links'])) {
            unset($input['links']);
        }

        if (is_array($input) && isset($input['meta']) && is_array($input['meta'])) {
            unset($input['meta']['path']);
        }

        $output = $input;

        return self::safeEncode($output);
    }
}
