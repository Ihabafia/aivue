# Architecture Overview

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         User Interface                          │
│              (Vue.js + Tailwind CSS + Inertia.js)              │
│                                                                 │
│  ┌──────────────────────────────────────────────────────┐     │
│  │     BlogEvaluator.vue Component                       │     │
│  │  - Blog post input form                              │     │
│  │  - Provider status display                           │     │
│  │  - Results visualization                             │     │
│  │  - Question scores breakdown                         │     │
│  └──────────────────────────────────────────────────────┘     │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             │ POST /api/blog-evaluator/evaluate
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Laravel Backend                              │
│                                                                 │
│  ┌──────────────────────────────────────────────────────┐     │
│  │     BlogEvaluationController                          │     │
│  │  - Validates input                                   │     │
│  │  - Calls BlogPostEvaluator service                   │     │
│  │  - Returns JSON response                             │     │
│  └─────────────────────┬────────────────────────────────┘     │
│                        │                                        │
│                        ▼                                        │
│  ┌──────────────────────────────────────────────────────┐     │
│  │     BlogPostEvaluator Service                         │     │
│  │                                                       │     │
│  │  1. Loads 30 evaluation questions                    │     │
│  │  2. Builds prompt for each LLM                       │     │
│  │  3. Calls each enabled provider                      │     │
│  │  4. Parses JSON responses                            │     │
│  │  5. Aggregates results                               │     │
│  │  6. Finds consensus issues/strengths                 │     │
│  │  7. Returns unified response                         │     │
│  └─────────────────────┬────────────────────────────────┘     │
└────────────────────────┼────────────────────────────────────────┘
                         │
                         │ HTTP Requests
        ┌────────────────┴────────────────┐
        │                                  │
        ▼                                  ▼
┌───────────────┐                 ┌───────────────┐
│  LLM Provider │                 │  LLM Provider │
│   (OpenAI)    │                 │  (Anthropic)  │
└───────────────┘                 └───────────────┘
        │                                  │
        ▼                                  ▼
┌───────────────┐                 ┌───────────────┐
│  LLM Provider │                 │  LLM Provider │
│   (Google)    │                 │   (Cohere)    │
└───────────────┘                 └───────────────┘
        │                                  │
        ▼                                  ▼
                    ┌───────────────┐
                    │  LLM Provider │
                    │   (Mistral)   │
                    └───────────────┘
```

## Data Flow

### 1. User Input
```
User → Vue Component → Form Data {title, content}
```

### 2. Request Processing
```
Vue Component → POST /api/blog-evaluator/evaluate
              → BlogEvaluationController
              → Validates: content (min 100 chars)
```

### 3. Evaluation Process
```
BlogPostEvaluator Service:
  ├─ Load config (30 questions, 5 providers)
  ├─ Build evaluation prompt
  │  └─ Questions + Blog Content
  │
  ├─ For each enabled provider:
  │  ├─ Call provider API
  │  ├─ Parse JSON response
  │  └─ Store result
  │
  └─ Aggregate results:
     ├─ Calculate average scores
     ├─ Find consensus issues
     ├─ Find consensus strengths
     └─ Generate summary
```

### 4. Response
```
Aggregated Results → JSON → Vue Component → UI Display
```

## Component Breakdown

### Configuration Layer
**File**: `config/blog-evaluator.php`
- 5 AI provider configurations
- 30 evaluation questions
- Timeout and retry settings

### Service Layer
**File**: `app/Services/BlogPostEvaluator.php`
- Core evaluation logic
- Provider-specific API calls
- Response parsing
- Result aggregation
- Consensus finding

### Controller Layer
**File**: `app/Http/Controllers/BlogEvaluationController.php`
- Request validation
- Service coordination
- Response formatting

### Presentation Layer
**File**: `resources/js/Components/BlogEvaluator.vue`
- User interface
- Form handling
- Results visualization
- Provider status

### Data Layer
**File**: `database/migrations/..._create_blog_evaluations_table.php`
- Stores evaluation history
- Fields: title, content, scores, results, issues, strengths

## Key Features

### Multi-Provider Evaluation
- Parallel or sequential API calls
- Independent evaluation by each LLM
- Graceful failure handling
- Success if at least one provider works

### Consensus Building
- Issues mentioned by ≥2 providers
- Strengths mentioned by ≥2 providers
- Average scoring across providers
- Individual and aggregate views

### 30-Point Analysis
Organized into 3 categories:
1. **Content Quality** (Questions 1-10)
2. **SEO & Readability** (Questions 11-20)
3. **Accuracy & Credibility** (Questions 21-30)

## Configuration

### Environment Variables
```env
# Provider Enable/Disable
OPENAI_ENABLED=true
ANTHROPIC_ENABLED=true
GOOGLE_ENABLED=true
COHERE_ENABLED=true
MISTRAL_ENABLED=true

# API Keys
OPENAI_API_KEY=...
ANTHROPIC_API_KEY=...
GOOGLE_API_KEY=...
COHERE_API_KEY=...
MISTRAL_API_KEY=...

# Models
OPENAI_MODEL=gpt-4
ANTHROPIC_MODEL=claude-3-opus-20240229
GOOGLE_MODEL=gemini-pro
COHERE_MODEL=command
MISTRAL_MODEL=mistral-large-latest

# Settings
EVALUATION_TIMEOUT=60
EVALUATION_MAX_RETRIES=3
```

## Error Handling

### Provider-Level Errors
- API timeout → Logged and marked as failed
- Invalid API key → Logged and marked as failed
- Rate limit → Logged and marked as failed
- Continues with other providers

### System-Level Errors
- No providers enabled → Exception thrown
- All providers fail → Error response
- Invalid JSON response → Logged and marked as failed

## Performance Considerations

### Sequential vs Parallel
- **Sequential**: Safer, slower (~2-5 minutes)
- **Parallel**: Faster, more complex (~30-60 seconds)

### Caching Opportunities
- Cache results for identical content
- Cache provider configurations
- Cache question lists

### Cost Management
- Each evaluation costs $0.35-$0.90
- Disable expensive providers (GPT-4)
- Use cheaper models
- Implement rate limiting

## Extension Points

### Adding New Providers
1. Add configuration in `config/blog-evaluator.php`
2. Add method in `BlogPostEvaluator::evaluateWithProvider()`
3. Add environment variables in `.env`

### Custom Questions
Modify `evaluation_questions` array in config file

### Custom Aggregation
Override `aggregateResults()` method

### Persistent Storage
Extend controller to save results to database using `blog_evaluations` table
