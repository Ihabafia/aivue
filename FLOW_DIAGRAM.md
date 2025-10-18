# System Flow Diagram

## Complete Evaluation Flow

```
┌─────────────────────────────────────────────────────────────────┐
│ STEP 1: User Input                                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  User enters blog post content in Vue.js interface             │
│  ┌────────────────────────────────────┐                        │
│  │  Title: "10 Tips for..."            │                        │
│  │  Content: [Blog post text...]       │                        │
│  │  [Evaluate Blog Post] Button        │                        │
│  └────────────────────────────────────┘                        │
│                      │                                          │
│                      │ Submit                                   │
│                      ▼                                          │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ STEP 2: Request Validation                                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  BlogEvaluationController validates:                           │
│  ✓ Content exists (required)                                   │
│  ✓ Content length ≥ 100 characters                             │
│  ✓ Title ≤ 255 characters (if provided)                        │
│                      │                                          │
│                      │ Valid                                    │
│                      ▼                                          │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ STEP 3: Prompt Generation                                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  BlogPostEvaluator builds evaluation prompt:                   │
│  ┌───────────────────────────────────────────┐                │
│  │ Blog Content: [user's content]            │                │
│  │                                             │                │
│  │ Evaluation Questions:                       │                │
│  │ 1. Is the title clear, compelling...       │                │
│  │ 2. Does the introduction hook...           │                │
│  │ ...                                         │                │
│  │ 30. Does content align with best...        │                │
│  │                                             │                │
│  │ Please evaluate and respond in JSON         │                │
│  └───────────────────────────────────────────┘                │
│                      │                                          │
│                      ▼                                          │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ STEP 4: Multi-LLM Evaluation (Parallel/Sequential)              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│     ┌────────────────┐        ┌────────────────┐               │
│     │   OpenAI       │        │  Anthropic     │               │
│     │   (GPT-4)      │        │  (Claude)      │               │
│     └───────┬────────┘        └───────┬────────┘               │
│             │ Prompt                  │ Prompt                  │
│             ▼                         ▼                         │
│     ┌────────────────┐        ┌────────────────┐               │
│     │  Analysis &    │        │  Analysis &    │               │
│     │  Scoring 1-10  │        │  Scoring 1-10  │               │
│     └───────┬────────┘        └───────┬────────┘               │
│             │ JSON Response           │ JSON Response           │
│             ▼                         ▼                         │
│                                                                 │
│     ┌────────────────┐   ┌────────────────┐   ┌──────────────┐│
│     │    Google      │   │    Cohere      │   │   Mistral    ││
│     │   (Gemini)     │   │                │   │              ││
│     └───────┬────────┘   └───────┬────────┘   └──────┬───────┘│
│             │                    │                    │         │
│             ▼                    ▼                    ▼         │
│                                                                 │
│  Each LLM returns:                                              │
│  ┌─────────────────────────────────────────────┐               │
│  │ {                                            │               │
│  │   "overall_score": 8.5,                      │               │
│  │   "evaluations": [                           │               │
│  │     {                                        │               │
│  │       "question_number": 1,                  │               │
│  │       "score": 9,                            │               │
│  │       "explanation": "...",                  │               │
│  │       "recommendations": [...]               │               │
│  │     }, ...                                   │               │
│  │   ],                                         │               │
│  │   "summary": "Overall assessment...",        │               │
│  │   "key_issues": ["issue1", "issue2"],        │               │
│  │   "strengths": ["strength1", "strength2"]    │               │
│  │ }                                            │               │
│  └─────────────────────────────────────────────┘               │
│                      │                                          │
│                      ▼                                          │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ STEP 5: Result Aggregation                                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  BlogPostEvaluator aggregates all responses:                   │
│                                                                 │
│  1. Calculate Average Scores                                    │
│     OpenAI: 8.5  ┐                                              │
│     Anthropic: 7.8 ├─→ Average: 8.1                            │
│     Google: 8.3  │                                              │
│     Cohere: 7.9  │                                              │
│     Mistral: 8.0 ┘                                              │
│                                                                 │
│  2. Find Consensus Issues (mentioned by ≥2 LLMs)                │
│     "Missing call to action" ✓ (3 LLMs)                        │
│     "Needs more data" ✓ (2 LLMs)                               │
│     "Typo in paragraph 3" ✗ (1 LLM)                            │
│                                                                 │
│  3. Find Consensus Strengths (mentioned by ≥2 LLMs)             │
│     "Clear structure" ✓ (4 LLMs)                               │
│     "Good examples" ✓ (3 LLMs)                                 │
│                                                                 │
│  4. Aggregate Question Scores                                   │
│     Q1: [9, 8, 9, 8, 9] → Avg: 8.6                             │
│     Q2: [7, 6, 8, 7, 7] → Avg: 7.0                             │
│     ...                                                         │
│                      │                                          │
│                      ▼                                          │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ STEP 6: Response Generation                                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Unified JSON response created:                                │
│  ┌─────────────────────────────────────────────┐               │
│  │ {                                            │               │
│  │   "success": true,                           │               │
│  │   "providers_used": 5,                       │               │
│  │   "average_overall_score": 8.1,              │               │
│  │   "consensus_issues": [                      │               │
│  │     "Missing call to action",                │               │
│  │     "Needs more supporting data"             │               │
│  │   ],                                         │               │
│  │   "consensus_strengths": [                   │               │
│  │     "Clear structure",                       │               │
│  │     "Good examples"                          │               │
│  │   ],                                         │               │
│  │   "question_scores": [...],                  │               │
│  │   "summary": "Based on 5 AIs...",            │               │
│  │   "individual_results": {...}                │               │
│  │ }                                            │               │
│  └─────────────────────────────────────────────┘               │
│                      │                                          │
│                      ▼                                          │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ STEP 7: UI Display                                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Vue component renders results:                                │
│                                                                 │
│  ┌────────────────────────────────────────────────┐            │
│  │  📊 Evaluation Summary                          │            │
│  │  ┌──────┐  ┌──────┐  ┌──────┐                 │            │
│  │  │ 8.1  │  │  5   │  │  30  │                 │            │
│  │  │/10   │  │ AIs  │  │Quest.│                 │            │
│  │  └──────┘  └──────┘  └──────┘                 │            │
│  │                                                 │            │
│  │  ❌ Key Issues (Consensus):                     │            │
│  │  • Missing call to action                      │            │
│  │  • Needs more supporting data                  │            │
│  │                                                 │            │
│  │  ✅ Strengths (Consensus):                      │            │
│  │  • Clear structure                             │            │
│  │  • Good examples                               │            │
│  │                                                 │            │
│  │  📝 Detailed Analysis:                          │            │
│  │  Q1: Is title clear? ───────────── 8.6/10     │            │
│  │  Q2: Hook reader? ─────────────── 7.0/10      │            │
│  │  ...                                           │            │
│  │                                                 │            │
│  │  🤖 Individual AI Results:                      │            │
│  │  [Expandable sections for each provider]      │            │
│  └────────────────────────────────────────────────┘            │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Error Handling Flow

```
Provider Fails
     │
     ├─→ Timeout? ────→ Log error, continue with others
     │
     ├─→ Invalid Key? ─→ Log error, continue with others
     │
     ├─→ Rate Limit? ──→ Log error, continue with others
     │
     └─→ Bad JSON? ────→ Log error, continue with others
     
All Providers Fail?
     │
     └─→ Return error response with details

At Least One Succeeds?
     │
     └─→ Return partial results with warnings
```

## Performance Timeline

```
0s     User submits blog post
│
1s     Validation complete
│
2s     Prompt generated
│
3-63s  LLM evaluations (sequential)
│      ├── OpenAI:    10-15s
│      ├── Anthropic: 10-15s
│      ├── Google:    8-12s
│      ├── Cohere:    8-12s
│      └── Mistral:   8-12s
│
64s    Results aggregated
│
65s    Response sent to UI
│
66s    UI updated with results

Total: ~60-70 seconds (sequential)
       ~30-40 seconds (parallel, if enabled)
```
