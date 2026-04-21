<?php

namespace Database\Seeders;

use App\Models\Org\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // 50 個虛構公司前綴（中英對照）
        $prefixes = [
            ['天行', 'Tianxing'], ['星河', 'Xinghe'],   ['雲端', 'Yunduan'],   ['晨光', 'Chenguang'], ['北辰', 'Beichen'],
            ['東方', 'Dongfang'], ['南海', 'Nanhai'],   ['西岳', 'Xiyue'],     ['九州', 'Jiuzhou'],   ['光華', 'Guanghua'],
            ['合眾', 'Hezhong'],  ['宏遠', 'Hongyuan'], ['金鼎', 'Jinding'],   ['立成', 'Licheng'],   ['明德', 'Mingde'],
            ['凱旋', 'Kaixuan'],  ['全昇', 'Quansheng'],['瑞峰', 'Ruifeng'],   ['勝利', 'Shengli'],   ['泰昌', 'Taichang'],
            ['通達', 'Tongda'],   ['興農', 'Xingnong'], ['遠東', 'Yuandong'],  ['長青', 'Changqing'], ['正大', 'Zhengda'],
            ['德盛', 'Desheng'],  ['永信', 'Yongxin'],  ['華新', 'Huaxin'],    ['建興', 'Jianxing'],  ['和興', 'Hexing'],
            ['久大', 'Jiuda'],    ['美達', 'Meida'],    ['英華', 'Yinghua'],   ['立群', 'Liqun'],     ['元亨', 'Yuanheng'],
            ['利達', 'Lida'],     ['貞昌', 'Zhenchang'],['寶華', 'Baohua'],    ['豐興', 'Fengxing'],  ['慶豐', 'Qingfeng'],
            ['億盛', 'Yisheng'],  ['嘉華', 'Jiahua'],   ['聯成', 'Liancheng'], ['齊豐', 'Qifeng'],    ['宏業', 'Hongye'],
            ['銀河', 'Yinhe'],    ['蒼穹', 'Cangqiong'],['藍海', 'Lanhai'],    ['千里', 'Qianli'],    ['萬象', 'Wanxiang'],
        ];

        // 10 個業別後綴（中英對照）
        $suffixes = [
            ['科技', 'Tech'],       ['集團', 'Group'],         ['工業', 'Industry'], ['電子', 'Electronics'], ['實業', 'Enterprise'],
            ['控股', 'Holdings'],   ['資訊', 'Information'],   ['投控', 'Holding'],  ['建設', 'Construction'],['光電', 'Optics'],
        ];

        // 縣市/區（隨機池）
        $states = ['台北市', '新北市', '桃園市', '台中市', '台南市', '高雄市', '新竹市', '新竹縣', '基隆市', '宜蘭縣'];
        $cities = ['中正區', '大同區', '中山區', '松山區', '大安區', '信義區', '北投區', '士林區', '內湖區', '南港區'];
        $streets = ['和平東路', '建國南路', '忠孝東路', '中山北路', '南京東路', '民權東路', '光復路', '文化路', '中華路', '復興路'];

        // 固定亂數種子，確保每次 seed 結果一致
        mt_srand(20260421);

        foreach ($prefixes as $i => [$zhPrefix, $enPrefix]) {
            [$zhSuffix, $enSuffix] = $suffixes[$i % count($suffixes)];

            $shortZh = $zhPrefix . $zhSuffix;
            $fullZh  = $shortZh . '股份有限公司';
            $shortEn = $enPrefix . ' ' . $enSuffix;
            $fullEn  = $shortEn . ' Co., Ltd.';

            $orgData = [
                'business_no'       => str_pad((string) mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'shipping_state'    => $states[mt_rand(0, count($states) - 1)],
                'shipping_city'     => $cities[mt_rand(0, count($cities) - 1)],
                'shipping_address1' => $streets[mt_rand(0, count($streets) - 1)] . mt_rand(1, 500) . '號',
            ];

            $org = Organization::create($orgData);
            $org->saveTranslations([
                'zh_Hant' => ['name' => $fullZh, 'short_name' => $shortZh],
                'en'      => ['name' => $fullEn, 'short_name' => $shortEn],
            ]);
        }
    }
}
