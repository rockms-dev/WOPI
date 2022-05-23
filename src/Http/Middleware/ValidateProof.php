<?php

namespace MS\Wopi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use MS\Wopi\Contracts\ConfigRepositoryInterface;
use MS\Wopi\Facades\ProofValidator;
use MS\Wopi\Support\ProofValidatorInput;

class ValidateProof
{
    public function handle(Request $request, Closure $next)
    {
        // Be carefull with database based config!
        $isproofValidationEnabled = app(ConfigRepositoryInterface::class)->getEnableProofValidation();

        if (! $isproofValidationEnabled) {
            return $next($request);
        }

        if (ProofValidator::isValid(ProofValidatorInput::fromRequest($request))) {
            return $next($request);
        }

        return abort(500);
    }
}
