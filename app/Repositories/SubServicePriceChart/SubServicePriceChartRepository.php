<?php

namespace App\Repositories\SubServicePriceChart;

use App\Models\SubServicePriceChart;

class SubServicePriceChartRepository
{

    public function store($data)
    {
        $priceChartData = [
            'sub_service_id' => $data['sub_service_id'],
            'time_duration' => $data['time_duration'],
            'current_price' => $data['current_price'],
            'original_price' => $data['original_price'] ?? null,
            'is_urgent' => $data['is_urgent'] ?? false,
            'order' => $data['order'] ?? 0,
            'status' => $data['status'] ?? true,
        ];

        $priceChart = SubServicePriceChart::create($priceChartData);

        return $priceChart;
    }

    public function update($data, $priceChart)
    {
        $priceChartData = [
            'sub_service_id' => $data['sub_service_id'],
            'time_duration' => $data['time_duration'],
            'current_price' => $data['current_price'],
            'original_price' => $data['original_price'] ?? null,
            'is_urgent' => $data['is_urgent'] ?? $priceChart->is_urgent,
            'order' => $data['order'] ?? $priceChart->order,
            'status' => $data['status'] ?? $priceChart->status,
        ];

        $priceChart->update($priceChartData);

        return $priceChart;
    }

    public function getAllPriceCharts($subServiceId = null)
    {
        $query = SubServicePriceChart::with('subService.service');
        
        if ($subServiceId) {
            $query->where('sub_service_id', $subServiceId);
        }
        
        return $query->orderBy('order')->orderBy('created_at', 'desc')->get();
    }

    public function bulkStore($data)
    {
        $priceCharts = [];
        $subServiceId = $data['sub_service_id'];

        foreach ($data['price_charts'] as $index => $chartData) {
            $priceCharts[] = SubServicePriceChart::create([
                'sub_service_id' => $subServiceId,
                'time_duration' => $chartData['time_duration'],
                'current_price' => $chartData['current_price'],
                'original_price' => $chartData['original_price'] ?? null,
                'is_urgent' => $chartData['is_urgent'] ?? false,
                'order' => $chartData['order'] ?? $index,
                'status' => $chartData['status'] ?? true,
            ]);
        }

        return $priceCharts;
    }
}

