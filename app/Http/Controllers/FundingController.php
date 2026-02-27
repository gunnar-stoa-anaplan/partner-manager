<?php

namespace App\Http\Controllers;

class FundingController extends Controller
{
    public function development(): \Illuminate\View\View
    {
        return view('business-development.funding.development');
    }
}
