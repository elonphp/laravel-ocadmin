<?php

namespace Database\Seeders;

use App\Models\Catalog\Option;
use App\Models\Catalog\OptionValue;
use App\Models\Catalog\OptionValueGroup;
use App\Models\Catalog\OptionValueLink;
use Illuminate\Database\Seeder;

class OptionValueGroupSeeder extends Seeder
{
    public function run(): void
    {
        // ========================================
        // 汽車規格群組 (car_specs) — 3 層
        // ========================================
        $carGroup = OptionValueGroup::create([
            'code' => 'car_specs',
            'sort_order' => 0,
            'is_active' => true,
        ]);
        $carGroup->saveTranslations([
            'zh_Hant' => ['name' => '汽車規格', 'description' => '廠牌 → 車型 → 車款'],
            'en' => ['name' => 'Car Specs', 'description' => 'Brand → Model → Trim'],
        ]);

        $brandOption = Option::where('code', 'brand')->first();
        $modelOption = Option::where('code', 'model')->first();
        $trimOption = Option::where('code', 'trim')->first();

        // 層級：brand(0) → model(1) → trim(2)
        $carGroup->levels()->create(['option_id' => $brandOption->id, 'level' => 0]);
        $carGroup->levels()->create(['option_id' => $modelOption->id, 'level' => 1]);
        $carGroup->levels()->create(['option_id' => $trimOption->id, 'level' => 2]);

        // 取得 option values
        $v = fn(string $code) => OptionValue::where('code', $code)->first();

        $toyota = $v('toyota');
        $honda = $v('honda');
        $ford = $v('ford');
        $altis = $v('altis');
        $yaris = $v('yaris');
        $civic = $v('civic');
        $fit = $v('fit');
        $focus = $v('focus');
        $kuga = $v('kuga');
        $flagship = $v('flagship');
        $luxury = $v('luxury');
        $classic = $v('classic');

        // 廠牌 → 車型 連動
        $this->link($toyota, [$altis, $yaris]);
        $this->link($honda, [$civic, $fit]);
        $this->link($ford, [$focus, $kuga]);

        // 車型 → 車款 連動
        $this->link($altis, [$flagship, $luxury]);
        $this->link($yaris, [$luxury, $classic]);
        $this->link($civic, [$flagship, $classic]);
        $this->link($fit, [$classic]);
        $this->link($focus, [$flagship, $luxury]);
        $this->link($kuga, [$flagship]);

        // ========================================
        // 窗簾配置群組 (curtain_config) — 3 層
        // ========================================
        $curtainGroup = OptionValueGroup::create([
            'code' => 'curtain_config',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $curtainGroup->saveTranslations([
            'zh_Hant' => ['name' => '窗簾配置', 'description' => '材質 → 框型 → 顏色'],
            'en' => ['name' => 'Curtain Config', 'description' => 'Material → Frame → Color'],
        ]);

        $materialOption = Option::where('code', 'material')->first();
        $frameOption = Option::where('code', 'frame')->first();
        $colorOption = Option::where('code', 'color')->first();

        // 層級：material(0) → frame(1) → color(2)
        $curtainGroup->levels()->create(['option_id' => $materialOption->id, 'level' => 0]);
        $curtainGroup->levels()->create(['option_id' => $frameOption->id, 'level' => 1]);
        $curtainGroup->levels()->create(['option_id' => $colorOption->id, 'level' => 2]);

        $wood = $v('wood');
        $aluminum = $v('aluminum');
        $woodBlind = $v('wood_blind');
        $woodRoller = $v('wood_roller');
        $aluBlind = $v('alu_blind');
        $aluRoller = $v('alu_roller');
        $woodGrain = $v('wood_grain');
        $walnut = $v('walnut');
        $white = $v('white');
        $silver = $v('silver');
        $black = $v('black');

        // 材質 → 框型 連動
        $this->link($wood, [$woodBlind, $woodRoller]);
        $this->link($aluminum, [$aluBlind, $aluRoller]);

        // 框型 → 顏色 連動
        $this->link($woodBlind, [$woodGrain, $walnut]);
        $this->link($woodRoller, [$woodGrain]);
        $this->link($aluBlind, [$white, $silver, $black]);
        $this->link($aluRoller, [$white, $silver]);
    }

    /**
     * 建立父值 → 子值連動
     */
    protected function link(OptionValue $parent, array $children): void
    {
        foreach ($children as $child) {
            OptionValueLink::create([
                'parent_option_value_id' => $parent->id,
                'child_option_value_id' => $child->id,
            ]);
        }
    }
}
