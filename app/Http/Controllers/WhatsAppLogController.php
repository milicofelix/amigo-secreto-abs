<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AmigoSecreto\WhatsAppLog;

class WhatsAppLogController extends Controller
{
    public function index()
    {
        $logs = WhatsAppLog::orderByDesc('id')->paginate(20);

        return view('whatsapp.logs', compact('logs'));
    }
}
