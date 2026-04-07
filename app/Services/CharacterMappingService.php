<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Character Mapping Service
 *
 * Resolves character IDs to character names using umapyoi.net API.
 * Caches mappings for 24 hours to minimize API calls.
 */
class CharacterMappingService
{
    protected const CACHE_KEY = 'umapyoi:character_mapping';

    protected const CACHE_TTL = 86400; // 24 hours

    protected string $baseUrl;

    protected int $timeout;

    public function __construct()
    {
        /** @var string $baseUrl */
        $baseUrl = config('services.umapyoi.url', 'https://www.umapyoi.net');
        $this->baseUrl = \is_string($baseUrl) ? $baseUrl : 'https://www.umapyoi.net';

        /** @var int $timeout */
        $timeout = config('services.umapyoi.timeout', 30);
        $this->timeout = \is_int($timeout) ? $timeout : 30;
    }

    /**
     * Get character name by ID
     */
    public function getCharacterName(int $charaId): string
    {
        $mapping = $this->getCharacterMapping();

        return $mapping[$charaId] ?? $this->extractNameFromId($charaId);
    }

    /**
     * Get full character mapping (chara_id => name)
     *
     * @return array<int, string>
     */
    public function getCharacterMapping(): array
    {
        /** @var array<int, string>|null $cached */
        $cached = Cache::get(self::CACHE_KEY);

        if ($cached !== null) {
            return $cached;
        }

        $mapping = $this->fetchCharacterMapping();
        Cache::put(self::CACHE_KEY, $mapping, self::CACHE_TTL);

        return $mapping;
    }

    /**
     * Fetch character mapping from API
     *
     * @return array<int, string>
     */
    protected function fetchCharacterMapping(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/v1/chara");

            if (! $response->successful()) {
                Log::warning('[CharacterMappingService] API request failed', [
                    'status' => $response->status(),
                ]);

                return $this->getStaticMapping();
            }

            $characters = $response->json();
            if (! \is_array($characters)) {
                return $this->getStaticMapping();
            }

            $mapping = [];
            foreach ($characters as $char) {
                if (! \is_array($char)) {
                    continue;
                }
                $id = $char['id'] ?? $char['game_id'] ?? null;
                $name = $char['name_en'] ?? $char['name'] ?? null;

                if ($id !== null && is_numeric($id) && is_string($name) && $name !== '') {
                    $mapping[(int) $id] = $name;
                }
            }

            // Merge with static mapping for completeness
            return array_replace($this->getStaticMapping(), $mapping);
        } catch (\Exception $e) {
            Log::error('[CharacterMappingService] Failed to fetch character mapping', [
                'error' => $e->getMessage(),
            ]);

            return $this->getStaticMapping();
        }
    }

    /**
     * Extract character name from gametora ID
     */
    public function extractNameFromGametoraId(string $gametoraId): string
    {
        // Pattern: "30028-kitasan-black" → "Kitasan Black"
        $parts = explode('-', $gametoraId);
        array_shift($parts); // Remove numeric prefix

        if (empty($parts)) {
            return 'Unknown';
        }

        return ucwords(implode(' ', $parts));
    }

    /**
     * Extract name from chara_id using static mapping
     */
    protected function extractNameFromId(int $charaId): string
    {
        $mapping = $this->getStaticMapping();

        return $mapping[$charaId] ?? "Character #{$charaId}";
    }

    /**
     * Static character mapping for offline/fallback use
     *
     * @return array<int, string>
     */
    protected function getStaticMapping(): array
    {
        return [
            // Main characters
            1001 => 'Special Week',
            1002 => 'Silence Suzuka',
            1003 => 'Tokai Teio',
            1004 => 'Maruzensky',
            1005 => 'Fuji Kiseki',
            1006 => 'Oguri Cap',
            1007 => 'Gold Ship',
            1008 => 'Vodka',
            1009 => 'Daiwa Scarlet',
            1010 => 'Taiki Shuttle',
            1011 => 'Grass Wonder',
            1012 => 'Hishi Amazon',
            1013 => 'Mejiro McQueen',
            1014 => 'El Condor Pasa',
            1015 => 'T.M. Opera O',
            1016 => 'Narita Brian',
            1017 => 'Symboli Rudolf',
            1018 => 'Air Groove',
            1019 => 'Agnes Digital',
            1020 => 'Seiun Sky',
            1021 => 'Tamamo Cross',
            1022 => 'Fine Motion',
            1023 => 'Biwa Hayahide',
            1024 => 'Mayano Top Gun',
            1025 => 'Manhattan Cafe',
            1026 => 'Mihono Bourbon',
            1027 => 'Mejiro Ryan',
            1028 => 'Hishi Akebono',
            1029 => 'Yukino Bijin',
            1030 => 'Rice Shower',
            1031 => 'Ines Fujin',
            1032 => 'Agnes Tachyon',
            1033 => 'Admire Vega',
            1034 => 'Inari One',
            1035 => 'Winning Ticket',
            1036 => 'Air Shakur',
            1037 => 'Eishin Flash',
            1038 => 'Curren Chan',
            1039 => 'Kawakami Princess',
            1040 => 'Gold City',
            1041 => 'Sakura Bakushin O',
            1042 => 'Seeking the Pearl',
            1043 => 'Shinko Windy',
            1044 => 'Sweep Tosho',
            1045 => 'Super Creek',
            1046 => 'Smart Falcon',
            1047 => 'Zenno Rob Roy',
            1048 => 'Tosen Jordan',
            1049 => 'Nakayama Festa',
            1050 => 'Narita Taishin',
            1051 => 'Nishino Flower',
            1052 => 'Haru Urara',
            1053 => 'Bamboo Memory',
            1054 => 'Biko Pegasus',
            1055 => 'Marvelous Sunday',
            1056 => 'Matikanefukukitaru',
            1057 => 'Mr. C.B.',
            1058 => 'Meisho Doto',
            1059 => 'Mejiro Dober',
            1060 => 'Nice Nature',
            1061 => 'King Halo',
            1062 => 'Matikanetannhauser',
            1063 => 'Ikuno Dictus',
            1064 => 'Mejiro Palmer',
            1065 => 'Daitaku Helios',
            1066 => 'Twin Turbo',
            1067 => 'Satono Diamond',
            1068 => 'Kitasan Black',
            1069 => 'Sakura Chiyono O',
            1070 => 'Sirius Symboli',
            1071 => 'Mejiro Ardan',
            1072 => 'Yaeno Muteki',
            1073 => 'Tsurumaru Tsuyoshi',
            1074 => 'Mejiro Bright',
            1075 => 'Daring Tact',
            1076 => 'Sakura Laurel',
            1077 => 'Narita Top Road',
            1078 => 'Yamanin Zephyr',
            1080 => 'Transcend',
            1081 => 'Espoir City',
            1082 => 'North Flight',
            1083 => 'Symboli Kris S',
            1084 => 'Tanino Gimlet',
            1085 => 'Daiichi Ruby',
            1086 => 'Mejiro Ramonu',
            1087 => 'Aston Machan',
            1089 => 'Cheval Grand',
            1090 => 'Verxina',
            1091 => 'Vivlos',
            1092 => 'Dantsu Flame',
            1093 => 'K.S. Miracle',
            1094 => 'Jungle Pocket',
            1096 => 'No Reason',
            1097 => 'Still in Love',
            1098 => 'Copano Rickey',
            1099 => 'Hokko Tarumae',
            1100 => 'Wonder Acute',
            1102 => 'Sounds of Earth',
            1103 => 'Royce and Royce',
            1104 => 'Katsuragi Ace',
            1105 => 'Neo Universe',
            1106 => 'Hishi Miracle',
            1107 => 'Tap Dance City',
            1108 => 'Duramente',
            1109 => 'Rhein Kraft',
            1110 => 'Cesario',
            1111 => 'Air Messiah',
            1112 => 'Daring Heart',
            1113 => 'Fusaichi Pandora',
            1114 => 'Buena Vista',
            1115 => 'Orfevre',
            1116 => 'Gentildonna',
            1117 => 'Win Variation',
            1118 => 'Admire Groove',
            1119 => 'Dream Journey',
            1120 => 'Calstone Light O',
            1121 => 'Durandal',
            1124 => 'Bubble Gum Fellow',
            1128 => 'Blast Onepiece',
            1129 => 'Almond Eye',
            1130 => 'Lucky Lilac',
            1131 => 'Gran Alegria',
            1133 => 'Chrono Genesis',
            1134 => 'Curren Bouquetdor',
            1135 => 'Stay Gold',

            // Support characters (trainers, staff)
            9001 => 'Tazuna Hayakawa',
            9002 => 'Yayoi Akikawa',
            9004 => 'Aoi Kiryuin',
            9005 => 'Sasami Anshinzawa',
            9006 => 'Riko Kashimoto',
            9008 => 'Light Hello',
            9040 => 'Ancestors Guides',
            9043 => 'Mei Satake',
            9044 => 'Ryoka Tsurugi',
            9047 => 'Group',
            9049 => 'Tucker Bryne',
        ];
    }

    /**
     * Clear the character mapping cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Check if a character ID is a support/trainer character
     */
    public function isSupportCharacter(int $charaId): bool
    {
        return $charaId >= 9000;
    }
}
