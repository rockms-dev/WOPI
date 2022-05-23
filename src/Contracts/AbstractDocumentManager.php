<?php

namespace MS\Wopi\Contracts;

use Closure;
use Exception;
use Illuminate\Support\Str;
use MS\Wopi\Contracts\Traits\SupportLocks;
use MS\Wopi\Facades\Discovery;

abstract class AbstractDocumentManager
{
    use SupportLocks;

    /**
     * No properties should be set to null. If you do not wish
     * to set a property, simply omit it from the response
     * and WOPI clients will use the default value.
     */
    protected static array $propertyMethodMapping = [
        // Required proprties
        'BaseFileName' => 'basename',
        'OwnerId' => 'owner',
        'Size' => 'size',
        'Version' => 'version',
        'UserId' => 'userId',
        'UserFriendlyName' => 'userFriendlyName',

        // Permission properties
        'ReadOnly' => 'isReadOnly',
        'UserCanNotWriteRelative' => 'userCanNotWriteRelative',
        'UserCanRename' => 'canUserRename',
        'UserCanWrite' => 'canUserWrite',

        // File URl proprties
        'CloseUrl' => 'closeUrl',
        'DownloadUrl' => 'downloadUrl',
        'FileVersionUrl' => 'getFileVersionUrl',

        // Sharable
        'FileSharingUrl' => 'sharingUrl',
        'SupportedShareUrlTypes' => 'supportedShareUrlTypes',

        // Override getting file content url
        'FileUrl' => 'getFileContentUrl',

        // Override getting file extension logic
        'FileExtension' => 'extension',

        // Meta data
        'LastModifiedTime' => 'lastModifiedTime',

        // hash
        'SHA256' => 'sha256Hash',

        // Disable Printing
        'DisablePrint' => 'disablePrint',
        'HidePrintOption' => 'hidePrintOption',

        // Disable Exporing
        'DisableExport' => 'disableExport',
        'HideExportOption' => 'hideExportOption',

        // Disable copy
        'DisableCopy' => 'disableCopy',

        // Interacts with user info
        'UserInfo' => 'getUserInfo',
        'SupportsUserInfo' => 'supportUserInfo',

        // Override supported features
        'SupportsDeleteFile' => 'supportDelete',
        'SupportsLocks' => 'supportLocks',
        'SupportsGetLock' => 'supportGetLock',
        'SupportsUpdate' => 'supportUpdate',
        'SupportsRename' => 'supportRename',
        'SupportsExtendedLockLength' => 'supportExtendedLockLength',

    ];

    /**
     * Resloved User Id.
     *
     * @var string|Closure
     */
    protected $userId = '';

    /**
     * Preform look up for the file/document.
     *
     * @param string $fileId unique ID, Represent a single file and URL safe.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    abstract public static function find(string $fileId): self;

    /**
     * Preform look up for the file/document by filename.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    abstract public static function findByName(string $filename): self;

    /**
     * Create new document instace on the host.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    abstract public static function create(array $properties): self;

    /**
     * Unique id that identifies single file could be numbers
     * or string, but also should be url safe. It should
     * match fileId parameter passed to static::find.
     */
    abstract public function id(): string;

    /**
     * Friendly name for current edting user.
     */
    abstract public function userFriendlyName(): string;

    public function supportUpdate(): bool
    {
        /** @var ConfigRepositoryInterface */
        $config = app(ConfigRepositoryInterface::class);

        return $config->supportUpdate();
    }

    public function supportRename(): bool
    {
        /** @var ConfigRepositoryInterface */
        $config = app(ConfigRepositoryInterface::class);

        return $config->supportRename();
    }

    public function supportDelete(): bool
    {
        /** @var ConfigRepositoryInterface */
        $config = app(ConfigRepositoryInterface::class);

        return $config->supportDelete();
    }

    /**
     * Name of the file, including extension, without a path. Used
     * for display in user interface (UI), and determining
     * and  determining the extension of the file.
     */
    abstract public function basename(): string;

    /**
     * Uniquely identifies the owner of the file. In most
     * cases, the user who uploaded or created the file
     * should be considered the owner.
     */
    abstract public function owner(): string;

    /**
     * The size of the file in bytes, expressed
     * as a long, a 64-bit signed integer.
     */
    abstract public function size(): int;

    /**
     * The current version of the file based on the server’s file
     * version schema, as a string. This value must change when
     * the file changes, and version values must never repeat.
     */
    abstract public function version(): string;

    /**
     * Binary contents of the file. Not the url!
     */
    abstract public function content(): string;

    /**
     * Determin if the document is locked or not.
     */
    abstract public function isLocked(): bool;

    /**
     * Get current lock on the document.
     */
    abstract public function getLock(): string;

    /**
     * Change document contents.
     */
    abstract public function put(string $content, array $editorsIds = []): void;

    /**
     * Delete the lock on the document.
     */
    abstract public function deleteLock(): void;

    /**
     * Lock the document prevent it from being altered or deleted.
     */
    abstract public function lock(string $lockId): void;

    /**
     * Manually set user id.
     */
    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Value uniquely identifying the user currently accessing the
     * file. Can be set to the current logged user ideally.
     */
    public function userId(): string
    {
        $defaultUserId = $this->defaultUser();

        if ($this->userId instanceof Closure) {
            $userId = call_user_func($this->userId, $this);

            return empty($userId) ? $defaultUserId : $userId;
        }

        return (string) empty($this->userId) ? $defaultUserId : $this->userId;
    }

    /**
     * When there's no user id this value will be used.
     */
    protected function defaultUser(): string
    {
        /** @var ConfigRepositoryInterface */
        $config = app(ConfigRepositoryInterface::class);

        return $config->getDefaultUser();
    }

    /**
     * Indicates that the user has permission to alter the
     * file. Setting this to true tells the WOPI client
     * that it can call PutFile on behalf of the user.
     *
     * @default-value false
     */
    public function canUserWrite(): bool
    {
        return true;
    }

    /**
     * Manually set user id using closure.
     */
    public function getUserUsing(Closure $calback): self
    {
        $this->userId = $calback;

        return $this;
    }

    /**
     * Convenient method for getUrlForAction.
     */
    public function generateUrl(string $lang = 'en-Us'): string
    {
        return $this->getUrlForAction('edit', $lang);
    }

    public function getUrlForAction(string $action, string $lang = 'en-US'): string
    {
        /** @var ConfigRepositoryInterface */
        $config = app(ConfigRepositoryInterface::class);

        $lang = empty($lang) ? $config->getDefaultUiLang() : $lang;

        $extension = method_exists($this, 'extension')
            ? Str::replaceFirst('.', '', $this->extension())
            : pathinfo($this->basename(), PATHINFO_EXTENSION);

        $url = route('wopi.checkFileInfo', [
            'file_id' => $this->id(),
        ]);

        
        //$url = "https://word-edit.officeapps.live.com/we/wordeditorframe.aspx?ui=en-us&rs=en-us";
        //return $url;

        $url = "https://FFC-onenote.officeapps.live.com/hosting/WopiTestFrame.aspx?ui=en-us&rs=en-us&hid=EwCIA8l6BAAUwihrrCrmQ4wuIJX5mbj7rQla6TUAAdjBQjNSI7kYE7P6l0i7ihppwYAO2tkIs/frEM4yCTikspbDKlbCcjiaPo2Q+MZNttpjhvnKzsGbEfkr9FPs+JJewwihLbOFXxSUuGum7mRxMxA9GJmx9oHkGW3y7S6cl0z7De/9M27R3+7m8QEqIPCEnscJuRFA4O5k455VwSzmBMIkE1HDQd965i0NeDtU/UMNcpMU79XugW02Tz0O+k0nWtt7WcbdM6Npo6FHDA9Sl6dNjEpuPmGy1jl2G/ylbaG+sMhpX6BRGY4Mh49YjaOEn4zpFGoxVWlR3z/VuZ+XjEl14g6yssncXxFcubK9irhS6/QcFUvWZfXmBaL9TAEDZgAACCns/LPMMIKYWAIUJr+NeLdXvr3GBG83xCNF288pn5WNdIGryZx2KuUmEFmCyeEyF/bukQEpSdQpyFr1ATpZVBPRLts+ceWVfW+kttu25ob0kIH7dsKsgDHB+VzZg/HBrXaIZg/GoKZBsMjtcsnZBuMCi4m9QQmshfo2rCtH16m29pTLrZbJARYBMpA+EzshC8se2YRRRYENDsZsgXkt1TEY6wjI7+HBmFzVoI8/sB4K6d1mzrf8Q3pxWEPQ01ZooLuL7LOd4sYj6mzKJWTEwSAN8eDxEK4jRfbqYIjMzccZVeB+XNRfvcat9cIHxMYGAoCHr4Kp5Ys9TYFm4p6UFakuSbIL7TTMbkvC+ShiOUIF9Nm0baRZ3fC5UzdktkA2gwm9W9HMYsFHPNpDQcaxcvHR+yObfgID0KU+SrATn2q4Y8Kg32kT9Vum7s9FkA/0qZWyALPfmh+gfiidUEiuquhwCW9qgkkvdvBPYHLyggT6VotRTndyffPmVj6LNrdNp00eTwbsojK3pmqvcPHZDYumJ0UxpdWL/fEgRuN50mVgFfp7PGl4Z5ex9AFA+k4weEVjURg3tE7u4zUZahaBIxGzYRW/jvAa1AaNJyJq8OymxP18cJUgmdez32EY7L0vP1wy4QdcrNq2F46MbhZE1HmiQn3A0qxpJsYOM/RPSkDZFXXZIvMfnNTGGjx0SK2ZjbT79kcnfTfXyHLakOD9jAYF38DQAU/+H7nPiV83u34S4w2FYwChWxkpinMrCEiGB98CphrpPBgBvLQ6ih+jWFD7OCdWw8fMMETSJ+ix7gDgo1CXAg==&wopisrc=oresoft-dev.dev/wopi/files/61f957411da49923161af92a ";

        // todo handle microsoft office 365 <> placeholders
        //return 'https://FFC-onenote.officeapps.live.com/hosting/WopiTestFrame.aspx?<ui=UI_LLCC&><rs=DC_LLCC&><dchat=DISABLE_CHAT&><hid=HOST_SESSION_ID&><sc=SESSION_CONTEXT&><wopisrc=WOPI_SOURCE&><IsLicensedUser=BUSINESS_USER&><testcategory=VALIDATOR_TEST_CATEGORY>';

        $actionUrl = optional(Discovery::discoverAction($extension, $action));

        // if (is_null($actionUrl['urlsrc'])) {
        //     throw new Exception("Unsupported action \"{$action}\" for \"{$extension}\" extension.");
        // }

        return "{$actionUrl['urlsrc']}lang={$lang}&WOPISrc={$url}";
    }

    /**
     * Get CheckfileInfo response proprites based
     * on implemented interfaces/features.
     */
    public function getResponseProprties(): array
    {
        return  collect(static::$propertyMethodMapping)
                ->flatMap(function (string $methodName, string $propertyName) {
                    if (method_exists($this, $methodName)) {
                        return [
                             $propertyName => $this->$methodName(),
                        ];
                    }
                })
                ->filter(fn ($value) => $value !== null)
                ->toArray();
    }
}
