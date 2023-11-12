<?php

namespace Database\Seeders;

use App\Models\General;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Crypto\Rsa\KeyPair;

class GeneralKeysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        [$privateKey, $publicKey] = (new KeyPair())->generate();

        General::create([
            'private_key' => $privateKey,
            'public_key' => $publicKey
        ]);
    }
}
