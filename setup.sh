#!/bin/bash

# Blog Post Evaluator - Setup Script
# This script helps set up the blog post evaluation system

echo "=========================================="
echo "Blog Post Evaluator - Setup Script"
echo "=========================================="
echo ""

# Check if .env file exists
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    echo "✓ .env file created"
else
    echo "⚠ .env file already exists, skipping..."
fi

echo ""
echo "Next steps:"
echo "1. Edit .env file and add your API keys for the AI providers:"
echo "   - OPENAI_API_KEY"
echo "   - ANTHROPIC_API_KEY"
echo "   - GOOGLE_API_KEY"
echo "   - COHERE_API_KEY"
echo "   - MISTRAL_API_KEY"
echo ""
echo "2. Install PHP dependencies:"
echo "   composer install"
echo ""
echo "3. Install JavaScript dependencies:"
echo "   npm install"
echo ""
echo "4. Run database migrations:"
echo "   php artisan migrate"
echo ""
echo "5. Build frontend assets:"
echo "   npm run build"
echo ""
echo "6. Start the development server:"
echo "   php artisan serve"
echo ""
echo "7. Access the blog evaluator at:"
echo "   http://localhost:8000/blog-evaluator"
echo ""
echo "For detailed documentation, see BLOG_EVALUATOR_README.md"
echo "=========================================="
