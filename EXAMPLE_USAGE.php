<?php

/**
 * Example Usage: Blog Post Evaluator
 * 
 * This file demonstrates how to use the BlogPostEvaluator service
 * in your Laravel application.
 */

use App\Services\BlogPostEvaluator;

// Example 1: Basic evaluation
$evaluator = new BlogPostEvaluator();

$blogContent = <<<BLOG
Title: 10 Tips for Better Time Management

Introduction:
Time management is crucial for productivity and success. In this article, 
we'll explore 10 proven strategies to help you make the most of your day.

1. Set Clear Goals
Start each day by identifying your top priorities. Use the SMART framework 
to ensure your goals are Specific, Measurable, Achievable, Relevant, and Time-bound.

2. Use Time Blocking
Allocate specific time slots for different tasks throughout your day. 
This prevents multitasking and helps you focus on one thing at a time.

3. Eliminate Distractions
Turn off notifications, close unnecessary tabs, and create a dedicated 
workspace to minimize interruptions.

... [additional content]

Conclusion:
By implementing these 10 time management strategies, you'll find yourself 
more productive and less stressed. Start with one or two techniques and 
gradually incorporate more as they become habits.
BLOG;

try {
    $results = $evaluator->evaluate($blogContent);
    
    if ($results['success']) {
        echo "Overall Score: " . $results['average_overall_score'] . "/10\n";
        echo "Providers Used: " . $results['providers_used'] . "\n\n";
        
        echo "Key Issues:\n";
        foreach ($results['consensus_issues'] as $issue) {
            echo "- " . $issue . "\n";
        }
        
        echo "\nStrengths:\n";
        foreach ($results['consensus_strengths'] as $strength) {
            echo "- " . $strength . "\n";
        }
        
        echo "\n" . $results['summary'] . "\n";
    } else {
        echo "Evaluation failed: " . $results['message'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Example 2: Evaluate and store results
use Illuminate\Support\Facades\DB;

$results = $evaluator->evaluate($blogContent);

if ($results['success']) {
    DB::table('blog_evaluations')->insert([
        'title' => 'Example Blog Post',
        'content' => $blogContent,
        'average_score' => $results['average_overall_score'],
        'providers_used' => $results['providers_used'],
        'results' => json_encode($results),
        'consensus_issues' => json_encode($results['consensus_issues']),
        'consensus_strengths' => json_encode($results['consensus_strengths']),
        'summary' => $results['summary'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

// Example 3: Get specific question analysis
if ($results['success']) {
    foreach ($results['question_scores'] as $questionScore) {
        if ($questionScore['average_score'] < 6) {
            echo "Low score on question {$questionScore['question_number']}: ";
            echo "{$questionScore['question']}\n";
            echo "Score: {$questionScore['average_score']}/10\n\n";
        }
    }
}

// Example 4: Compare individual provider results
if ($results['success']) {
    foreach ($results['individual_results'] as $provider => $result) {
        if ($result['success']) {
            echo "{$provider}: {$result['overall_score']}/10\n";
        }
    }
}
