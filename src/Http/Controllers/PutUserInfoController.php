<?php

namespace MS\Wopi\Http\Controllers;

use MS\Wopi\Contracts\WopiInterface;
use MS\Wopi\Http\Requests\WopiRequest;
use MS\Wopi\Support\RequestHelper;

class PutUserInfoController extends WopiBaseController
{
    public function __invoke(WopiRequest $request, string $fileId, WopiInterface $wopiImplementation)
    {
        $accessToken = RequestHelper::parseAccessToken($request);

        return $wopiImplementation->putUserInfo($fileId, $accessToken, $request);
    }
}
