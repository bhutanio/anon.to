<?php

namespace Database\Seeders;

use App\Models\AllowList;
use Illuminate\Database\Seeder;

class AllowListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blockedDomains = [
            // Common spam domains
            ['domain' => 'bit.ly', 'reason' => 'Commonly used for spam', 'pattern_type' => 'exact'],
            ['domain' => 'tinyurl.com', 'reason' => 'Commonly used for spam', 'pattern_type' => 'exact'],
            ['domain' => '*.tk', 'reason' => 'Free TLD often used for spam', 'pattern_type' => 'wildcard'],
            ['domain' => '*.ml', 'reason' => 'Free TLD often used for spam', 'pattern_type' => 'wildcard'],
            ['domain' => '*.ga', 'reason' => 'Free TLD often used for spam', 'pattern_type' => 'wildcard'],
            ['domain' => '*.cf', 'reason' => 'Free TLD often used for spam', 'pattern_type' => 'wildcard'],
            ['domain' => '*.gq', 'reason' => 'Free TLD often used for spam', 'pattern_type' => 'wildcard'],

            // Known malicious domains (examples)
            ['domain' => 'phishing-example.com', 'reason' => 'Known phishing site', 'pattern_type' => 'exact'],
            ['domain' => 'malware-example.com', 'reason' => 'Known malware distribution', 'pattern_type' => 'exact'],

            // Internal/local addresses (security)
            ['domain' => 'localhost', 'reason' => 'Internal address - SSRF protection', 'pattern_type' => 'exact'],
            ['domain' => '127.0.0.1', 'reason' => 'Loopback address - SSRF protection', 'pattern_type' => 'exact'],
            ['domain' => '192.168.*', 'reason' => 'Private network - SSRF protection', 'pattern_type' => 'wildcard'],
            ['domain' => '10.*', 'reason' => 'Private network - SSRF protection', 'pattern_type' => 'wildcard'],
            ['domain' => '172.16.*', 'reason' => 'Private network - SSRF protection', 'pattern_type' => 'wildcard'],
        ];

        foreach ($blockedDomains as $domain) {
            AllowList::firstOrCreate(
                [
                    'domain' => $domain['domain'],
                    'type' => 'block',
                ],
                [
                    'pattern_type' => $domain['pattern_type'],
                    'reason' => $domain['reason'],
                    'is_active' => true,
                    'hit_count' => 0,
                    'added_by' => null,
                ]
            );
        }

        $this->command->info('Allow list seeded with '.count($blockedDomains).' blocked domains.');
    }
}
