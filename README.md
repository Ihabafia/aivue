# aivue
AI in Laravel / Inertia / Vue / Tailwind CSS

## AI Blog Post Evaluator

A comprehensive blog post evaluation system that uses **5 different AI LLMs** to analyze and evaluate blog post content against **30 quality metrics**.

### Features

- **5 AI Providers**: OpenAI, Anthropic, Google, Cohere, and Mistral AI
- **30 Evaluation Questions**: Comprehensive analysis of content quality, SEO, readability, accuracy, and credibility
- **Consensus Analysis**: Identifies common issues and strengths across multiple AI providers
- **Detailed Scoring**: Each question receives scores and explanations from all providers
- **Vue.js Interface**: Beautiful, responsive UI built with Vue 3 and Tailwind CSS

### Quick Start

1. Configure API keys in `.env` (see `.env.example`)
2. Run migrations: `php artisan migrate`
3. Access the evaluator at `/blog-evaluator`

For detailed documentation, see [BLOG_EVALUATOR_README.md](BLOG_EVALUATOR_README.md)
