<?php

namespace App\Repositories\SubServiceFaq;

use App\Models\SubServiceFaq;

class SubServiceFaqRepository
{

    public function store($data)
    {
        $faqData = [
            'sub_service_id' => $data['sub_service_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'order' => $data['order'] ?? 0,
            'status' => $data['status'] ?? true,
        ];

        $faq = SubServiceFaq::create($faqData);

        return $faq;
    }

    public function update($data, $faq)
    {
        $faqData = [
            'sub_service_id' => $data['sub_service_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'order' => $data['order'] ?? $faq->order,
            'status' => $data['status'] ?? $faq->status,
        ];

        $faq->update($faqData);

        return $faq;
    }

    public function getAllFaqs($subServiceId = null)
    {
        $query = SubServiceFaq::with('subService.service');
        
        if ($subServiceId) {
            $query->where('sub_service_id', $subServiceId);
        }
        
        return $query->orderBy('order')->orderBy('created_at', 'desc')->get();
    }

    public function bulkStore($data)
    {
        $faqs = [];
        $subServiceId = $data['sub_service_id'];

        foreach ($data['faqs'] as $index => $faqData) {
            $faqs[] = SubServiceFaq::create([
                'sub_service_id' => $subServiceId,
                'title' => $faqData['title'],
                'description' => $faqData['description'],
                'order' => $faqData['order'] ?? $index,
                'status' => $faqData['status'] ?? true,
            ]);
        }

        return $faqs;
    }
}

