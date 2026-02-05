<?php

namespace App\Portals\Ocadmin\Modules\Dashboard;

use Illuminate\Http\Request;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class DashboardController extends OcadminController
{
    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => '首頁',
                'href' => route('lang.ocadmin.dashboard'),
            ],
        ];
    }

    public function index()
    {
        return view('ocadmin.dashboard::index', [
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * API: 取得銷售圖表資料
     */
    public function chartSales(Request $request)
    {
        $range = $request->get('range', 'month');
        $data = $this->getMockChartData($range);

        return response()->json($data);
    }

    /**
     * API: 取得地圖資料
     */
    public function mapData()
    {
        $data = [
            'tw' => ['total' => 150, 'amount' => 'NT$45,000'],
            'us' => ['total' => 80,  'amount' => 'NT$24,000'],
            'jp' => ['total' => 50,  'amount' => 'NT$15,000'],
            'hk' => ['total' => 30,  'amount' => 'NT$9,000'],
        ];

        return response()->json($data);
    }

    protected function getMockChartData(string $range): array
    {
        $orderData = [];
        $customerData = [];
        $xaxis = [];

        switch ($range) {
            case 'day':
                for ($i = 0; $i <= 23; $i++) {
                    $orderData[] = [$i, rand(0, 20)];
                    $customerData[] = [$i, rand(0, 10)];
                    $xaxis[] = [$i, sprintf('%02d:00', $i)];
                }
                break;

            case 'week':
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                for ($i = 0; $i < 7; $i++) {
                    $orderData[] = [$i, rand(10, 100)];
                    $customerData[] = [$i, rand(5, 50)];
                    $xaxis[] = [$i, $days[$i]];
                }
                break;

            case 'year':
                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                for ($i = 0; $i < 12; $i++) {
                    $orderData[] = [$i, rand(100, 1000)];
                    $customerData[] = [$i, rand(50, 500)];
                    $xaxis[] = [$i, $months[$i]];
                }
                break;

            case 'month':
            default:
                $daysInMonth = date('t');
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $orderData[] = [$i, rand(5, 80)];
                    $customerData[] = [$i, rand(2, 40)];
                    $xaxis[] = [$i, sprintf('%02d', $i)];
                }
                break;
        }

        return [
            'order' => [
                'label' => 'Orders',
                'data'  => $orderData
            ],
            'customer' => [
                'label' => 'Customers',
                'data'  => $customerData
            ],
            'xaxis' => $xaxis
        ];
    }
}
