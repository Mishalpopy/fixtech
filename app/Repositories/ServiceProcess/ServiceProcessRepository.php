<?php

namespace App\Repositories\ServiceProcess;

use App\Models\ServiceProcess;

class ServiceProcessRepository
{

    public function store($data)
    {
        $processData = [
            'sub_service_id' => $data['sub_service_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'order' => $data['order'] ?? 0,
            'status' => $data['status'] ?? true,
        ];

        $process = ServiceProcess::create($processData);

        return $process;
    }

    public function update($data, $process)
    {
        $processData = [
            'sub_service_id' => $data['sub_service_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'order' => $data['order'] ?? $process->order,
            'status' => $data['status'] ?? $process->status,
        ];

        $process->update($processData);

        return $process;
    }

    public function getAllProcesses($subServiceId = null)
    {
        $query = ServiceProcess::with('subService.service');
        
        if ($subServiceId) {
            $query->where('sub_service_id', $subServiceId);
        }
        
        return $query->orderBy('order')->orderBy('created_at', 'desc')->get();
    }
}

