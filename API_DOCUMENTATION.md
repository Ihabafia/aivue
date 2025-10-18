# API Documentation

## Blog Post Evaluation API

### Endpoints

#### 1. GET /blog-evaluator
Shows the blog evaluation interface.

**Response**: Inertia page with Vue component

---

#### 2. POST /api/blog-evaluator/evaluate
Evaluates blog post content using 5 AI LLMs.

**Request Body**:
```json
{
  "title": "Optional blog post title",
  "content": "Blog post content (minimum 100 characters)"
}
```

**Validation Rules**:
- `content`: required, string, minimum 100 characters
- `title`: optional, string, maximum 255 characters

**Success Response (200 OK)**:
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
      "question": "Is the title clear, compelling, and accurately represents the content?",
      "average_score": 8.4,
      "provider_scores": [
        {
          "provider": "openai",
          "score": 9,
          "explanation": "The title is clear and descriptive"
        },
        {
          "provider": "anthropic",
          "score": 8,
          "explanation": "Good title but could be more compelling"
        }
      ]
    }
  ],
  "summary": "Based on evaluation by 5 AI providers: ...",
  "individual_results": {
    "openai": {
      "success": true,
      "overall_score": 7.5,
      "evaluations": [...],
      "summary": "...",
      "key_issues": [...],
      "strengths": [...]
    },
    "anthropic": { ... },
    "google": { ... },
    "cohere": { ... },
    "mistral": { ... }
  }
}
```

**Validation Error Response (422 Unprocessable Entity)**:
```json
{
  "success": false,
  "errors": {
    "content": [
      "The content field is required."
    ]
  }
}
```

**Error Response (500 Internal Server Error)**:
```json
{
  "success": false,
  "message": "Evaluation failed: [error details]"
}
```

---

## Response Fields

### Main Response Object

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Whether the evaluation was successful |
| `providers_used` | integer | Number of AI providers that successfully evaluated |
| `total_providers` | integer | Total number of configured providers |
| `average_overall_score` | float | Average score across all providers (1-10) |
| `consensus_issues` | array | Issues mentioned by multiple providers |
| `consensus_strengths` | array | Strengths mentioned by multiple providers |
| `question_scores` | array | Aggregated scores for each question |
| `summary` | string | Combined summary from all providers |
| `individual_results` | object | Individual results from each provider |

### Question Score Object

| Field | Type | Description |
|-------|------|-------------|
| `question_number` | integer | Question number (1-30) |
| `question` | string | The evaluation question |
| `average_score` | float | Average score from all providers |
| `provider_scores` | array | Individual scores from each provider |

### Provider Score Object

| Field | Type | Description |
|-------|------|-------------|
| `provider` | string | Name of the AI provider |
| `score` | integer | Score given by this provider (1-10) |
| `explanation` | string | Provider's explanation for the score |

### Individual Provider Result

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Whether this provider succeeded |
| `overall_score` | float | Overall score from this provider |
| `evaluations` | array | Detailed evaluations for each question |
| `summary` | string | Provider's overall summary |
| `key_issues` | array | Issues identified by this provider |
| `strengths` | array | Strengths identified by this provider |
| `error` | string | Error message (only if success is false) |

---

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

---

## Rate Limits

The system is subject to rate limits from the individual AI providers:
- OpenAI: Depends on your API tier
- Anthropic: Depends on your API tier
- Google: Depends on your API tier
- Cohere: Depends on your API tier
- Mistral: Depends on your API tier

**Recommendation**: Implement your own rate limiting on the `/api/blog-evaluator/evaluate` endpoint to prevent excessive API costs.

---

## Error Handling

The API handles errors gracefully:
- If one provider fails, the system continues with others
- If all providers fail, returns a 500 error with details
- Provider-specific errors are logged and included in `individual_results`

---

## Performance

- Typical evaluation time: 30-60 seconds
- Timeout per provider: 60 seconds (configurable)
- Providers are called sequentially by default
- Set `EVALUATION_PARALLEL=true` in .env for parallel calls (experimental)

---

## Security

- All API keys should be stored in `.env` file
- Never commit `.env` to version control
- The `.env.example` file is provided as a template
- API keys are not exposed in responses
