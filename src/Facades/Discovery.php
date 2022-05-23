<?php

namespace MS\Wopi\Facades;

use Illuminate\Support\Facades\Facade;
use MS\Wopi\Services\Discovery as WopiDiscovery;

/**
 * @method static \SimpleXMLElement discover(string $rawXmlString)
 * @method static null|array discoverAction(string $extension, string $name = 'view')
 * @method static array discoverExtension(string $extension)
 * @method static array discoverMimeType(string $mimeType)
 * @method static string getCapabilitiesUrl()
 * @method static string getPublicKey()
 * @method static string getOldPublicKey()
 * @method static string getProofExponent()
 * @method static string getOldProofExponent()
 * @method static string getProofModulus()
 * @method static string getOldProofModulus()
 *
 * @see \MS\Wopi\Services\Discovery
 */
class Discovery extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WopiDiscovery::class;
    }
}
