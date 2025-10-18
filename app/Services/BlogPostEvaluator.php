<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlogPostEvaluator
{
    protected array $providers;
    protected array $questions;
    protected array $results = [];

    public function __construct()
    {
        $this->providers = config('blog-evaluator.providers', []);
        $this->questions = config('blog-evaluator.evaluation_questions', []);
    }

    /**
     * Evaluate blog post content using all enabled LLM providers
     *
     * @param string $content The blog post content to evaluate
     * @return array Aggregated results from all LLMs
     */
    public function evaluate(string $content): array
    {
        $this->results = [];
        $enabledProviders = $this->getEnabledProviders();

        if (empty($enabledProviders)) {
            throw new \Exception('No AI providers are enabled. Please configure at least one provider.');
        }

        foreach ($enabledProviders as $providerName => $providerConfig) {
            try {
                $this->results[$providerName] = $this->evaluateWithProvider($content, $providerName, $providerConfig);
            } catch (\Exception $e) {
                Log::error("Evaluation failed for {$providerName}: " . $e->getMessage());
                $this->results[$providerName] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $this->aggregateResults();
    }

    /**
     * Get only enabled providers
     */
    protected function getEnabledProviders(): array
    {
        return array_filter($this->providers, function ($config) {
            return ($config['enabled'] ?? false) && !empty($config['api_key']);
        });
    }

    /**
     * Evaluate content with a specific provider
     */
    protected function evaluateWithProvider(string $content, string $providerName, array $config): array
    {
        $prompt = $this->buildPrompt($content);
        
        switch ($providerName) {
            case 'openai':
                return $this->callOpenAI($prompt, $config);
            case 'anthropic':
                return $this->callAnthropic($prompt, $config);
            case 'google':
                return $this->callGoogle($prompt, $config);
            case 'cohere':
                return $this->callCohere($prompt, $config);
            case 'mistral':
                return $this->callMistral($prompt, $config);
            default:
                throw new \Exception("Unknown provider: {$providerName}");
        }
    }

    /**
     * Build evaluation prompt with questions
     */
    protected function buildPrompt(string $content): string
    {
        $questionsList = implode("\n", array_map(
            fn($i, $q) => ($i + 1) . ". {$q}",
            array_keys($this->questions),
            $this->questions
        ));

        return <<<PROMPT
You are an expert blog post evaluator. Please analyze the following blog post content and answer each of the 30 evaluation questions below. For each question, provide:
1. A score from 1-10 (where 10 is excellent)
2. A brief explanation
3. Specific recommendations for improvement if applicable

Blog Post Content:
---
{$content}
---

Evaluation Questions:
{$questionsList}

Please provide your response in JSON format with the following structure:
{
    "overall_score": <average score>,
    "evaluations": [
        {
            "question_number": 1,
            "question": "<question text>",
            "score": <1-10>,
            "explanation": "<your explanation>",
            "recommendations": ["<recommendation 1>", "<recommendation 2>"]
        },
        ...
    ],
    "summary": "<overall summary of the blog post quality>",
    "key_issues": ["<issue 1>", "<issue 2>", ...],
    "strengths": ["<strength 1>", "<strength 2>", ...]
}
PROMPT;
    }

    /**
     * Call OpenAI API
     */
    protected function callOpenAI(string $prompt, array $config): array
    {
        $response = Http::timeout(config('blog-evaluator.timeout', 60))
            ->withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ])
            ->post($config['endpoint'], [
                'model' => $config['model'],
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert blog post evaluator. Always respond in valid JSON format.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ]);

        if (!$response->successful()) {
            throw new \Exception("OpenAI API error: " . $response->body());
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';
        
        return $this->parseResponse($content);
    }

    /**
     * Call Anthropic API
     */
    protected function callAnthropic(string $prompt, array $config): array
    {
        $response = Http::timeout(config('blog-evaluator.timeout', 60))
            ->withHeaders([
                'x-api-key' => $config['api_key'],
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])
            ->post($config['endpoint'], [
                'model' => $config['model'],
                'max_tokens' => 4096,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception("Anthropic API error: " . $response->body());
        }

        $data = $response->json();
        $content = $data['content'][0]['text'] ?? '';
        
        return $this->parseResponse($content);
    }

    /**
     * Call Google Gemini API
     */
    protected function callGoogle(string $prompt, array $config): array
    {
        $endpoint = $config['endpoint'] . '/' . $config['model'] . ':generateContent';
        
        $response = Http::timeout(config('blog-evaluator.timeout', 60))
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint . '?key=' . $config['api_key'], [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception("Google API error: " . $response->body());
        }

        $data = $response->json();
        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        return $this->parseResponse($content);
    }

    /**
     * Call Cohere API
     */
    protected function callCohere(string $prompt, array $config): array
    {
        $response = Http::timeout(config('blog-evaluator.timeout', 60))
            ->withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ])
            ->post($config['endpoint'], [
                'model' => $config['model'],
                'message' => $prompt,
            ]);

        if (!$response->successful()) {
            throw new \Exception("Cohere API error: " . $response->body());
        }

        $data = $response->json();
        $content = $data['text'] ?? '';
        
        return $this->parseResponse($content);
    }

    /**
     * Call Mistral API
     */
    protected function callMistral(string $prompt, array $config): array
    {
        $response = Http::timeout(config('blog-evaluator.timeout', 60))
            ->withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ])
            ->post($config['endpoint'], [
                'model' => $config['model'],
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception("Mistral API error: " . $response->body());
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';
        
        return $this->parseResponse($content);
    }

    /**
     * Parse LLM response and extract JSON
     */
    protected function parseResponse(string $content): array
    {
        // Try to extract JSON from markdown code blocks if present
        $pattern = '/```json\s*(.*?)\s*```/s';
        if (preg_match($pattern, $content, $matches)) {
            $content = $matches[1];
        }

        $parsed = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to parse LLM response as JSON: " . json_last_error_msg());
        }

        return array_merge($parsed, ['success' => true]);
    }

    /**
     * Aggregate results from all LLMs
     */
    protected function aggregateResults(): array
    {
        $successful = array_filter($this->results, fn($r) => $r['success'] ?? false);
        
        if (empty($successful)) {
            return [
                'success' => false,
                'message' => 'All LLM evaluations failed',
                'individual_results' => $this->results,
            ];
        }

        $aggregated = [
            'success' => true,
            'providers_used' => count($successful),
            'total_providers' => count($this->results),
            'average_overall_score' => $this->calculateAverageScore($successful),
            'consensus_issues' => $this->findConsensusIssues($successful),
            'consensus_strengths' => $this->findConsensusStrengths($successful),
            'question_scores' => $this->aggregateQuestionScores($successful),
            'summary' => $this->generateConsensusSummary($successful),
            'individual_results' => $this->results,
        ];

        return $aggregated;
    }

    /**
     * Calculate average overall score
     */
    protected function calculateAverageScore(array $results): float
    {
        $scores = array_column($results, 'overall_score');
        return round(array_sum($scores) / count($scores), 2);
    }

    /**
     * Find issues mentioned by multiple LLMs
     */
    protected function findConsensusIssues(array $results): array
    {
        $allIssues = [];
        
        foreach ($results as $result) {
            if (isset($result['key_issues'])) {
                foreach ($result['key_issues'] as $issue) {
                    $allIssues[] = strtolower(trim($issue));
                }
            }
        }

        // Count occurrences
        $issueCounts = array_count_values($allIssues);
        
        // Return issues mentioned by at least 2 LLMs
        $threshold = min(2, count($results));
        return array_keys(array_filter($issueCounts, fn($count) => $count >= $threshold));
    }

    /**
     * Find strengths mentioned by multiple LLMs
     */
    protected function findConsensusStrengths(array $results): array
    {
        $allStrengths = [];
        
        foreach ($results as $result) {
            if (isset($result['strengths'])) {
                foreach ($result['strengths'] as $strength) {
                    $allStrengths[] = strtolower(trim($strength));
                }
            }
        }

        // Count occurrences
        $strengthCounts = array_count_values($allStrengths);
        
        // Return strengths mentioned by at least 2 LLMs
        $threshold = min(2, count($results));
        return array_keys(array_filter($strengthCounts, fn($count) => $count >= $threshold));
    }

    /**
     * Aggregate scores for each question across all LLMs
     */
    protected function aggregateQuestionScores(array $results): array
    {
        $questionScores = [];

        foreach ($this->questions as $index => $question) {
            $scores = [];
            $questionNumber = $index + 1;

            foreach ($results as $providerName => $result) {
                if (isset($result['evaluations'])) {
                    foreach ($result['evaluations'] as $evaluation) {
                        if ($evaluation['question_number'] == $questionNumber) {
                            $scores[] = [
                                'provider' => $providerName,
                                'score' => $evaluation['score'],
                                'explanation' => $evaluation['explanation'],
                            ];
                        }
                    }
                }
            }

            if (!empty($scores)) {
                $avgScore = array_sum(array_column($scores, 'score')) / count($scores);
                $questionScores[] = [
                    'question_number' => $questionNumber,
                    'question' => $question,
                    'average_score' => round($avgScore, 2),
                    'provider_scores' => $scores,
                ];
            }
        }

        return $questionScores;
    }

    /**
     * Generate consensus summary
     */
    protected function generateConsensusSummary(array $results): string
    {
        $summaries = array_column($results, 'summary');
        $providerCount = count($summaries);
        
        return "Based on evaluation by {$providerCount} AI providers: " . implode(' | ', $summaries);
    }
}
