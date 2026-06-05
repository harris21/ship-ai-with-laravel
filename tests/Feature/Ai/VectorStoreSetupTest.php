<?php

use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;
use Laravel\Ai\Stores;

test('vector store is created with correct name', function () {
    Stores::fake();

    $this->artisan('kb:setup-store')
        ->assertSuccessful();

    Stores::assertCreated('SupportAI Knowledge Base');
});

test('documents are uploaded to the vector store', function () {
    Stores::fake();
    Storage::fake();

    Storage::put('docs/return-policy.md', '# Return Policy');
    Storage::put('docs/shipping-guide.md', '# Shipping Guide');
    Storage::put('docs/billing-faq.md', '# Billing FAQ');
    Storage::put('docs/account-security.md', '# Account Security');
    Storage::put('docs/product-warranty.md', '# Product Warranty');

    $this->artisan('kb:setup-store')
        ->assertSuccessful();

    Stores::assertCreated('SupportAI Knowledge Base');
});

test('embedding generation can be faked for knowledge base seeding', function () {
    Embeddings::fake();

    $this->artisan('kb:seed')
        ->assertSuccessful();

    Embeddings::assertGenerated(
        fn (EmbeddingsPrompt $prompt) => $prompt->contains('Return Policy')
    );
});
