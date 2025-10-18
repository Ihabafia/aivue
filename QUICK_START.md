# Quick Start Guide

## What This System Does

Evaluates blog posts using **5 different AI LLMs** against **30 quality questions**, providing:
- Overall quality scores
- Consensus feedback from multiple AIs
- Detailed question-by-question analysis
- Issues and strengths identification

## 5-Minute Setup

### 1. Get API Keys
You need at least ONE of these (preferably all 5):
- **OpenAI**: https://platform.openai.com/api-keys
- **Anthropic**: https://console.anthropic.com/
- **Google**: https://makersuite.google.com/app/apikey
- **Cohere**: https://dashboard.cohere.com/api-keys
- **Mistral**: https://console.mistral.ai/

### 2. Configure Environment
```bash
# Copy example env file
cp .env.example .env

# Edit .env and add your API keys
# At minimum, add one key:
OPENAI_API_KEY=sk-your-key-here
```

### 3. Install & Setup
```bash
# Run setup script
chmod +x setup.sh
./setup.sh

# Or manually:
composer install
npm install
php artisan migrate
npm run build
php artisan serve
```

### 4. Access
Open browser: http://localhost:8000/blog-evaluator

## Usage Example

### Web Interface
1. Paste your blog post content (100+ characters)
2. Click "Evaluate Blog Post"
3. Wait 30-60 seconds
4. Review results

### Programmatic
```php
use App\Services\BlogPostEvaluator;

$evaluator = new BlogPostEvaluator();
$results = $evaluator->evaluate($blogContent);

echo "Score: " . $results['average_overall_score'] . "/10\n";
print_r($results['consensus_issues']);
```

## What Gets Evaluated?

### Content Quality (10 questions)
- Title clarity
- Introduction effectiveness
- Structure and flow
- Evidence and support
- Conclusion quality
- Writing style
- Grammar and errors
- Tone consistency
- Technical explanations
- Value to readers

### SEO & Readability (10 questions)
- Keyword integration
- Heading structure
- Content length
- Sentence structure
- Reading ease
- Links (internal/external)
- Image descriptions
- Meta description
- Broken links
- Search query answers

### Accuracy & Credibility (10 questions)
- Fact accuracy
- Source citations
- Information balance
- Misleading claims
- Author credentials
- Copyright issues
- Fair comparisons
- Information currency
- Contradictions
- Best practices alignment

## Interpreting Results

### Scores
- **8-10**: Excellent
- **6-7**: Good, minor improvements needed
- **4-5**: Fair, notable issues
- **1-3**: Poor, major revision needed

### Consensus Items
- **Issues**: Problems identified by 2+ AIs (high confidence)
- **Strengths**: Positives identified by 2+ AIs (reliable)

## Cost Per Evaluation

Approximate costs (varies by content length):
- OpenAI (GPT-4): $0.10-$0.30
- Anthropic (Claude): $0.10-$0.25
- Google (Gemini): $0.05-$0.15
- Cohere: $0.05-$0.10
- Mistral: $0.05-$0.10

**Total: ~$0.35-$0.90 per evaluation**

To reduce costs:
- Use fewer providers
- Use cheaper models (GPT-3.5, Claude Haiku)
- Disable providers in .env

## Troubleshooting

### "No AI providers are enabled"
➜ Add at least one API key to .env

### "API error: 401"
➜ Check your API key is correct

### "Timeout"
➜ Increase EVALUATION_TIMEOUT in .env

### All evaluations fail
➜ Check Laravel logs: `tail -f storage/logs/laravel.log`

## File Structure

```
aivue/
├── config/blog-evaluator.php          # Configuration
├── app/Services/BlogPostEvaluator.php # Core service
├── app/Http/Controllers/...           # API controller
├── resources/js/Components/...        # Vue UI
├── routes/web.php                     # Routes
├── database/migrations/...            # Database
├── tests/Feature/...                  # Tests
└── .env                               # Your config (not committed)
```

## Documentation

- **Full Documentation**: `BLOG_EVALUATOR_README.md`
- **API Reference**: `API_DOCUMENTATION.md`
- **Architecture**: `ARCHITECTURE.md`
- **Examples**: `EXAMPLE_USAGE.php`

## Next Steps

1. ✓ Set up API keys
2. ✓ Run the evaluator
3. Try evaluating different content types
4. Customize questions in config if needed
5. Add result persistence (database already set up)
6. Implement caching for repeated content
7. Add rate limiting for production use

## Support

- Issues: https://github.com/Ihabafia/aivue/issues
- Documentation: See MD files in repository root
- Examples: See EXAMPLE_USAGE.php

---

**Pro Tip**: Start with just OpenAI or Google (cheaper) to test, then enable all 5 for production use.
