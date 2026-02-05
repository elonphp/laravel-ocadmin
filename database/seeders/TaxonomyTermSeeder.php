<?php

namespace Database\Seeders;

use App\Models\Config\Taxonomy;
use App\Models\Config\Term;
use Illuminate\Database\Seeder;

class TaxonomyTermSeeder extends Seeder
{
    public function run(): void
    {
        $taxonomy = Taxonomy::create([
            'code' => 'skill',
            'description' => '員工技能分類',
            'sort_order' => 0,
        ]);

        $taxonomy->saveTranslations([
            'zh_Hant' => ['name' => '技能'],
            'en' => ['name' => 'Skills'],
        ]);

        $terms = [
            'programming' => [
                'zh_Hant' => '程式開發',
                'en' => 'Programming',
                'children' => [
                    'php' => ['zh_Hant' => 'PHP', 'en' => 'PHP'],
                    'javascript' => ['zh_Hant' => 'JavaScript', 'en' => 'JavaScript'],
                    'python' => ['zh_Hant' => 'Python', 'en' => 'Python'],
                ],
            ],
            'language' => [
                'zh_Hant' => '語言能力',
                'en' => 'Languages',
                'children' => [
                    'english' => ['zh_Hant' => '英語', 'en' => 'English'],
                    'japanese' => ['zh_Hant' => '日語', 'en' => 'Japanese'],
                ],
            ],
            'office' => [
                'zh_Hant' => '辦公軟體',
                'en' => 'Office Software',
                'children' => [
                    'excel' => ['zh_Hant' => 'Excel', 'en' => 'Excel'],
                    'word' => ['zh_Hant' => 'Word', 'en' => 'Word'],
                ],
            ],
        ];

        $sortOrder = 0;
        foreach ($terms as $code => $data) {
            $parent = Term::create([
                'taxonomy_id' => $taxonomy->id,
                'code' => $code,
                'sort_order' => $sortOrder++,
            ]);

            $parent->saveTranslations([
                'zh_Hant' => ['name' => $data['zh_Hant']],
                'en' => ['name' => $data['en']],
            ]);

            $childOrder = 0;
            foreach ($data['children'] as $childCode => $childNames) {
                $child = Term::create([
                    'taxonomy_id' => $taxonomy->id,
                    'parent_id' => $parent->id,
                    'code' => $childCode,
                    'sort_order' => $childOrder++,
                ]);

                $child->saveTranslations([
                    'zh_Hant' => ['name' => $childNames['zh_Hant']],
                    'en' => ['name' => $childNames['en']],
                ]);
            }
        }
    }
}
