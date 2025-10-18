<?php

namespace App\Http\Controllers;

use App\Services\BlogPostEvaluator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class BlogEvaluationController extends Controller
{
    protected BlogPostEvaluator $evaluator;

    public function __construct(BlogPostEvaluator $evaluator)
    {
        $this->evaluator = $evaluator;
    }

    /**
     * Show the blog evaluation form
     */
    public function index()
    {
        return Inertia::render('BlogEvaluator', [
            'questions' => config('blog-evaluator.evaluation_questions'),
            'providers' => $this->getProviderStatus(),
        ]);
    }

    /**
     * Evaluate a blog post
     */
    public function evaluate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:100',
            'title' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $content = $request->input('content');
            $title = $request->input('title');
            
            // Add title to content if provided
            $fullContent = $title ? "Title: {$title}\n\n{$content}" : $content;
            
            $results = $this->evaluator->evaluate($fullContent);

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Evaluation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get status of configured providers
     */
    protected function getProviderStatus(): array
    {
        $providers = config('blog-evaluator.providers', []);
        $status = [];

        foreach ($providers as $name => $config) {
            $status[$name] = [
                'enabled' => $config['enabled'] ?? false,
                'configured' => !empty($config['api_key']),
                'model' => $config['model'] ?? 'N/A',
            ];
        }

        return $status;
    }
}
