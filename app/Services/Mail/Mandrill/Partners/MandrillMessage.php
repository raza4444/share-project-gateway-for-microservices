<?php
    /**
     * Created by PhpStorm.
     * User: markushartman
     * Date: 15-11-17
     * Time: 11:10
     */

    namespace App\Services\Mail\Mandrill\Partners;

    use App\Partner;
    use App\Services\Mail\Mandrill\MandrillMailApi;

    /**
     * Class MandrillMessage
     * @package App\Services\Mandrill\Mail
     */
    class MandrillMessage
    {
        private $service        = null;
        private $merge_language = 'mailchimp';
        private $partner        = null;

        public function __construct(MandrillMailApi $service, Partner $partner)
        {
            $this->service = $service;
            $this->partner = $partner;
        }

        public function useMailChimp()
        {
            $this->merge_language = 'mailchimp';

            return $this;
        }

        public function useHandleBars()
        {
            $this->merge_language = 'handlebars';

            return $this;
        }

        public function get()
        {
            $service = $this->service;
            $partner = $this->partner;

            return [
                'html'                => '<p>Example HTML content</p>',
                'text'                => 'Example text content',
                'subject'             => $service->subject,
                'from_email'          => $partner->email_from,
                'from_name'           => $partner->name,
                'to'                  => [
                    [
                        'email' => $service->to_mail,
                        'name'  => $service->to_name,
                        'type'  => 'to'
                    ]
                ],
                'headers'             => ['Reply-To' => $partner->email_reply_to],
                'important'           => false,
                'track_opens'         => true,
                'track_clicks'        => true,
                'auto_text'           => null,
                'auto_html'           => null,
                'inline_css'          => null,
                'url_strip_qs'        => null,
                'preserve_recipients' => null,
                'view_content_link'   => null,
                'bcc_address'         => $partner->mail_bcc,
                'tracking_domain'     => null,
                'signing_domain'      => null,
                'return_path_domain'  => null,
                'merge'               => true,
                'merge_language'      => $this->merge_language,
                'merge_vars'          => [
                    [
                        'rcpt' => $service->to_mail,
                        'vars' => $service->merge_vars
                    ]
                ],
                'global_merge_vars'   => $service->global_merge_vars
            ];
        }
    }