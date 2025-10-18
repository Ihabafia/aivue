<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">
                    AI Blog Post Evaluator
                </h1>
                <p class="text-lg text-gray-600">
                    Evaluate your blog post using 5 different AI LLMs across 30 quality metrics
                </p>
            </div>

            <!-- Provider Status -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">AI Provider Status</h2>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div
                        v-for="(status, provider) in providers"
                        :key="provider"
                        class="flex items-center space-x-2"
                    >
                        <div
                            :class="[
                                'w-3 h-3 rounded-full',
                                status.enabled && status.configured
                                    ? 'bg-green-500'
                                    : 'bg-red-500'
                            ]"
                        ></div>
                        <span class="text-sm font-medium capitalize">{{ provider }}</span>
                    </div>
                </div>
            </div>

            <!-- Input Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Submit Your Blog Post</h2>
                
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Blog Post Title (Optional)
                    </label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter your blog post title..."
                    />
                </div>

                <div class="mb-4">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                        Blog Post Content *
                    </label>
                    <textarea
                        id="content"
                        v-model="form.content"
                        rows="12"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Paste your blog post content here (minimum 100 characters)..."
                    ></textarea>
                    <p class="text-sm text-gray-500 mt-1">
                        Characters: {{ form.content.length }} / 100 minimum
                    </p>
                </div>

                <button
                    @click="evaluateBlogPost"
                    :disabled="isEvaluating || form.content.length < 100"
                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-md font-semibold hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                >
                    <span v-if="isEvaluating">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Evaluating... This may take a minute
                    </span>
                    <span v-else>Evaluate Blog Post</span>
                </button>

                <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-red-800">{{ error }}</p>
                </div>
            </div>

            <!-- Results -->
            <div v-if="results" class="space-y-6">
                <!-- Summary Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold mb-4">Evaluation Summary</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-4xl font-bold text-blue-600">
                                {{ results.average_overall_score }}/10
                            </div>
                            <div class="text-sm text-gray-600 mt-1">Overall Score</div>
                        </div>
                        
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-4xl font-bold text-green-600">
                                {{ results.providers_used }}
                            </div>
                            <div class="text-sm text-gray-600 mt-1">AI Providers Used</div>
                        </div>
                        
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <div class="text-4xl font-bold text-purple-600">
                                {{ questions.length }}
                            </div>
                            <div class="text-sm text-gray-600 mt-1">Questions Evaluated</div>
                        </div>
                    </div>

                    <div class="prose max-w-none">
                        <p class="text-gray-700">{{ results.summary }}</p>
                    </div>
                </div>

                <!-- Consensus Issues -->
                <div v-if="results.consensus_issues.length > 0" class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4 text-red-600">
                        Key Issues (Identified by Multiple AIs)
                    </h3>
                    <ul class="space-y-2">
                        <li
                            v-for="(issue, index) in results.consensus_issues"
                            :key="index"
                            class="flex items-start"
                        >
                            <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">{{ issue }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Consensus Strengths -->
                <div v-if="results.consensus_strengths.length > 0" class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4 text-green-600">
                        Strengths (Identified by Multiple AIs)
                    </h3>
                    <ul class="space-y-2">
                        <li
                            v-for="(strength, index) in results.consensus_strengths"
                            :key="index"
                            class="flex items-start"
                        >
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">{{ strength }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Question Scores -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">Detailed Question Analysis</h3>
                    
                    <div class="space-y-4">
                        <div
                            v-for="questionScore in results.question_scores"
                            :key="questionScore.question_number"
                            class="border-b border-gray-200 pb-4 last:border-b-0"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-sm font-medium text-gray-900 flex-1">
                                    {{ questionScore.question_number }}. {{ questionScore.question }}
                                </h4>
                                <div
                                    :class="[
                                        'ml-4 px-3 py-1 rounded-full text-sm font-semibold',
                                        questionScore.average_score >= 8 ? 'bg-green-100 text-green-800' :
                                        questionScore.average_score >= 6 ? 'bg-yellow-100 text-yellow-800' :
                                        'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ questionScore.average_score }}/10
                                </div>
                            </div>
                            
                            <div class="mt-2 space-y-1">
                                <div
                                    v-for="providerScore in questionScore.provider_scores"
                                    :key="providerScore.provider"
                                    class="text-xs text-gray-600 pl-4"
                                >
                                    <span class="font-medium capitalize">{{ providerScore.provider }}:</span>
                                    <span class="ml-1">{{ providerScore.score }}/10 - {{ providerScore.explanation }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Individual Provider Results -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">Individual Provider Results</h3>
                    
                    <div class="space-y-4">
                        <div
                            v-for="(result, provider) in results.individual_results"
                            :key="provider"
                            class="border border-gray-200 rounded-lg p-4"
                        >
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-semibold capitalize">{{ provider }}</h4>
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-xs font-semibold',
                                        result.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ result.success ? 'Success' : 'Failed' }}
                                </span>
                            </div>
                            
                            <div v-if="result.success" class="text-sm text-gray-700">
                                <p><strong>Score:</strong> {{ result.overall_score }}/10</p>
                                <p class="mt-2"><strong>Summary:</strong> {{ result.summary }}</p>
                            </div>
                            
                            <div v-else class="text-sm text-red-600">
                                <p>{{ result.error }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    questions: Array,
    providers: Object,
});

const form = ref({
    title: '',
    content: '',
});

const isEvaluating = ref(false);
const results = ref(null);
const error = ref(null);

const evaluateBlogPost = async () => {
    if (form.value.content.length < 100) {
        error.value = 'Please enter at least 100 characters of content.';
        return;
    }

    isEvaluating.value = true;
    error.value = null;
    results.value = null;

    try {
        const response = await fetch('/api/blog-evaluator/evaluate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                title: form.value.title,
                content: form.value.content,
            }),
        });

        const data = await response.json();

        if (data.success) {
            results.value = data;
        } else {
            error.value = data.message || 'Evaluation failed. Please try again.';
        }
    } catch (err) {
        error.value = 'An error occurred while evaluating your blog post. Please try again.';
        console.error('Evaluation error:', err);
    } finally {
        isEvaluating.value = false;
    }
};
</script>
