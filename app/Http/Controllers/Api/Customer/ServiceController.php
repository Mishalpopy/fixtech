<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Get all services with sub-services and all relations
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $services = Service::where('status', true)
                ->with([
                    'subServices' => function ($query) {
                        $query->where('status', true)
                            ->with([
                                'serviceItems' => function ($q) {
                                    $q->where('status', true);
                                },
                                'processes' => function ($q) {
                                    $q->where('status', true)->orderBy('order');
                                },
                                'priceCharts' => function ($q) {
                                    $q->where('status', true)->orderBy('order');
                                },
                                'faqs' => function ($q) {
                                    $q->where('status', true)->orderBy('order');
                                }
                            ]);
                    }
                ])
                ->get();

            // Format image URLs
            $services = $services->map(function ($service) {
                if ($service->image) {
                    $service->image_url = asset('storage/' . $service->image);
                }
                
                $service->sub_services = $service->subServices->map(function ($subService) {
                    if ($subService->image) {
                        $subService->image_url = asset('storage/' . $subService->image);
                    }
                    
                    $subService->items = $subService->serviceItems->map(function ($item) {
                        if ($item->image) {
                            $item->image_url = asset('storage/' . $item->image);
                        }
                        return $item;
                    });
                    
                    return $subService;
                });
                
                return $service;
            });

            return response()->json([
                'success' => true,
                'message' => 'Services retrieved successfully',
                'data' => $services
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve services',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single service with all relations
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $service = Service::where('id', $id)
                ->where('status', true)
                ->with([
                    'subServices' => function ($query) {
                        $query->where('status', true)
                            ->with([
                                'serviceItems' => function ($q) {
                                    $q->where('status', true);
                                },
                                'processes' => function ($q) {
                                    $q->where('status', true)->orderBy('order');
                                },
                                'priceCharts' => function ($q) {
                                    $q->where('status', true)->orderBy('order');
                                },
                                'faqs' => function ($q) {
                                    $q->where('status', true)->orderBy('order');
                                }
                            ]);
                    }
                ])
                ->first();

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }

            // Format image URLs
            if ($service->image) {
                $service->image_url = asset('storage/' . $service->image);
            }
            
            $service->sub_services = $service->subServices->map(function ($subService) {
                if ($subService->image) {
                    $subService->image_url = asset('storage/' . $subService->image);
                }
                
                $subService->items = $subService->serviceItems->map(function ($item) {
                    if ($item->image) {
                        $item->image_url = asset('storage/' . $item->image);
                    }
                    return $item;
                });
                
                return $subService;
            });

            return response()->json([
                'success' => true,
                'message' => 'Service retrieved successfully',
                'data' => $service
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
