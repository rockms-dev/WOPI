<?php

namespace MS\Wopi\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WopiController extends Controller
{
    public function __invoke(Request $request)
    {
        echo "MS Wopi";
    }
}
