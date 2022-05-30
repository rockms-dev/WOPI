<?php

return [
     /*
     * Managing documents differs a lot between apps, because of this reason
     * this configration left empty to be implemented by the user There's
     * plans to implement example storage manager in the future though.
     */
    'document_manager' => MS\Wopi\Services\DBDocumentManager::class,

     /*
     * Default UI langauge.
     */
    'ui_language' => 'en-US',
    /*
     * Here, you can customize how would you like to retrive
     * all of the diffrent configration options.
     */
    'config_repository' => MS\Wopi\Services\DefaultConfigRepository::class,

    /*
     * This package comes with a convenient implementation of the
     * wopi spec you can build your own and swap it form here.
     */
    'wopi_implementation' => MS\Wopi\Wopi::class,

    /*
     * This request get injected into every request, and currently does
     * not have any validation logic. It's a great place to implement
     * custom validation for the access_token and access_token_ttl.
     */
    'wopi_request' =>  MS\Wopi\Http\Requests\WopiRequest::class,

    /*
     * Here's you can define your middleware pipeline that every
     * request from the wopi client will go through.
     */
    'middleware' => [MS\Wopi\Http\Middleware\ValidateProof::class],

     /*
     * Collabora or Microsoft Office 365 or any WOPI client url.
     */
    'client_url' => env('WOPI_CLIENT_URL', 'https://onenote.officeapps.live.com/hosting/discovery'),

    /*
     * Collabora or Microsoft Office 365 or any WOPI client url.
     */
    'server_url' => env('WOPI_SERVER_URL', ''),

   /*
    * Tells the WOPI client when an access token expires, represented as
    * a timestamp. It's not a duration rather than a date of expiry.
    */
   'access_token_ttl' => env('WOPI_ACCESS_TOKEN_TTL', 0),

   /*
    * Every request will be approved using RSA keys.
    * It's not recommended to disable it.
    */
   'enable_proof_validation' => false,

   /*
    * Enable/disable support for deleting documents.
    * default: false
    */
   'support_delete' => true,

   /*
    * Enable/disable support for renaming documents.
    * default: false
    */
   'support_rename' => true,

   /*
    * Default user name string that will appear in case
    * no user passed to the client.
    */
   'default_user' => 'Unknown User',

   /*
    * Enable/disable support for updating documents.
    * default: true
    */
   'support_update' => true,

   /*
    * Enable/disable support locking functionality,
    * thought you have to implement lock functions.
    *
    * default: false
    */
   'support_locks' => true,

   /*
    * Enable/disable support for GetLock operation.
    *
    * default: false
    */
   'support_get_locks' => true,

    /*
    * Enable/disable support for lock IDs up to 1024 ASCII characters
    * long. If disabled WOPI clients will assume that lock IDs
    * are limited to 256 ASCII characters.
    *
    * default: false
    */
   'support_extended_lock_length' => true,

       /*
    * Enable/disable support for storing basic information
    * about the user and enable PutUserInfo operation.
    *
    * @default false
    */
   'support_user_info' => true,
];
