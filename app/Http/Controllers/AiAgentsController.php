<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AiAgentsController extends Controller
{
    public function index(): View
    {
        return view('admin.ai-agents.index');
    }
}
