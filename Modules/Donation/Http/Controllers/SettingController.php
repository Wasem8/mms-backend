<?php

namespace Modules\Donation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Donation\Models\Setting;
use Modules\Donation\Repositories\SettingRepositoryInterface;
use Illuminate\Support\Facades\Cache;


class SettingController extends Controller
{
    private const RATE_CACHE_KEY = 'setting.usd_to_syp_rate';

    public function __construct(
        private readonly SettingRepositoryInterface $settingRepo,
    ) {}

    /**
     * Return all settings (for the admin dashboard).
     *
     * GET /admin/settings
     */
    public function index()
    {
        $settings = Setting::all(['key', 'value']);

        return response()->json($settings);
    }


    public function updateExchangeRate(Request $request)
    {
        $validated = $request->validate([
            'rate' => [
                'required',
                'numeric',
                'min:1',
                'max:10000000',
            ],
        ]);

        $newRate = (float) $validated['rate'];

        $this->settingRepo->set('usd_to_syp_rate', (string) $newRate);


        Cache::forget(self::RATE_CACHE_KEY);

        return response()->json([
            'message'  => 'Exchange rate updated successfully.',
            'key'      => 'usd_to_syp_rate',
            'new_rate' => $newRate,
            'unit'     => '1 USD = ' . number_format($newRate, 0) . ' SYP',
        ]);
    }
}
