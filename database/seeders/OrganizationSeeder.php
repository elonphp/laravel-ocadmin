<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = [
            // 1. 台積電
            [
                'business_no' => '22099131',
                'shipping_state' => '新竹市',
                'shipping_city' => '東區',
                'shipping_address1' => '新竹科學園區力行六路8號',
                'translations' => [
                    'zh_Hant' => ['name' => '台灣積體電路製造股份有限公司', 'short_name' => '台積電'],
                    'en' => ['name' => 'Taiwan Semiconductor Manufacturing Co., Ltd.', 'short_name' => 'TSMC'],
                ],
            ],
            // 2. 鴻海
            [
                'business_no' => '04541302',
                'shipping_state' => '新北市',
                'shipping_city' => '土城區',
                'shipping_address1' => '自由街2號',
                'translations' => [
                    'zh_Hant' => ['name' => '鴻海精密工業股份有限公司', 'short_name' => '鴻海'],
                    'en' => ['name' => 'Hon Hai Precision Industry Co., Ltd.', 'short_name' => 'Foxconn'],
                ],
            ],
            // 3. 聯發科
            [
                'business_no' => '97168356',
                'shipping_state' => '新竹市',
                'shipping_city' => '東區',
                'shipping_address1' => '新竹科學園區篤行一路1號',
                'translations' => [
                    'zh_Hant' => ['name' => '聯發科技股份有限公司', 'short_name' => '聯發科'],
                    'en' => ['name' => 'MediaTek Inc.', 'short_name' => 'MediaTek'],
                ],
            ],
            // 4. 台達電
            [
                'business_no' => '23024468',
                'shipping_state' => '台北市',
                'shipping_city' => '內湖區',
                'shipping_address1' => '瑞光路186號',
                'translations' => [
                    'zh_Hant' => ['name' => '台達電子工業股份有限公司', 'short_name' => '台達電'],
                    'en' => ['name' => 'Delta Electronics, Inc.', 'short_name' => 'Delta'],
                ],
            ],
            // 5. 中信金
            [
                'business_no' => '80333992',
                'shipping_state' => '台北市',
                'shipping_city' => '南港區',
                'shipping_address1' => '經貿二路168號',
                'translations' => [
                    'zh_Hant' => ['name' => '中國信託金融控股股份有限公司', 'short_name' => '中信金'],
                    'en' => ['name' => 'CTBC Financial Holding Co., Ltd.', 'short_name' => 'CTBC FHC'],
                ],
            ],
            // 6. 日月光投控
            [
                'business_no' => '54401209',
                'shipping_state' => '高雄市',
                'shipping_city' => '楠梓區',
                'shipping_address1' => '經三路26號',
                'translations' => [
                    'zh_Hant' => ['name' => '日月光投資控股股份有限公司', 'short_name' => '日月光投控'],
                    'en' => ['name' => 'ASE Technology Holding Co., Ltd.', 'short_name' => 'ASE'],
                ],
            ],
            // 7. 富邦金
            [
                'business_no' => '84149490',
                'shipping_state' => '台北市',
                'shipping_city' => '松山區',
                'shipping_address1' => '敦化南路一段108號',
                'translations' => [
                    'zh_Hant' => ['name' => '富邦金融控股股份有限公司', 'short_name' => '富邦金'],
                    'en' => ['name' => 'Fubon Financial Holding Co., Ltd.', 'short_name' => 'Fubon FHC'],
                ],
            ],
            // 8. 廣達
            [
                'business_no' => '22555003',
                'shipping_state' => '桃園市',
                'shipping_city' => '龜山區',
                'shipping_address1' => '文化二路211號',
                'translations' => [
                    'zh_Hant' => ['name' => '廣達電腦股份有限公司', 'short_name' => '廣達'],
                    'en' => ['name' => 'Quanta Computer Inc.', 'short_name' => 'Quanta'],
                ],
            ],
            // 9. 國泰金
            [
                'business_no' => '16878678',
                'shipping_state' => '台北市',
                'shipping_city' => '信義區',
                'shipping_address1' => '仁愛路四段296號',
                'translations' => [
                    'zh_Hant' => ['name' => '國泰金融控股股份有限公司', 'short_name' => '國泰金'],
                    'en' => ['name' => 'Cathay Financial Holding Co., Ltd.', 'short_name' => 'Cathay FHC'],
                ],
            ],
            // 10. 智邦
            [
                'business_no' => '23966406',
                'shipping_state' => '新竹市',
                'shipping_city' => '東區',
                'shipping_address1' => '新竹科學園區研新一路1號',
                'translations' => [
                    'zh_Hant' => ['name' => '智邦科技股份有限公司', 'short_name' => '智邦'],
                    'en' => ['name' => 'Accton Technology Corp.', 'short_name' => 'Accton'],
                ],
            ],
            // 11. 聯電
            [
                'business_no' => '23638777',
                'shipping_state' => '新竹市',
                'shipping_city' => '東區',
                'shipping_address1' => '新竹科學園區力行二路3號',
                'translations' => [
                    'zh_Hant' => ['name' => '聯華電子股份有限公司', 'short_name' => '聯電'],
                    'en' => ['name' => 'United Microelectronics Corp.', 'short_name' => 'UMC'],
                ],
            ],
            // 12. 玉山金
            [
                'business_no' => '70790148',
                'shipping_state' => '台北市',
                'shipping_city' => '松山區',
                'shipping_address1' => '民生東路三段36號',
                'translations' => [
                    'zh_Hant' => ['name' => '玉山金融控股股份有限公司', 'short_name' => '玉山金'],
                    'en' => ['name' => 'E.SUN Financial Holding Co., Ltd.', 'short_name' => 'E.SUN FHC'],
                ],
            ],
            // 13. 中華電
            [
                'business_no' => '96979933',
                'shipping_state' => '台北市',
                'shipping_city' => '大安區',
                'shipping_address1' => '信義路一段21之3號',
                'translations' => [
                    'zh_Hant' => ['name' => '中華電信股份有限公司', 'short_name' => '中華電'],
                    'en' => ['name' => 'Chunghwa Telecom Co., Ltd.', 'short_name' => 'CHT'],
                ],
            ],
            // 14. 奇鋐
            [
                'business_no' => '84149961',
                'shipping_state' => '新北市',
                'shipping_city' => '汐止區',
                'shipping_address1' => '中興路100號',
                'translations' => [
                    'zh_Hant' => ['name' => '奇鋐科技股份有限公司', 'short_name' => '奇鋐'],
                    'en' => ['name' => 'Asia Vital Components Co., Ltd.', 'short_name' => 'AVC'],
                ],
            ],
            // 15. 兆豐金
            [
                'business_no' => '16086702',
                'shipping_state' => '台北市',
                'shipping_city' => '中正區',
                'shipping_address1' => '前鎮街38號',
                'translations' => [
                    'zh_Hant' => ['name' => '兆豐金融控股股份有限公司', 'short_name' => '兆豐金'],
                    'en' => ['name' => 'Mega Financial Holding Co., Ltd.', 'short_name' => 'Mega FHC'],
                ],
            ],
            // 16. 緯穎
            [
                'business_no' => '54387502',
                'shipping_state' => '新北市',
                'shipping_city' => '新店區',
                'shipping_address1' => '中正路531號',
                'translations' => [
                    'zh_Hant' => ['name' => '緯穎科技服務股份有限公司', 'short_name' => '緯穎'],
                    'en' => ['name' => 'Wiwynn Corp.', 'short_name' => 'Wiwynn'],
                ],
            ],
            // 17. 台光電
            [
                'business_no' => '30401609',
                'shipping_state' => '桃園市',
                'shipping_city' => '龜山區',
                'shipping_address1' => '樂善里牛角坡2之6號',
                'translations' => [
                    'zh_Hant' => ['name' => '台光電子材料股份有限公司', 'short_name' => '台光電'],
                    'en' => ['name' => 'Elite Material Co., Ltd.', 'short_name' => 'EMC'],
                ],
            ],
            // 18. 緯創
            [
                'business_no' => '13109202',
                'shipping_state' => '新北市',
                'shipping_city' => '汐止區',
                'shipping_address1' => '新台五路一段88號',
                'translations' => [
                    'zh_Hant' => ['name' => '緯創資通股份有限公司', 'short_name' => '緯創'],
                    'en' => ['name' => 'Wistron Corp.', 'short_name' => 'Wistron'],
                ],
            ],
            // 19. 台新新光金
            [
                'business_no' => '70815860',
                'shipping_state' => '台北市',
                'shipping_city' => '中山區',
                'shipping_address1' => '民權東路二段144號',
                'translations' => [
                    'zh_Hant' => ['name' => '台新新光金融控股股份有限公司', 'short_name' => '台新新光金'],
                    'en' => ['name' => 'Taishin Shin Kong Financial Holding Co., Ltd.', 'short_name' => 'TSKFHC'],
                ],
            ],
            // 20. 元大金
            [
                'business_no' => '16846905',
                'shipping_state' => '台北市',
                'shipping_city' => '中山區',
                'shipping_address1' => '南京東路三段225號',
                'translations' => [
                    'zh_Hant' => ['name' => '元大金融控股股份有限公司', 'short_name' => '元大金'],
                    'en' => ['name' => 'Yuanta Financial Holding Co., Ltd.', 'short_name' => 'Yuanta FHC'],
                ],
            ],
            // 21. 統一
            [
                'business_no' => '73251209',
                'shipping_state' => '台南市',
                'shipping_city' => '永康區',
                'shipping_address1' => '中正路301號',
                'translations' => [
                    'zh_Hant' => ['name' => '統一企業股份有限公司', 'short_name' => '統一'],
                    'en' => ['name' => 'Uni-President Enterprises Corp.', 'short_name' => 'Uni-President'],
                ],
            ],
            // 22. 華碩
            [
                'business_no' => '23638606',
                'shipping_state' => '台北市',
                'shipping_city' => '北投區',
                'shipping_address1' => '立德路15號',
                'translations' => [
                    'zh_Hant' => ['name' => '華碩電腦股份有限公司', 'short_name' => '華碩'],
                    'en' => ['name' => 'ASUSTeK Computer Inc.', 'short_name' => 'ASUS'],
                ],
            ],
            // 23. 國巨
            [
                'business_no' => '03094801',
                'shipping_state' => '高雄市',
                'shipping_city' => '楠梓區',
                'shipping_address1' => '加昌路478號',
                'translations' => [
                    'zh_Hant' => ['name' => '國巨股份有限公司', 'short_name' => '國巨'],
                    'en' => ['name' => 'Yageo Corp.', 'short_name' => 'Yageo'],
                ],
            ],
            // 24. 永豐金
            [
                'business_no' => '16888207',
                'shipping_state' => '台北市',
                'shipping_city' => '中正區',
                'shipping_address1' => '博愛路17號',
                'translations' => [
                    'zh_Hant' => ['name' => '永豐金融控股股份有限公司', 'short_name' => '永豐金'],
                    'en' => ['name' => 'SinoPac Financial Holdings Co., Ltd.', 'short_name' => 'SinoPac FHC'],
                ],
            ],
            // 25. 第一金
            [
                'business_no' => '70756128',
                'shipping_state' => '台北市',
                'shipping_city' => '中正區',
                'shipping_address1' => '重慶南路一段30號',
                'translations' => [
                    'zh_Hant' => ['name' => '第一金融控股股份有限公司', 'short_name' => '第一金'],
                    'en' => ['name' => 'First Financial Holding Co., Ltd.', 'short_name' => 'First FHC'],
                ],
            ],
            // 26. 光寶科
            [
                'business_no' => '04250407',
                'shipping_state' => '台北市',
                'shipping_city' => '內湖區',
                'shipping_address1' => '行忠路66號',
                'translations' => [
                    'zh_Hant' => ['name' => '光寶科技股份有限公司', 'short_name' => '光寶科'],
                    'en' => ['name' => 'Lite-On Technology Corp.', 'short_name' => 'Lite-On'],
                ],
            ],
            // 27. 南亞
            [
                'business_no' => '03255506',
                'shipping_state' => '新北市',
                'shipping_city' => '樹林區',
                'shipping_address1' => '東興街14號',
                'translations' => [
                    'zh_Hant' => ['name' => '南亞塑膠工業股份有限公司', 'short_name' => '南亞'],
                    'en' => ['name' => 'Nan Ya Plastics Corp.', 'short_name' => 'Nan Ya'],
                ],
            ],
            // 28. 華南金
            [
                'business_no' => '70767754',
                'shipping_state' => '台北市',
                'shipping_city' => '信義區',
                'shipping_address1' => '松仁路123號',
                'translations' => [
                    'zh_Hant' => ['name' => '華南金融控股股份有限公司', 'short_name' => '華南金'],
                    'en' => ['name' => 'Hua Nan Financial Holdings Co., Ltd.', 'short_name' => 'HNFHC'],
                ],
            ],
            // 29. 致茂
            [
                'business_no' => '35049306',
                'shipping_state' => '桃園市',
                'shipping_city' => '龜山區',
                'shipping_address1' => '華亞科技園區復興三路10號',
                'translations' => [
                    'zh_Hant' => ['name' => '致茂電子股份有限公司', 'short_name' => '致茂'],
                    'en' => ['name' => 'Chroma ATE Inc.', 'short_name' => 'Chroma'],
                ],
            ],
            // 30. 世芯-KY
            [
                'business_no' => '54332681',
                'shipping_state' => '新竹市',
                'shipping_city' => '東區',
                'shipping_address1' => '新竹科學園區創新二路8號',
                'translations' => [
                    'zh_Hant' => ['name' => '世芯電子股份有限公司', 'short_name' => '世芯-KY'],
                    'en' => ['name' => 'Alchip Technologies, Ltd.', 'short_name' => 'Alchip'],
                ],
            ],
            // 31. 凱基金
            [
                'business_no' => '80355482',
                'shipping_state' => '台北市',
                'shipping_city' => '中山區',
                'shipping_address1' => '長安東路二段225號',
                'translations' => [
                    'zh_Hant' => ['name' => '凱基金融控股股份有限公司', 'short_name' => '凱基金'],
                    'en' => ['name' => 'KGI Financial Holding Co., Ltd.', 'short_name' => 'KGI FHC'],
                ],
            ],
            // 32. 貿聯-KY
            [
                'business_no' => '54380166',
                'shipping_state' => '台北市',
                'shipping_city' => '內湖區',
                'shipping_address1' => '瑞光路258巷2號',
                'translations' => [
                    'zh_Hant' => ['name' => '貿聯國際股份有限公司', 'short_name' => '貿聯-KY'],
                    'en' => ['name' => 'BizLink Holding Inc.', 'short_name' => 'BizLink'],
                ],
            ],
            // 33. 合庫金
            [
                'business_no' => '27876927',
                'shipping_state' => '台北市',
                'shipping_city' => '中山區',
                'shipping_address1' => '長安東路二段225號',
                'translations' => [
                    'zh_Hant' => ['name' => '合作金庫金融控股股份有限公司', 'short_name' => '合庫金'],
                    'en' => ['name' => 'Taiwan Cooperative Financial Holding Co., Ltd.', 'short_name' => 'TCB FHC'],
                ],
            ],
            // 34. 瑞昱
            [
                'business_no' => '22671299',
                'shipping_state' => '新竹市',
                'shipping_city' => '東區',
                'shipping_address1' => '新竹科學園區創新二路2號',
                'translations' => [
                    'zh_Hant' => ['name' => '瑞昱半導體股份有限公司', 'short_name' => '瑞昱'],
                    'en' => ['name' => 'Realtek Semiconductor Corp.', 'short_name' => 'Realtek'],
                ],
            ],
            // 35. 大立光
            [
                'business_no' => '22045705',
                'shipping_state' => '台中市',
                'shipping_city' => '南屯區',
                'shipping_address1' => '精科路11號',
                'translations' => [
                    'zh_Hant' => ['name' => '大立光電股份有限公司', 'short_name' => '大立光'],
                    'en' => ['name' => 'Largan Precision Co., Ltd.', 'short_name' => 'Largan'],
                ],
            ],
            // 36. 健策
            [
                'business_no' => '97176270',
                'shipping_state' => '高雄市',
                'shipping_city' => '楠梓區',
                'shipping_address1' => '經二路18號',
                'translations' => [
                    'zh_Hant' => ['name' => '健策精密工業股份有限公司', 'short_name' => '健策'],
                    'en' => ['name' => 'Coretronic Intelligent Robotics Corp.', 'short_name' => 'KENMOS'],
                ],
            ],
            // 37. 中鋼
            [
                'business_no' => '75340852',
                'shipping_state' => '高雄市',
                'shipping_city' => '前鎮區',
                'shipping_address1' => '成功二路88號',
                'translations' => [
                    'zh_Hant' => ['name' => '中國鋼鐵股份有限公司', 'short_name' => '中鋼'],
                    'en' => ['name' => 'China Steel Corp.', 'short_name' => 'CSC'],
                ],
            ],
            // 38. 南亞科
            [
                'business_no' => '84149876',
                'shipping_state' => '新北市',
                'shipping_city' => '泰山區',
                'shipping_address1' => '南林路1號',
                'translations' => [
                    'zh_Hant' => ['name' => '南亞科技股份有限公司', 'short_name' => '南亞科'],
                    'en' => ['name' => 'Nanya Technology Corp.', 'short_name' => 'Nanya Tech'],
                ],
            ],
            // 39. 聯詠
            [
                'business_no' => '84598506',
                'shipping_state' => '新竹市',
                'shipping_city' => '東區',
                'shipping_address1' => '新竹科學園區創新一路12號',
                'translations' => [
                    'zh_Hant' => ['name' => '聯詠科技股份有限公司', 'short_name' => '聯詠'],
                    'en' => ['name' => 'Novatek Microelectronics Corp.', 'short_name' => 'Novatek'],
                ],
            ],
            // 40. 川湖
            [
                'business_no' => '86520218',
                'shipping_state' => '高雄市',
                'shipping_city' => '岡山區',
                'shipping_address1' => '本工六路23號',
                'translations' => [
                    'zh_Hant' => ['name' => '川湖科技股份有限公司', 'short_name' => '川湖'],
                    'en' => ['name' => 'King Slide Works Co., Ltd.', 'short_name' => 'King Slide'],
                ],
            ],
            // 41. 長榮
            [
                'business_no' => '04255703',
                'shipping_state' => '桃園市',
                'shipping_city' => '蘆竹區',
                'shipping_address1' => '新南路一段362號',
                'translations' => [
                    'zh_Hant' => ['name' => '長榮海運股份有限公司', 'short_name' => '長榮'],
                    'en' => ['name' => 'Evergreen Marine Corp.', 'short_name' => 'Evergreen'],
                ],
            ],
            // 42. 台塑
            [
                'business_no' => '03347504',
                'shipping_state' => '台北市',
                'shipping_city' => '松山區',
                'shipping_address1' => '敦化北路201號',
                'translations' => [
                    'zh_Hant' => ['name' => '台灣塑膠工業股份有限公司', 'short_name' => '台塑'],
                    'en' => ['name' => 'Formosa Plastics Corp.', 'short_name' => 'FPC'],
                ],
            ],
            // 43. 和泰車
            [
                'business_no' => '11347802',
                'shipping_state' => '台北市',
                'shipping_city' => '松山區',
                'shipping_address1' => '南京東路四段2號',
                'translations' => [
                    'zh_Hant' => ['name' => '和泰汽車股份有限公司', 'short_name' => '和泰車'],
                    'en' => ['name' => 'Hotai Motor Co., Ltd.', 'short_name' => 'Hotai'],
                ],
            ],
            // 44. 康霈
            [
                'business_no' => '90335617',
                'shipping_state' => '新竹縣',
                'shipping_city' => '竹北市',
                'shipping_address1' => '生醫路二段22號',
                'translations' => [
                    'zh_Hant' => ['name' => '康霈生技股份有限公司', 'short_name' => '康霈'],
                    'en' => ['name' => 'Candel Therapeutics, Inc.', 'short_name' => 'Candel'],
                ],
            ],
            // 45. 台灣大
            [
                'business_no' => '97176269',
                'shipping_state' => '台北市',
                'shipping_city' => '大安區',
                'shipping_address1' => '敦化南路二段172號',
                'translations' => [
                    'zh_Hant' => ['name' => '台灣大哥大股份有限公司', 'short_name' => '台灣大'],
                    'en' => ['name' => 'Taiwan Mobile Co., Ltd.', 'short_name' => 'TWM'],
                ],
            ],
            // 46. 遠傳
            [
                'business_no' => '97178633',
                'shipping_state' => '台北市',
                'shipping_city' => '內湖區',
                'shipping_address1' => '內湖路一段399號',
                'translations' => [
                    'zh_Hant' => ['name' => '遠傳電信股份有限公司', 'short_name' => '遠傳'],
                    'en' => ['name' => 'Far EasTone Telecommunications Co., Ltd.', 'short_name' => 'FET'],
                ],
            ],
            // 47. 研華
            [
                'business_no' => '05765285',
                'shipping_state' => '台北市',
                'shipping_city' => '內湖區',
                'shipping_address1' => '瑞光路26巷20弄1號',
                'translations' => [
                    'zh_Hant' => ['name' => '研華股份有限公司', 'short_name' => '研華'],
                    'en' => ['name' => 'Advantech Co., Ltd.', 'short_name' => 'Advantech'],
                ],
            ],
            // 48. 統一超
            [
                'business_no' => '22555003',
                'shipping_state' => '台北市',
                'shipping_city' => '松山區',
                'shipping_address1' => '東興路8號',
                'translations' => [
                    'zh_Hant' => ['name' => '統一超商股份有限公司', 'short_name' => '統一超'],
                    'en' => ['name' => 'President Chain Store Corp.', 'short_name' => 'PCSC'],
                ],
            ],
            // 49. 萬海
            [
                'business_no' => '03553901',
                'shipping_state' => '台北市',
                'shipping_city' => '中山區',
                'shipping_address1' => '松江路136號',
                'translations' => [
                    'zh_Hant' => ['name' => '萬海航運股份有限公司', 'short_name' => '萬海'],
                    'en' => ['name' => 'Wan Hai Lines Ltd.', 'short_name' => 'Wan Hai'],
                ],
            ],
            // 50. 台塑化
            [
                'business_no' => '86927929',
                'shipping_state' => '雲林縣',
                'shipping_city' => '麥寮鄉',
                'shipping_address1' => '台塑工業園區1號',
                'translations' => [
                    'zh_Hant' => ['name' => '台塑石化股份有限公司', 'short_name' => '台塑化'],
                    'en' => ['name' => 'Formosa Petrochemical Corp.', 'short_name' => 'FPCC'],
                ],
            ],
        ];

        foreach ($organizations as $orgData) {
            $translations = $orgData['translations'];
            unset($orgData['translations']);

            $org = Organization::create($orgData);
            $org->saveTranslations($translations);
        }
    }
}
