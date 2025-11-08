<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hash Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for generating short URL hashes.
    |
    */

    'hash_length' => env('HASH_LENGTH', 6),

    'note_hash_length' => env('NOTE_HASH_LENGTH', 8),

    /*
    |--------------------------------------------------------------------------
    | URL Validation
    |--------------------------------------------------------------------------
    |
    | Validation rules for URLs.
    |
    */

    'max_url_length' => env('MAX_URL_LENGTH', 2048),

    /*
    |--------------------------------------------------------------------------
    | Note Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for notes/pastebin feature.
    |
    */

    'max_note_size' => env('MAX_NOTE_SIZE', 10485760), // 10MB

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache TTL for links and other data.
    |
    */

    'default_cache_ttl' => env('CACHE_TTL', 86400), // 24 hours

    /*
    |--------------------------------------------------------------------------
    | Excluded Words
    |--------------------------------------------------------------------------
    |
    | Words to exclude from hash generation to prevent offensive or
    | confusing short URLs. All lowercase.
    |
    */

    'excluded_words' => [
        // Common profanity
        'fuck', 'shit', 'damn', 'hell', 'bitch', 'ass', 'piss', 'cock',
        'cunt', 'dick', 'pussy', 'tits', 'fag', 'dyke', 'slut', 'whore',

        // Potentially confusing
        'anal', 'nazi', 'rape', 'kill', 'dead', 'die', 'sex', 'porn',
        'xxx', 'drug', 'meth', 'isis', 'klan',

        // Common words that might be confusing
        'login', 'admin', 'root', 'api', 'www', 'mail', 'ftp', 'ssh',
        'test', 'demo', 'dev', 'staging', 'prod',

        // Slurs and offensive terms
        'nigger', 'chink', 'spic', 'kike', 'wetback', 'gook', 'towel',

        // Additional offensive combinations
        'asshole', 'jackass', 'dumbass', 'fatass', 'badass',

        'assbag',
        'asshat',
        'asswad',
        'bampot',
        'beaner',
        'biatch',
        'bimbos',
        'bloody',
        'bollok',
        'bollox',
        'boobie',
        'booobs',
        'buceta',
        'bugger',
        'coochy',
        'cooter',
        'cummer',
        'cunnie',
        'cyalis',
        'dammit',
        'darkie',
        'dildos',
        'doggin',
        'dommes',
        'dookie',
        'douche',
        'erotic',
        'escort',
        'eunuch',
        'fagbag',
        'faggit',
        'faggot',
        'fagots',
        'farted',
        'fatass',
        'fcuker',
        'fecker',
        'feltch',
        'femdom',
        'flamer',
        'flange',
        'fooker',
        'fucked',
        'fucker',
        'fuckin',
        'fukker',
        'fukkin',
        'fukwit',
        'gayass',
        'gaybob',
        'gaysex',
        'gaywad',
        'goatcx',
        'goatse',
        'gokkun',
        'gringo',
        'hentai',
        'honkey',
        'hooker',
        'hotsex',
        'incest',
        'knobed',
        'kondum',
        'kootch',
        'kummer',
        'l3itch',
        'lezzie',
        'lolita',
        'muther',
        'n1gger',
        'nambla',
        'nigg3r',
        'nigg4h',
        'niggah',
        'niggas',
        'niggaz',
        'nigger',
        'niglet',
        'nipple',
        'nudity',
        'nympho',
        'orgasm',
        'pecker',
        'phuked',
        'pimpis',
        'pising',
        'pissed',
        'pisser',
        'pissin',
        'pornos',
        'punany',
        'pusies',
        'raping',
        'rapist',
        'rectum',
        'retard',
        'rimjaw',
        'rimjob',
        'sadism',
        'sadist',
        'scroat',
        'scrote',
        'shited',
        'shitey',
        'shitty',
        'smegma',
        'snatch',
        'sodomy',
        'spooge',
        'sucker',
        'tosser',
        'tranny',
        'twatty',
        'v14gra',
        'vagina',
        'viagra',
        'voyeur',
        'wanker',
        'xrated',
    ],
];
