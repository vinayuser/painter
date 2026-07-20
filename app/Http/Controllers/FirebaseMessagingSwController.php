<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class FirebaseMessagingSwController extends Controller
{
    public function __invoke(): Response
    {
        $js = view('firebase-messaging-sw', [
            'apiKey' => config('services.firebase.web_api_key'),
            'authDomain' => config('services.firebase.web_auth_domain'),
            'projectId' => config('services.firebase.web_project_id'),
            'messagingSenderId' => config('services.firebase.web_messaging_sender_id'),
            'appId' => config('services.firebase.web_app_id'),
        ])->render();

        return response($js, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Service-Worker-Allowed' => '/',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
