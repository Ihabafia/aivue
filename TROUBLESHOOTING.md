# Troubleshooting Guide

## Common Issues and Solutions

### Installation Issues

#### Issue: "composer: command not found"
**Solution:**
```bash
# Install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

#### Issue: "npm: command not found"
**Solution:**
```bash
# Install Node.js and npm
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

#### Issue: Migration fails with "Class not found"
**Solution:**
```bash
# Clear and regenerate autoload
composer dump-autoload
php artisan cache:clear
php artisan config:clear
php artisan migrate
```

---

### API Configuration Issues

#### Issue: "No AI providers are enabled"
**Cause:** No API keys configured or all providers disabled

**Solution:**
1. Check `.env` file exists:
   ```bash
   ls -la .env
   ```

2. If missing, copy from example:
   ```bash
   cp .env.example .env
   ```

3. Add at least one API key:
   ```env
   OPENAI_ENABLED=true
   OPENAI_API_KEY=sk-your-actual-key-here
   ```

4. Clear config cache:
   ```bash
   php artisan config:clear
   ```

#### Issue: "API error: 401 Unauthorized"
**Cause:** Invalid or expired API key

**Solution:**
1. Verify API key in provider dashboard
2. Check for extra spaces in `.env`:
   ```env
   # Wrong (has space)
   OPENAI_API_KEY= sk-abc123

   # Correct (no space)
   OPENAI_API_KEY=sk-abc123
   ```

3. Regenerate API key if necessary

#### Issue: "API error: 429 Too Many Requests"
**Cause:** Rate limit exceeded

**Solution:**
1. Wait before retrying
2. Check your usage quota in provider dashboard
3. Upgrade your API plan
4. Disable some providers to reduce calls:
   ```env
   OPENAI_ENABLED=true
   ANTHROPIC_ENABLED=false  # Disable to reduce calls
   GOOGLE_ENABLED=false
   ```

---

### Evaluation Issues

#### Issue: "Evaluation timeout"
**Cause:** LLM taking too long to respond

**Solution:**
1. Increase timeout in `.env`:
   ```env
   EVALUATION_TIMEOUT=120  # Increase from 60 to 120 seconds
   ```

2. Clear config cache:
   ```bash
   php artisan config:clear
   ```

3. Try with shorter content first

#### Issue: "All LLM evaluations failed"
**Cause:** Multiple possible reasons

**Solution:**
1. Check Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. Test each provider individually by disabling others:
   ```env
   # Test OpenAI only
   OPENAI_ENABLED=true
   ANTHROPIC_ENABLED=false
   GOOGLE_ENABLED=false
   COHERE_ENABLED=false
   MISTRAL_ENABLED=false
   ```

3. Verify internet connectivity:
   ```bash
   curl https://api.openai.com/v1/models
   ```

#### Issue: "Failed to parse LLM response as JSON"
**Cause:** LLM returned non-JSON format

**Solution:**
1. This is usually temporary - retry the evaluation
2. Check individual provider results in response
3. Try with different/simpler content
4. If persistent for one provider, disable it

---

### UI Issues

#### Issue: "Page not found" when accessing /blog-evaluator
**Cause:** Routes not loaded or Inertia not configured

**Solution:**
1. Clear route cache:
   ```bash
   php artisan route:clear
   php artisan route:cache
   ```

2. Verify routes exist:
   ```bash
   php artisan route:list | grep blog-evaluator
   ```

3. Check web.php routes are loaded in your main routes file

#### Issue: "Inertia page not rendering"
**Cause:** Vue component not found or build issue

**Solution:**
1. Build frontend assets:
   ```bash
   npm run build
   ```

2. For development, use watch mode:
   ```bash
   npm run dev
   ```

3. Check component path matches in controller:
   ```php
   // In BlogEvaluationController
   return Inertia::render('BlogEvaluator', [...]);
   ```

#### Issue: "Evaluation button doesn't work"
**Cause:** CSRF token missing or JavaScript error

**Solution:**
1. Ensure CSRF meta tag in layout:
   ```html
   <meta name="csrf-token" content="{{ csrf_token() }}">
   ```

2. Check browser console for errors (F12)

3. Verify API route is not behind auth middleware

---

### Performance Issues

#### Issue: "Evaluation takes too long (>2 minutes)"
**Cause:** Sequential provider calls

**Solution:**
1. Enable parallel requests (experimental):
   ```env
   EVALUATION_PARALLEL=true
   ```

2. Reduce number of enabled providers:
   ```env
   # Use only fastest providers
   OPENAI_ENABLED=false    # Slower with GPT-4
   GOOGLE_ENABLED=true     # Usually fast
   MISTRAL_ENABLED=true    # Usually fast
   ```

3. Use faster models:
   ```env
   OPENAI_MODEL=gpt-3.5-turbo  # Faster than gpt-4
   ANTHROPIC_MODEL=claude-3-haiku-20240307  # Faster than opus
   ```

#### Issue: "High API costs"
**Cause:** Using expensive models on large content

**Solution:**
1. Use cheaper models:
   ```env
   OPENAI_MODEL=gpt-3.5-turbo
   ANTHROPIC_MODEL=claude-3-haiku-20240307
   ```

2. Reduce number of providers from 5 to 2-3

3. Implement caching for repeated content

4. Add rate limiting to prevent abuse:
   ```php
   // In routes/web.php
   Route::post('/api/blog-evaluator/evaluate', ...)
       ->middleware('throttle:5,1'); // 5 requests per minute
   ```

---

### Database Issues

#### Issue: "Migration already exists"
**Cause:** Running migration twice

**Solution:**
```bash
# Check migration status
php artisan migrate:status

# If needed, rollback and re-run
php artisan migrate:rollback --step=1
php artisan migrate
```

#### Issue: "Table 'blog_evaluations' doesn't exist"
**Cause:** Migration not run

**Solution:**
```bash
# Run migrations
php artisan migrate

# If issues, try:
php artisan migrate:fresh  # WARNING: Deletes all data
```

---

### Testing Issues

#### Issue: "Tests fail with 'Class not found'"
**Cause:** Autoload or namespace issue

**Solution:**
```bash
composer dump-autoload
php artisan config:clear
```

#### Issue: "HTTP tests fail with 404"
**Cause:** Routes not registered in test environment

**Solution:**
1. Check test extends TestCase:
   ```php
   class BlogPostEvaluatorTest extends TestCase
   ```

2. Ensure routes are loaded in tests/TestCase.php

#### Issue: "Tests timeout"
**Cause:** Actual API calls in tests

**Solution:**
Tests should use Http::fake() to mock responses (already implemented)

---

## Debugging Tips

### Enable Debug Mode
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Check Logs
```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log

# Search for errors
grep -i "error\|exception" storage/logs/laravel.log
```

### Test Individual Components

**Test Configuration:**
```bash
php artisan tinker
>>> config('blog-evaluator.providers')
>>> config('blog-evaluator.evaluation_questions')
```

**Test Service:**
```bash
php artisan tinker
>>> $evaluator = new App\Services\BlogPostEvaluator();
>>> $result = $evaluator->evaluate('Test content that is more than 100 characters...');
>>> print_r($result);
```

**Test API Endpoint:**
```bash
curl -X POST http://localhost:8000/api/blog-evaluator/evaluate \
  -H "Content-Type: application/json" \
  -d '{"content":"Test blog content that is definitely more than one hundred characters long so it passes validation and can be evaluated properly."}'
```

### Common Log Messages

**Normal Operation:**
```
[INFO] BlogPostEvaluator: Starting evaluation with 5 providers
[INFO] BlogPostEvaluator: OpenAI evaluation completed
[INFO] BlogPostEvaluator: Evaluation completed with 5/5 providers
```

**Errors to Watch For:**
```
[ERROR] Evaluation failed for openai: API timeout
[ERROR] Failed to parse LLM response as JSON
[ERROR] No AI providers are enabled
```

---

## Getting Help

### 1. Check Documentation
- BLOG_EVALUATOR_README.md - Full documentation
- API_DOCUMENTATION.md - API reference
- ARCHITECTURE.md - System architecture
- QUICK_START.md - Quick setup guide

### 2. Search Issues
- Check GitHub issues: https://github.com/Ihabafia/aivue/issues

### 3. Create Issue
Include:
- Error message (full stack trace)
- Laravel version
- PHP version
- Steps to reproduce
- Relevant .env settings (without API keys!)

### 4. Community Support
- Check Laravel documentation
- Check provider API documentation
- Stack Overflow with tag [laravel] or specific provider tags

---

## Prevention

### Best Practices

1. **Always use .env for API keys**
   - Never hardcode API keys
   - Never commit .env to git

2. **Implement rate limiting**
   - Prevent API cost abuse
   - Use Laravel's throttle middleware

3. **Monitor API usage**
   - Check provider dashboards regularly
   - Set up billing alerts

4. **Cache results**
   - Store evaluations in database
   - Return cached results for identical content

5. **Handle errors gracefully**
   - System continues if one provider fails
   - Log all errors for debugging

6. **Test before deploying**
   - Run test suite
   - Test with sample content
   - Verify all providers work

7. **Keep dependencies updated**
   ```bash
   composer update
   npm update
   ```
