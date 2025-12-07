<?php

namespace Database\Seeders;

use App\Models\SubService;
use App\Models\ServiceProcess;
use Illuminate\Database\Seeder;

class ServiceProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all sub-services
        $subServices = SubService::all();

        foreach ($subServices as $subService) {
            $this->createProcessesForSubService($subService);
        }
    }

    /**
     * Create processes for a specific sub-service
     */
    private function createProcessesForSubService(SubService $subService): void
    {
        $subServiceName = strtolower($subService->name);

        // Skip if processes already exist for this sub-service
        if ($subService->processes()->count() > 0) {
            return;
        }

        // Define processes based on sub-service name
        $processes = $this->getProcessesForSubService($subServiceName, $subService->name);

        foreach ($processes as $index => $process) {
            ServiceProcess::create([
                'sub_service_id' => $subService->id,
                'title' => $process['title'],
                'description' => $process['description'],
                'order' => $index + 1,
                'status' => true,
            ]);
        }
    }

    /**
     * Get processes configuration for a sub-service
     */
    private function getProcessesForSubService(string $subServiceName, string $displayName): array
    {
        // Common processes for most services
        $commonProcesses = [
            [
                'title' => 'Initial Assessment',
                'description' => "Our expert technician will assess your {$displayName} condition and identify any issues or requirements",
            ],
            [
                'title' => 'Quote and Approval',
                'description' => 'We provide a detailed quote with all costs and wait for your approval before proceeding',
            ],
            [
                'title' => 'Service Execution',
                'description' => 'Professional work is carried out by certified technicians using quality materials',
            ],
            [
                'title' => 'Quality Check',
                'description' => 'Final inspection to ensure everything is working perfectly and meets our quality standards',
            ],
            [
                'title' => 'Completion and Handover',
                'description' => 'Service completion documentation and handover of the completed work',
            ],
        ];

        // Specific processes for different sub-services
        if (str_contains($subServiceName, 'toilet')) {
            return [
                [
                    'title' => 'Initial Assessment',
                    'description' => 'Our expert technician will assess your toilet condition and identify any issues',
                ],
                [
                    'title' => 'Quote and Approval',
                    'description' => 'We provide a detailed quote and wait for your approval before proceeding',
                ],
                [
                    'title' => 'Service Execution',
                    'description' => 'Professional installation or repair work is carried out by certified technicians',
                ],
                [
                    'title' => 'Quality Check',
                    'description' => 'Final inspection to ensure everything is working perfectly',
                ],
            ];
        }

        if (str_contains($subServiceName, 'bath') || str_contains($subServiceName, 'shower')) {
            return [
                [
                    'title' => 'Site Inspection',
                    'description' => 'Thorough inspection of your bathroom space and requirements',
                ],
                [
                    'title' => 'Installation Planning',
                    'description' => 'Detailed planning and preparation for installation',
                ],
                [
                    'title' => 'Material Preparation',
                    'description' => 'Preparation and verification of all required materials',
                ],
                [
                    'title' => 'Installation',
                    'description' => 'Professional installation of bath and shower components',
                ],
                [
                    'title' => 'Testing and Quality Check',
                    'description' => 'Testing all connections and final quality inspection',
                ],
            ];
        }

        if (str_contains($subServiceName, 'drainage') || str_contains($subServiceName, 'blockage')) {
            return [
                [
                    'title' => 'Drainage Inspection',
                    'description' => 'Comprehensive inspection of drainage system to identify blockages',
                ],
                [
                    'title' => 'Problem Identification',
                    'description' => 'Detailed analysis of the blockage cause and location',
                ],
                [
                    'title' => 'Cleaning Process',
                    'description' => 'Professional cleaning and blockage removal using appropriate tools',
                ],
                [
                    'title' => 'System Testing',
                    'description' => 'Testing drainage flow to ensure complete blockage removal',
                ],
                [
                    'title' => 'Preventive Recommendations',
                    'description' => 'Providing recommendations to prevent future blockages',
                ],
            ];
        }

        if (str_contains($subServiceName, 'tap') || str_contains($subServiceName, 'mixer')) {
            return [
                [
                    'title' => 'Assessment',
                    'description' => 'Assessment of existing tap/mixer condition and requirements',
                ],
                [
                    'title' => 'Selection and Quote',
                    'description' => 'Selection of appropriate tap/mixer and providing detailed quote',
                ],
                [
                    'title' => 'Installation',
                    'description' => 'Professional installation of tap/mixer with proper connections',
                ],
                [
                    'title' => 'Testing',
                    'description' => 'Testing water flow, temperature control, and leak checks',
                ],
            ];
        }

        if (str_contains($subServiceName, 'wiring') || str_contains($subServiceName, 'electrical')) {
            return [
                [
                    'title' => 'Safety Assessment',
                    'description' => 'Complete electrical safety assessment and code compliance check',
                ],
                [
                    'title' => 'Planning and Design',
                    'description' => 'Electrical wiring planning and design according to safety standards',
                ],
                [
                    'title' => 'Wiring Installation',
                    'description' => 'Professional electrical wiring installation by certified electricians',
                ],
                [
                    'title' => 'Safety Testing',
                    'description' => 'Comprehensive safety testing and certification',
                ],
            ];
        }

        if (str_contains($subServiceName, 'cleaning')) {
            return [
                [
                    'title' => 'Area Assessment',
                    'description' => 'Assessment of cleaning requirements and area size',
                ],
                [
                    'title' => 'Preparation',
                    'description' => 'Preparation of cleaning equipment and materials',
                ],
                [
                    'title' => 'Cleaning Process',
                    'description' => 'Professional cleaning service execution',
                ],
                [
                    'title' => 'Quality Inspection',
                    'description' => 'Final inspection to ensure cleaning standards are met',
                ],
            ];
        }

        if (str_contains($subServiceName, 'consultation')) {
            return [
                [
                    'title' => 'Booking Confirmation',
                    'description' => 'Confirmation of consultation appointment and details',
                ],
                [
                    'title' => 'Consultation Session',
                    'description' => 'Expert consultation session to discuss your requirements',
                ],
                [
                    'title' => 'Recommendations',
                    'description' => 'Providing professional recommendations and solutions',
                ],
                [
                    'title' => 'Follow-up',
                    'description' => 'Follow-up communication and support',
                ],
            ];
        }

        // Default processes for other sub-services
        return $commonProcesses;
    }
}
