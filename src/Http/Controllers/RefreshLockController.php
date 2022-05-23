<?php

namespace MS\Wopi\Http\Controllers;

use MS\Wopi\Contracts\WopiInterface;
use MS\Wopi\Http\Requests\WopiRequest;
use MS\Wopi\Support\RequestHelper;

class RefreshLockController extends WopiBaseController
{
    public function __invoke(WopiRequest $request, string $fileId, WopiInterface $wopiImplementation)
    {
        $accessToken = RequestHelper::parseAccessToken($request);

        return $wopiImplementation->refreshLock($fileId, $accessToken, $request);
    }
}
