# AI Blog Post Evaluator

A comprehensive blog post evaluation system that uses **5 different AI LLMs** to analyze content against **30 quality metrics**. This system provides multi-perspective analysis and consensus-based recommendations for improving your blog posts.

## Features

- **Multi-LLM Analysis**: Evaluates content using 5 different AI providers:
  - OpenAI (GPT-4)
  - Anthropic (Claude)
  - Google (Gemini)
  - Cohere
  - Mistral AI

- **Comprehensive Evaluation**: Analyzes blog posts across 30 questions covering:
  - Content Quality (10 questions)
  - SEO & Readability (10 questions)
  - Accuracy & Credibility (10 questions)

- **Consensus Analysis**: Identifies issues and strengths mentioned by multiple AI providers for more reliable feedback

- **Detailed Scoring**: Each question receives a score from 1-10 with explanations from each AI provider

## Installation

### Prerequisites

- Laravel 10+ application
- PHP 8.1+
- Inertia.js with Vue 3
- Tailwind CSS

### Setup Instructions

1. **Copy the files** to your Laravel application:
   - `config/blog-evaluator.php` - Configuration file
   - `app/Services/BlogPostEvaluator.php` - Main evaluation service
   - `app/Http/Controllers/BlogEvaluationController.php` - Controller
   - `routes/web.php` - Routes (merge with your existing routes)
   - `resources/js/Components/BlogEvaluator.vue` - Vue component
   - `database/migrations/2024_01_01_000000_create_blog_evaluations_table.php` - Database migration

2. **Configure API Keys**:
   
   Copy the `.env.example` variables to your `.env` file and add your API keys:

   ```env
   OPENAI_ENABLED=true
   OPENAI_API_KEY=sk-...
   
   ANTHROPIC_ENABLED=true
   ANTHROPIC_API_KEY=sk-ant-...
   
   GOOGLE_ENABLED=true
   GOOGLE_API_KEY=AI...
   
   COHERE_ENABLED=true
   COHERE_API_KEY=...
   
   MISTRAL_ENABLED=true
   MISTRAL_API_KEY=...
   ```

   **Note**: At least one AI provider must be configured for the system to work.

3. **Run Database Migration**:
   ```bash
   php artisan migrate
   ```

4. **Register the Vue Component** in your Inertia layout or app.js:
   ```javascript
   import BlogEvaluator from './Components/BlogEvaluator.vue';
   ```

## Usage

### Web Interface

1. Navigate to `/blog-evaluator` in your browser
2. Enter your blog post title (optional) and content (minimum 100 characters)
3. Click "Evaluate Blog Post"
4. Wait for the evaluation (typically 30-60 seconds)
5. Review the results:
   - Overall score and summary
   - Consensus issues and strengths
   - Detailed question-by-question analysis
   - Individual provider results

### Programmatic Usage

You can also use the evaluator service directly in your code:

```php
use App\Services\BlogPostEvaluator;

$evaluator = new BlogPostEvaluator();
$results = $evaluator->evaluate($blogPostContent);

// Access results
$overallScore = $results['average_overall_score'];
$issues = $results['consensus_issues'];
$strengths = $results['consensus_strengths'];
```

## The 30 Evaluation Questions

### Content Quality (1-10)
1. Is the title clear, compelling, and accurately represents the content?
2. Does the introduction effectively hook the reader and outline what to expect?
3. Is the content well-structured with clear sections and logical flow?
4. Are the main points well-supported with evidence, examples, or data?
5. Is the conclusion effective in summarizing key points and providing closure?
6. Is the writing style appropriate for the target audience?
7. Are there any grammatical, spelling, or punctuation errors?
8. Is the tone consistent throughout the article?
9. Are technical terms and jargon explained adequately?
10. Does the content provide value and actionable insights to readers?

### SEO & Readability (11-20)
11. Are keywords naturally integrated without keyword stuffing?
12. Are headings (H1, H2, H3) properly structured for SEO?
13. Is the content length appropriate for the topic?
14. Are sentences and paragraphs concise and easy to read?
15. Is the Flesch Reading Ease score at an appropriate level?
16. Are there sufficient internal and external links?
17. Are images, if referenced, properly described with alt text?
18. Is the meta description compelling and within character limits?
19. Are there any broken or missing links?
20. Does the content answer common user search queries?

### Accuracy & Credibility (21-30)
21. Are all facts and statistics accurate and up-to-date?
22. Are sources properly cited and credible?
23. Is the information balanced and unbiased?
24. Are there any misleading or false claims?
25. Is the author's expertise or credentials mentioned?
26. Are there any potential copyright or plagiarism issues?
27. Are comparisons fair and well-researched?
28. Is the information current and relevant?
29. Are there any contradictions within the content?
30. Does the content align with industry best practices and standards?

## API Response Format

The evaluation returns a JSON response with the following structure:

```json
{
  "success": true,
  "providers_used": 5,
  "total_providers": 5,
  "average_overall_score": 7.8,
  "consensus_issues": [
    "lack of supporting data",
    "missing call to action"
  ],
  "consensus_strengths": [
    "clear structure",
    "engaging introduction"
  ],
  "question_scores": [
    {
      "question_number": 1,
      "question": "Is the title clear...",
      "average_score": 8.4,
      "provider_scores": [
        {
          "provider": "openai",
          "score": 9,
          "explanation": "..."
        }
      ]
    }
  ],
  "summary": "Based on evaluation by 5 AI providers...",
  "individual_results": {
    "openai": { ... },
    "anthropic": { ... }
  }
}
```

## Configuration

### Timeout Settings

Adjust timeout for LLM API calls in `.env`:
```env
EVALUATION_TIMEOUT=60  # seconds
```

### Enable/Disable Providers

You can selectively enable or disable providers:
```env
OPENAI_ENABLED=true
ANTHROPIC_ENABLED=false
```

### Custom Models

Specify different models for each provider:
```env
OPENAI_MODEL=gpt-4-turbo
ANTHROPIC_MODEL=claude-3-sonnet-20240229
```

## Troubleshooting

### All evaluations fail
- Check that at least one provider is enabled and has a valid API key
- Verify API keys are correctly set in `.env`
- Check Laravel logs for specific error messages

### Timeout errors
- Increase `EVALUATION_TIMEOUT` in `.env`
- Consider disabling slower providers

### JSON parsing errors
- This may occur if an LLM returns non-JSON content
- The system attempts to extract JSON from markdown code blocks
- Check individual provider results in the response

## Cost Considerations

Using 5 AI providers per evaluation will incur API costs from each provider. Estimated costs per evaluation:

- OpenAI (GPT-4): $0.10 - $0.30
- Anthropic (Claude): $0.10 - $0.25
- Google (Gemini): $0.05 - $0.15
- Cohere: $0.05 - $0.10
- Mistral: $0.05 - $0.10

**Total per evaluation: ~$0.35 - $0.90**

To reduce costs:
- Disable some providers
- Use cheaper models (e.g., GPT-3.5 instead of GPT-4)
- Cache results for similar content

## Security

- API keys are stored in `.env` and should never be committed to version control
- The `.env` file is listed in `.gitignore`
- Use `.env.example` as a template

## License

This code is provided as-is for use with the aivue project.

## Support

For issues or questions, please create an issue in the GitHub repository.
