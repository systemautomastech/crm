<?php

namespace Automas\AIBusinessAdvisor\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIAnalysisService
{
    /**
     * Analyze metrics using the configured AI provider.
     */
    public function analyze(array $metrics, array $healthScore, ?int $userId = null): array
    {
        $provider = company_setting('ai_agent_provider', $userId);
        $model    = company_setting('ai_agent_model', $userId);
        $apiKey   = company_setting('ai_agent_api_key', $userId);

        if (!$provider || !$model || !$apiKey) {
            throw new \Exception(__('AI Agent is not configured. Please configure it in Settings → AI Agent.'));
        }

        $currencySymbol  = company_setting('currencySymbol', $userId);
        $defaultCurrency = company_setting('defaultCurrency', $userId);

        $prompt = $this->buildPrompt($metrics, $healthScore, $currencySymbol, $defaultCurrency);

        $response = match ($provider) {
            'openai'    => $this->callOpenAI($model, $apiKey, $prompt),
            'anthropic' => $this->callAnthropic($model, $apiKey, $prompt),
            'google'    => $this->callGoogle($model, $apiKey, $prompt),
            default     => throw new \Exception('Unsupported AI provider: ' . $provider),
        };

        return $response;
    }

    /**
     * Build the AI prompt with metrics and health scores.
     */
    private function buildPrompt(array $metrics, array $healthScore, string $currencySymbol, string $defaultCurrency): string
    {
        $metricsJson = json_encode($metrics, JSON_PRETTY_PRINT);

        return <<<PROMPT
You are a senior business intelligence advisor analyzing a company's operational and financial performance. Your analysis must be professional, data-driven, and easy for business owners and managers to understand. Respond ONLY with valid JSON — no extra text, no markdown formatting.

BUSINESS HEALTH SCORES:
- Overall: {$healthScore['score']}/100
- Financial: {$healthScore['financial_score']}/100
- Team: {$healthScore['team_score']}/100
- Sales: {$healthScore['sales_score']}/100
- Projects: {$healthScore['project_score']}/100
- Operations: {$healthScore['operations_score']}/100

BUSINESS METRICS:
{$metricsJson}

Respond with this exact JSON structure:
{
  "insights": [
    {
      "title": "Professional insight title (5-8 words)",
      "description": "Clear explanation with specific numbers and business impact (1-2 sentences)",
      "severity": "positive|info|warning|critical",
      "module": "account|hrm|sales|projects|pos|inventory"
    }
  ],
  "recommendations": [
    {
      "recommendation": "Clear, actionable step the user should take (1-2 sentences)",
      "reason": "Business value or risk of not acting (1 sentence)",
      "priority": "high|medium|low",
      "related_module": "account|hrm|sales|projects|pos|inventory"
    }
  ],
  "alerts": [
    {
      "title": "Clear alert headline stating the issue (4-6 words)",
      "message": "Direct explanation of the problem, its impact, and urgency (1-2 sentences)",
      "severity": "warning|critical",
      "module": "account|hrm|sales|projects|pos|inventory"
    }
  ]
}

WRITING GUIDELINES — make every word count:

1. INSIGHT TITLES:
   - Use professional business language (e.g., "Revenue Declined 15% Month-over-Month", "Strong Cash Position Maintained")
   - Include the metric or trend when relevant (e.g., "Profit Margin Below Industry Average")
   - Avoid vague titles like "Good Performance" or "Needs Attention"
   - Keep between 5-8 words

2. INSIGHT DESCRIPTIONS:
   - Lead with the specific number or percentage from the data
   - Explain what it means for the business in plain language
   - Example GOOD: "Monthly revenue dropped to {$currencySymbol}1.2L from {$currencySymbol}1.5L last month, indicating a need to review sales activity and customer retention."
   - Example BAD: "Revenue is low. This is not good for business."

3. RECOMMENDATIONS:
   - Start with a specific verb (Review, Follow up, Reconcile, Allocate, Reduce)
   - Mention exactly what to do and by when if applicable
   - The "reason" should state the business outcome (e.g., "This will improve cash flow and reduce collection delays.")

4. ALERTS:
   - Titles must state the problem directly (e.g., "5 Invoices Overdue by 30+ Days")
   - Messages should explain the risk or consequence clearly
   - Use "warning" for manageable issues, "critical" for urgent risks

RULES:
- Generate 3-5 insights, 2-3 recommendations, 0-2 alerts
- Always reference actual numbers from the metrics — never make up figures
- Prioritize critical and warning items before positive ones
- Skip any module that has no meaningful data or zero values across all fields
- Keep all text concise; business users scan quickly
- Always use the "{$currencySymbol}" symbol ({$defaultCurrency}) for all monetary values — never use any other currency symbol
- ALERTS MUST BE UNIQUE: Never generate duplicate or similar alerts about the same issue. Each alert should address a different critical problem.
- For alerts, ensure each title is distinct and covers different business risks (e.g., don't create two alerts about "Sales Pipeline" with slight wording changes)
PROMPT;
    }

    /**
     * Call OpenAI API.
     */
    private function callOpenAI(string $model, string $apiKey, string $prompt): array
    {
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model'       => $model,
            'messages'    => [
                ['role' => 'system', 'content' => 'You are a business advisor. Respond only with valid JSON.'],
                ['role' => 'user',   'content' => $prompt],
            ],
            'max_tokens'  => 4096,
            'temperature' => 0.3,
        ]);

        if ($response->failed()) {
            Log::error('AIBusinessAdvisor: OpenAI API call failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \Exception('AI API call failed: ' . $response->status());
        }

        $content = $response->json('choices.0.message.content');
        return $this->parseJsonResponse($content);
    }

    /**
     * Call Anthropic (Claude) API.
     */
    private function callAnthropic(string $model, string $apiKey, string $prompt): array
    {
        $response = Http::timeout(120)->withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => $model,
            'max_tokens' => 4096,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if ($response->failed()) {
            Log::error('AIBusinessAdvisor: Anthropic API call failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \Exception('AI API call failed: ' . $response->status());
        }

        $content = $response->json('content.0.text');
        return $this->parseJsonResponse($content);
    }

    /**
     * Call Google Gemini API.
     */
    private function callGoogle(string $model, string $apiKey, string $prompt): array
    {
        // Ensure model name has 'models/' prefix for API
        $modelPath = str_starts_with($model, 'models/') ? $model : 'models/' . $model;
        
        $response = Http::timeout(120)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/{$modelPath}:generateContent?key={$apiKey}", [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 4096],
        ]);

        if ($response->failed()) {
            Log::error('AIBusinessAdvisor: Google API call failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \Exception('AI API call failed: ' . $response->status());
        }

        $content = $response->json('candidates.0.content.parts.0.text');
        
        if (!$content) {
            Log::error('AIBusinessAdvisor: Google API empty content', [
                'response' => $response->json(),
            ]);
            throw new \Exception('AI returned empty content');
        }
        
        return $this->parseJsonResponse($content);
    }

    /**
     * Parse AI response text into JSON array.
     * Strips markdown code blocks if present.
     */
    private function parseJsonResponse(?string $content): array
    {
        if (empty($content)) {
            Log::error('AIBusinessAdvisor: Empty AI response');
            throw new \Exception('AI returned an empty response.');
        }

        $rawContent = $content;

        // Clean the content - decode HTML entities only
        // NOTE: Do NOT use stripslashes() here — it corrupts valid JSON
        // by removing backslashes from escaped quotes inside strings.
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5);
        $content = trim($content);
        
        // Strip markdown code blocks
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);
        $content = trim($content);

        // Try multiple parsing strategies
        $strategies = [
            // Strategy 1: direct decode
            fn () => json_decode($content, true),
            // Strategy 2: fix trailing commas
            fn () => json_decode(preg_replace('/,(\s*[}\]])/', '$1', $content), true),
            // Strategy 3: extract first JSON object/array from text
            fn () => $this->extractJsonFromText($content),
            // Strategy 4: fix common AI issues (comments, single quotes)
            fn () => json_decode($this->fixCommonJsonIssues($content), true),
        ];

        foreach ($strategies as $index => $strategy) {
            try {
                $decoded = $strategy();
                $error = json_last_error();

                if ($error === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            } catch (\Throwable $e) {
                // Continue to next strategy
            }
        }

        // Detect if response was truncated
        $lastChars = substr($content, -100);
        $isTruncated = !str_ends_with(trim($content), '}') && !str_ends_with(trim($content), ']');
        $contentLength = strlen($content);

        Log::error('AIBusinessAdvisor: Failed to parse AI JSON response', [
            'raw_content_first_1000' => substr($rawContent, 0, 1000),
            'raw_content_last_500'   => substr($rawContent, -500),
            'cleaned_first_1000'     => substr($content, 0, 1000),
            'cleaned_last_500'       => substr($content, -500),
            'content_length'         => $contentLength,
            'is_truncated'           => $isTruncated,
            'last_100_chars'         => $lastChars,
        ]);

        if ($isTruncated) {
            throw new \Exception("AI response appears truncated (length: {$contentLength}, doesn't end with } or ]). Try increasing max_tokens or reducing prompt size.");
        }

        throw new \Exception('AI returned invalid JSON. Check logs for full response. Length: ' . $contentLength . ' Last 100 chars: ' . $lastChars);
    }

    /**
     * Try to extract JSON object/array from mixed text content.
     */
    private function extractJsonFromText(string $text): ?array
    {
        // Try to find the first { ... } or [ ... ] block
        if (preg_match('/(\{.*\})/s', $text, $matches)) {
            $decoded = json_decode($matches[1], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        if (preg_match('/(\[.*\])/s', $text, $matches)) {
            $decoded = json_decode($matches[1], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Fix common JSON issues from AI responses.
     */
    private function fixCommonJsonIssues(string $content): string
    {
        // Remove C-style comments
        $content = preg_replace('/\/\/.*$/m', '', $content);
        $content = preg_replace('/\/\*.*?\*\//s', '', $content);

        // Remove BOM
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Fix single quotes used instead of double quotes (basic cases)
        $content = preg_replace("/'([^']*)':\s*'([^']*)'/", '"$1": "$2"', $content);

        // Fix single-quoted keys
        $content = preg_replace("/'([^']*)':\s*/", '"$1": ', $content);

        // Fix trailing commas before closing brackets
        $content = preg_replace('/,(\s*[}\]])/', '$1', $content);

        return trim($content);
    }
}