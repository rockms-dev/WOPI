<?php

namespace MS\Wopi\Facades;

use Illuminate\Support\Facades\Facade;
use MS\Wopi\Services\ProofValidator as WopiProofValidator;

/**
 * @method static bool isValid(\MS\Wopi\Support\ProofValidatorInput $proofValidatorInput)
 *
 * @see \MS\Wopi\Services\ProofValidator
 */
class ProofValidator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WopiProofValidator::class;
    }
}
