<?php
// Activity logging

declare(strict_types=1);

require_once __DIR__ . '/db.php';

const ACTIVITY_COLLECTION = 'activity';

function add_activity(string $description, string $type): void
{
    $entry = [
        'id' => JsonDB::nextId(),
        'description' => $description,
        'type' => $type,
        'timestamp' => date('c'),
    ];
    JsonDB::upsert(ACTIVITY_COLLECTION, function(array $items) use ($entry) {
        array_unshift($items, $entry);
        if (count($items) > 200) {
            $items = array_slice($items, 0, 200);
        }
        return $items;
    });
}

function get_activity(int $limit = 20): array
{
    $items = JsonDB::read(ACTIVITY_COLLECTION);
    return array_slice($items, 0, $limit);
}
