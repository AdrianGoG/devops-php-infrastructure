<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Four articles about the project itself, so the blog is never an empty
     * screen at a presentation. updateOrCreate keeps the seeder safe to re-run.
     */
    public function run(): void
    {
        $posts = [
            [
                'slug' => 'nine-applications-three-servers',
                'title' => 'Nine applications, three servers, six PHP versions',
                'excerpt' => 'Why an estate on mixed PHP versions is normal, and what it takes to run one without fear.',
                'body' => "Every application of this infrastructure runs in its own container, with the exact PHP version it was written for.\n\nThat is not sloppiness, it is the situation every team inherits: an estate grows one application at a time, each one pinned to whatever was current when it was written. The interesting question is not how to avoid it, but how to move the whole estate forward without a weekend of downtime.\n\nContainers make the mixed state survivable. The pipeline makes moving forward repeatable.",
                'status' => 'published',
                'author' => 'Adrian G.',
                'published_at' => now()->subDays(21),
            ],
            [
                'slug' => 'what-breaks-when-php-changes',
                'title' => 'What actually breaks when PHP changes major version',
                'excerpt' => 'Three applications, three completely different kinds of failure - and three very different amounts of work.',
                'body' => "app-crm breaks because of its own source code: curly brace string offsets, a removed function, reversed implode arguments. Three one-line diffs.\n\napp-inventory breaks because of its dependencies. Its code is clean; the framework it runs on never supported the new PHP version. The fix is four major framework upgrades.\n\napp-ticket-system does not break at all. One line in a Dockerfile.\n\nAll three produce the same HTTP 500. Only reading the error tells you which of the three you are looking at.",
                'status' => 'published',
                'author' => 'Adrian G.',
                'published_at' => now()->subDays(9),
            ],
            [
                'slug' => 'a-green-pipeline-is-not-a-clean-upgrade',
                'title' => 'A green pipeline is not the same as a clean upgrade',
                'excerpt' => 'The failures that no test catches are the ones worth designing for.',
                'body' => "Two of the incompatibilities planted in this estate break nothing visible. A constructor silently stops being a constructor. A deprecation quietly fills a log.\n\nThe pipeline stays green. The endpoints stay at 200. And something is still wrong.\n\nThat is why the health endpoints report more than a status code, and why the metrics include a gauge that flips from 1 to 0 when the silent failure happens. A monitoring system that only watches status codes will tell you everything is fine.",
                'status' => 'published',
                'author' => 'Adrian G.',
                'published_at' => now()->subDays(2),
            ],
            [
                'slug' => 'rollback-notes',
                'title' => 'Notes on automatic rollback',
                'excerpt' => 'Draft: what the pipeline should do when the smoke test comes back red.',
                'body' => "Still thinking this through. The smoke test already tells us whether the new version answers 200. The open question is what to keep around so the previous version can come back in seconds rather than minutes.",
                'status' => 'draft',
                'author' => 'Adrian G.',
                'published_at' => null,
            ],
        ];

        foreach ($posts as $post) {
            Post::updateOrCreate(['slug' => $post['slug']], $post);
        }
    }
}