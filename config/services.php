<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'analytics' => [
        'url'     => env('ANALYTICS_API_URL', 'http://127.0.0.1:8001'),
        'key'     => env('ANALYTICS_API_KEY'),
        'timeout' => env('ANALYTICS_API_TIMEOUT', 10),
    ],

    // Dipakai GeminiService untuk tool "web_search" custom (lihat
    // TavilySearchService + docblock GeminiService kenapa ini perlu).
    // Daftar gratis di https://tavily.com untuk dapat API key (1.000
    // kredit/bulan, tanpa kartu kredit).
    'tavily' => [
        'key'     => env('TAVILY_API_KEY'),
        'timeout' => env('TAVILY_API_TIMEOUT', 10),
    ],

    'anthropic' => [
        'key'   => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-5'),
    ],

    // Fallback pertama kalau Claude gagal (lihat GroqService + ChatController).
    // Dipakai lewat endpoint OpenAI-compatible Groq, jadi format tool calling-nya
    // mengikuti spesifikasi OpenAI, bukan Anthropic/Google.
    // Model default GPT-OSS (bukan llama-3.3-70b-versatile lagi) karena tool
    // bawaan Groq "browser_search" (web_search) cuma didukung di model GPT-OSS
    // -- lihat docblock GroqService.
    'groq' => [
        'key'   => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
    ],

    // Fallback terakhir kalau Claude & Groq berdua gagal.
    'gemini' => [
        'key'   => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ],

];
