<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class MessageTemplatesSeeder extends Seeder
{
    /**
     * Seed one non-deletable system template per scenario. Idempotent: re-running refreshes
     * bodies/names without duplicating (keyed on the stable `key` slug).
     */
    public function run()
    {
        foreach (MessageTemplate::systemDefaults() as $key => $def) {
            MessageTemplate::updateOrCreate(
                ['key' => $key],
                [
                    'name'      => $def['name'],
                    'body'      => $def['body'],
                    'is_system' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
