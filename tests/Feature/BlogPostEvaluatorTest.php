<?php

namespace Tests\Feature;

use App\Services\BlogPostEvaluator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BlogPostEvaluatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock configuration
        Config::set('blog-evaluator.providers', [
            'openai' => [
                'enabled' => true,
                'api_key' => 'test-key',
                'model' => 'gpt-4',
                'endpoint' => 'https://api.openai.com/v1/chat/completions',
            ],
        ]);
        
        Config::set('blog-evaluator.evaluation_questions', [
            'Is the title clear?',
            'Does the introduction hook the reader?',
        ]);
    }

    /** @test */
    public function it_can_evaluate_blog_post_content()
    {
        // Mock HTTP response
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'overall_score' => 8.5,
                                'evaluations' => [
                                    [
                                        'question_number' => 1,
                                        'question' => 'Is the title clear?',
                                        'score' => 9,
                                        'explanation' => 'The title is clear and concise.',
                                        'recommendations' => ['Consider adding keywords'],
                                    ],
                                    [
                                        'question_number' => 2,
                                        'question' => 'Does the introduction hook the reader?',
                                        'score' => 8,
                                        'explanation' => 'Good hook with room for improvement.',
                                        'recommendations' => ['Add a compelling statistic'],
                                    ],
                                ],
                                'summary' => 'Well-written blog post with minor improvements needed.',
                                'key_issues' => ['Missing call to action'],
                                'strengths' => ['Clear structure', 'Good examples'],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $evaluator = new BlogPostEvaluator();
        $content = 'This is a test blog post content that is more than 100 characters long to meet the minimum requirement for evaluation.';
        
        $results = $evaluator->evaluate($content);

        $this->assertTrue($results['success']);
        $this->assertEquals(1, $results['providers_used']);
        $this->assertEquals(8.5, $results['average_overall_score']);
        $this->assertArrayHasKey('question_scores', $results);
        $this->assertArrayHasKey('consensus_issues', $results);
        $this->assertArrayHasKey('consensus_strengths', $results);
    }

    /** @test */
    public function it_throws_exception_when_no_providers_enabled()
    {
        Config::set('blog-evaluator.providers', []);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No AI providers are enabled');

        $evaluator = new BlogPostEvaluator();
        $evaluator->evaluate('Test content');
    }

    /** @test */
    public function it_handles_provider_failures_gracefully()
    {
        // Mock failing HTTP response
        Http::fake([
            'api.openai.com/*' => Http::response('Error', 500),
        ]);

        $evaluator = new BlogPostEvaluator();
        $content = 'This is a test blog post content that is more than 100 characters long to meet the minimum requirement.';
        
        $results = $evaluator->evaluate($content);

        $this->assertFalse($results['success']);
        $this->assertEquals('All LLM evaluations failed', $results['message']);
    }

    /** @test */
    public function it_validates_minimum_content_length()
    {
        $response = $this->postJson('/api/blog-evaluator/evaluate', [
            'content' => 'Short',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content']);
    }

    /** @test */
    public function it_accepts_optional_title()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'overall_score' => 8.5,
                                'evaluations' => [],
                                'summary' => 'Test summary',
                                'key_issues' => [],
                                'strengths' => [],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/blog-evaluator/evaluate', [
            'title' => 'Test Blog Post',
            'content' => 'This is a test blog post content that is more than 100 characters long to meet the minimum requirement for evaluation.',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_aggregates_scores_from_multiple_providers()
    {
        Config::set('blog-evaluator.providers', [
            'openai' => [
                'enabled' => true,
                'api_key' => 'test-key-1',
                'model' => 'gpt-4',
                'endpoint' => 'https://api.openai.com/v1/chat/completions',
            ],
            'anthropic' => [
                'enabled' => true,
                'api_key' => 'test-key-2',
                'model' => 'claude-3-opus-20240229',
                'endpoint' => 'https://api.anthropic.com/v1/messages',
            ],
        ]);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'overall_score' => 8.0,
                                'evaluations' => [],
                                'summary' => 'OpenAI summary',
                                'key_issues' => ['issue1'],
                                'strengths' => ['strength1'],
                            ]),
                        ],
                    ],
                ],
            ], 200),
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    [
                        'text' => json_encode([
                            'overall_score' => 9.0,
                            'evaluations' => [],
                            'summary' => 'Anthropic summary',
                            'key_issues' => ['issue1'],
                            'strengths' => ['strength2'],
                        ]),
                    ],
                ],
            ], 200),
        ]);

        $evaluator = new BlogPostEvaluator();
        $content = 'This is a test blog post content that is more than 100 characters long to meet the minimum requirement.';
        
        $results = $evaluator->evaluate($content);

        $this->assertTrue($results['success']);
        $this->assertEquals(2, $results['providers_used']);
        $this->assertEquals(8.5, $results['average_overall_score']); // Average of 8.0 and 9.0
    }
}
