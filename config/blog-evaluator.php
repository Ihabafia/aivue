<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI LLM Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the 5 different AI LLM providers used for blog post evaluation.
    | Each provider requires an API key set in your .env file.
    |
    */
    
    'providers' => [
        'openai' => [
            'enabled' => env('OPENAI_ENABLED', true),
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
        ],
        'anthropic' => [
            'enabled' => env('ANTHROPIC_ENABLED', true),
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-opus-20240229'),
            'endpoint' => 'https://api.anthropic.com/v1/messages',
        ],
        'google' => [
            'enabled' => env('GOOGLE_ENABLED', true),
            'api_key' => env('GOOGLE_API_KEY'),
            'model' => env('GOOGLE_MODEL', 'gemini-pro'),
            'endpoint' => 'https://generativelanguage.googleapis.com/v1/models',
        ],
        'cohere' => [
            'enabled' => env('COHERE_ENABLED', true),
            'api_key' => env('COHERE_API_KEY'),
            'model' => env('COHERE_MODEL', 'command'),
            'endpoint' => 'https://api.cohere.ai/v1/chat',
        ],
        'mistral' => [
            'enabled' => env('MISTRAL_ENABLED', true),
            'api_key' => env('MISTRAL_API_KEY'),
            'model' => env('MISTRAL_MODEL', 'mistral-large-latest'),
            'endpoint' => 'https://api.mistral.ai/v1/chat/completions',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Evaluation Questions
    |--------------------------------------------------------------------------
    |
    | 30 questions to evaluate blog post content for quality, issues, and improvements.
    |
    */
    
    'evaluation_questions' => [
        // Content Quality (10 questions)
        'Is the title clear, compelling, and accurately represents the content?',
        'Does the introduction effectively hook the reader and outline what to expect?',
        'Is the content well-structured with clear sections and logical flow?',
        'Are the main points well-supported with evidence, examples, or data?',
        'Is the conclusion effective in summarizing key points and providing closure?',
        'Is the writing style appropriate for the target audience?',
        'Are there any grammatical, spelling, or punctuation errors?',
        'Is the tone consistent throughout the article?',
        'Are technical terms and jargon explained adequately?',
        'Does the content provide value and actionable insights to readers?',

        // SEO & Readability (10 questions)
        'Are keywords naturally integrated without keyword stuffing?',
        'Are headings (H1, H2, H3) properly structured for SEO?',
        'Is the content length appropriate for the topic?',
        'Are sentences and paragraphs concise and easy to read?',
        'Is the Flesch Reading Ease score at an appropriate level?',
        'Are there sufficient internal and external links?',
        'Are images, if referenced, properly described with alt text?',
        'Is the meta description compelling and within character limits?',
        'Are there any broken or missing links?',
        'Does the content answer common user search queries?',

        // Accuracy & Credibility (10 questions)
        'Are all facts and statistics accurate and up-to-date?',
        'Are sources properly cited and credible?',
        'Is the information balanced and unbiased?',
        'Are there any misleading or false claims?',
        'Is the author\'s expertise or credentials mentioned?',
        'Are there any potential copyright or plagiarism issues?',
        'Are comparisons fair and well-researched?',
        'Is the information current and relevant?',
        'Are there any contradictions within the content?',
        'Does the content align with industry best practices and standards?',
    ],

    /*
    |--------------------------------------------------------------------------
    | Evaluation Settings
    |--------------------------------------------------------------------------
    */
    
    'timeout' => env('EVALUATION_TIMEOUT', 60), // seconds per LLM call
    'max_retries' => env('EVALUATION_MAX_RETRIES', 3),
    'parallel_requests' => env('EVALUATION_PARALLEL', true),
];
