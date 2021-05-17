<?php
    /**
     * Created by PhpStorm.
     * User: markushartman
     * Date: 15-11-17
     * Time: 11:10
     */

    namespace App\Services\Mail\Mandrill\CentraalBeheer;

    use App\Services\Mail\Mandrill\MandrillMailApi;

    /**
     * Class MandrillMessage
     * @package App\Services\Mandrill\Mail
     */
    class MandrillMessage
    {
        private $service        = null;
        private $merge_language = 'mailchimp';

        public function useMailChimp(){
            $this->merge_language = 'mailchimp';
            return $this;
        }

        public function useHandleBars(){
            $this->merge_language = 'handlebars';
            return $this;
        }

        public function __construct(MandrillMailApi $service)
        {
            $this->service = $service;
        }

        public function get(){

            $service = $this->service;

            return ['html'                => '<p>Example HTML content</p>',
                    'text'                => 'Example text content',
                    'subject'             => $service->subject,
                    'from_email'          => config('mail.from.address'),
                    'from_name'           => 'Centraal Beheer',
                    'to'                  => [['email' => $service->to_mail,
                                               'name'  => $service->to_name,
                                               'type'  => 'to'
                                              ]
                    ],
                    'headers'             => ['Reply-To' => 'klushulp@centraalbeheer.nl'],
                    'important'           => false,
                    'track_opens'         => true,
                    'track_clicks'        => true,
                    'auto_text'           => null,
                    'auto_html'           => null,
                    'inline_css'          => null,
                    'url_strip_qs'        => null,
                    'preserve_recipients' => null,
                    'view_content_link'   => null,
                    'bcc_address'         => null,
                    'tracking_domain'     => null,
                    'signing_domain'      => null,
                    'return_path_domain'  => null,
                    'merge'               => true,
                    'merge_language'      => $this->merge_language,
                    'merge_vars'          => [['rcpt' => $service->to_mail,
                                               'vars' => $service->merge_vars
                                              ]
                    ],
                    'global_merge_vars'   => $service->global_merge_vars,
                    //                    'tags'                      => ['password-resets'],
                    //                    'subaccount'                => 'customer-123',
                    //                    'google_analytics_domains'  => ['example.com'],
                    //                    'google_analytics_campaign' => 'message.from_email@example.com',
                    //                    'metadata'                  => ['website' => 'www.example.com'],
                    //                    'recipient_metadata'        => [['rcpt'   => $service->to_mail,
                    //                                                     'values' => ['user_id' => 123456]
                    //                                                    ]
                    //                    ],
                    //                    'attachments'               => [['type'    => 'text/plain',
                    //                                                     'name'    => 'myfile.txt',
                    //                                                     'content' => 'ZXhhbXBsZSBmaWxl'
                    //                                                    ]
                    //                    ],
                    //                    'images'                    => [['type'    => 'image/png',
                    //                                                     'name'    => 'IMAGECID',
                    //                                                     'content' => 'ZXhhbXBsZSBmaWxl'
                    //                                                    ]
                    //                    ]
            ];
        }
    }