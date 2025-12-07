<?php

namespace Database\Seeders;

use App\Models\SubService;
use App\Models\SubServicePriceChart;
use Illuminate\Database\Seeder;

class SubServicePriceChartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all sub-services
        $subServices = SubService::all();

        // Standard time durations based on Figma design
        $timeDurations = [
            'First 1 Hr',
            'Upto 1.5 Hrs',
            'Upto 2 Hrs',
            'Upto 2.5 Hrs',
            'Upto 3 Hrs',
            'Upto 3.5 Hrs',
            'Upto 4 Hrs'
        ];

        foreach ($subServices as $subService) {
            // Skip if price charts already exist for this sub-service
            if ($subService->priceCharts()->count() > 0) {
                continue;
            }

            // Get pricing based on sub-service type
            $pricing = $this->getPricingForSubService($subService->name);

            // Create price charts for each time duration
            foreach ($timeDurations as $index => $timeDuration) {
                SubServicePriceChart::create([
                    'sub_service_id' => $subService->id,
                    'time_duration' => $timeDuration,
                    'current_price' => $pricing['current_price'],
                    'original_price' => $pricing['original_price'],
                    'is_urgent' => false,
                    'order' => $index,
                    'status' => true,
                ]);
            }

            // Optionally create urgent pricing (higher price)
            if ($this->shouldHaveUrgentPricing($subService->name)) {
                foreach ($timeDurations as $index => $timeDuration) {
                    SubServicePriceChart::create([
                        'sub_service_id' => $subService->id,
                        'time_duration' => $timeDuration,
                        'current_price' => $pricing['urgent_current_price'],
                        'original_price' => $pricing['urgent_original_price'],
                        'is_urgent' => true,
                        'order' => $index + 100, // Place urgent prices after regular ones
                        'status' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Get pricing configuration for a sub-service
     */
    private function getPricingForSubService(string $subServiceName): array
    {
        $subServiceName = strtolower($subServiceName);

        // Default pricing based on Figma design (AED 20 current, AED 30 original)
        $defaultPricing = [
            'current_price' => 20.00,
            'original_price' => 30.00,
            'urgent_current_price' => 35.00,
            'urgent_original_price' => 50.00,
        ];

        // Special pricing for different sub-services
        if (str_contains($subServiceName, 'toilet')) {
            return [
                'current_price' => 20.00,
                'original_price' => 30.00,
                'urgent_current_price' => 40.00,
                'urgent_original_price' => 60.00,
            ];
        }

        if (str_contains($subServiceName, 'bath') || str_contains($subServiceName, 'shower')) {
            return [
                'current_price' => 25.00,
                'original_price' => 35.00,
                'urgent_current_price' => 45.00,
                'urgent_original_price' => 65.00,
            ];
        }

        if (str_contains($subServiceName, 'drainage') || str_contains($subServiceName, 'blockage')) {
            return [
                'current_price' => 30.00,
                'original_price' => 40.00,
                'urgent_current_price' => 50.00,
                'urgent_original_price' => 70.00,
            ];
        }

        if (str_contains($subServiceName, 'wiring') || str_contains($subServiceName, 'electrical')) {
            return [
                'current_price' => 40.00,
                'original_price' => 55.00,
                'urgent_current_price' => 65.00,
                'urgent_original_price' => 85.00,
            ];
        }

        if (str_contains($subServiceName, 'cleaning')) {
            return [
                'current_price' => 15.00,
                'original_price' => 25.00,
                'urgent_current_price' => 30.00,
                'urgent_original_price' => 45.00,
            ];
        }

        if (str_contains($subServiceName, 'consultation')) {
            return [
                'current_price' => 0.00, // Free consultation
                'original_price' => null,
                'urgent_current_price' => 50.00,
                'urgent_original_price' => 75.00,
            ];
        }

        // Default pricing
        return $defaultPricing;
    }

    /**
     * Determine if sub-service should have urgent pricing
     */
    private function shouldHaveUrgentPricing(string $subServiceName): bool
    {
        $subServiceName = strtolower($subServiceName);

        // Consultation doesn't need urgent pricing
        if (str_contains($subServiceName, 'consultation')) {
            return false;
        }

        // Most services should have urgent pricing
        return true;
    }
}
