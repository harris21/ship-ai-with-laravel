<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Laravel\Ai\Files\Document;
use Laravel\Ai\Stores;

#[Signature('kb:setup-store')]
#[Description('Upload support documents and create a vector store with the AI provider')]
class SetupVectorStore extends Command
{
    public function handle(): void
    {
        $this->info('Creating vector store...');

        $store = Stores::create(
            name: 'SupportAI Knowledge Base',
            description: 'Customer support documentation, policies, and procedures',
        );

        $this->info("Vector store created: {$store->id}");

        $documents = [
            'docs/return-policy.md',
            'docs/shipping-guide.md',
            'docs/billing-faq.md',
            'docs/account-security.md',
            'docs/product-warranty.md',
        ];

        $bar = $this->output->createProgressBar(count($documents));

        foreach ($documents as $path) {
            $store->add(
                Document::fromStorage($path)
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('All documents uploaded and indexed!');
        $this->info("Store ID: {$store->id}");
        $this->warn('Save this store ID in your .env file as VECTOR_STORE_ID');
    }
}
