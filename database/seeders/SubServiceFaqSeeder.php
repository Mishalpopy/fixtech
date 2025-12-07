<?php

namespace Database\Seeders;

use App\Models\SubService;
use App\Models\SubServiceFaq;
use Illuminate\Database\Seeder;

class SubServiceFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all sub-services
        $subServices = SubService::all();

        foreach ($subServices as $subService) {
            // Skip if FAQs already exist for this sub-service
            if ($subService->faqs()->count() > 0) {
                continue;
            }

            // Get FAQs based on sub-service type
            $faqs = $this->getFaqsForSubService($subService->name);

            // Create FAQs
            foreach ($faqs as $index => $faq) {
                SubServiceFaq::create([
                    'sub_service_id' => $subService->id,
                    'title' => $faq['title'],
                    'description' => $faq['description'],
                    'order' => $index,
                    'status' => true,
                ]);
            }
        }
    }

    /**
     * Get FAQs configuration for a sub-service
     */
    private function getFaqsForSubService(string $subServiceName): array
    {
        $subServiceName = strtolower($subServiceName);

        // Common FAQs for most services
        $commonFaqs = [
            [
                'title' => 'How long does the service take?',
                'description' => 'The duration depends on the specific service required. Our technician will provide an estimated time during the initial assessment.',
            ],
            [
                'title' => 'What are your working hours?',
                'description' => 'We operate from 8:00 AM to 8:00 PM, Monday through Saturday. Emergency services are available 24/7.',
            ],
            [
                'title' => 'Do you provide warranty on your services?',
                'description' => 'Yes, we provide a comprehensive warranty on all our services. The warranty period varies depending on the type of service provided.',
            ],
            [
                'title' => 'What payment methods do you accept?',
                'description' => 'We accept cash, credit cards, debit cards, and digital payment methods. Payment can be made upon completion of the service.',
            ],
        ];

        // Specific FAQs for different sub-services
        if (str_contains($subServiceName, 'toilet')) {
            return [
                [
                    'title' => 'How do I know if my toilet needs repair?',
                    'description' => 'Common signs include constant running water, weak flush, leaks around the base, or unusual noises. If you notice any of these, it\'s time to call a professional.',
                ],
                [
                    'title' => 'Can you install any brand of toilet?',
                    'description' => 'Yes, our technicians are experienced with all major toilet brands and can install or repair any model you prefer.',
                ],
                [
                    'title' => 'How long does toilet installation take?',
                    'description' => 'A standard toilet installation typically takes 1-2 hours, depending on the complexity and any additional plumbing work required.',
                ],
                [
                    'title' => 'Do you dispose of the old toilet?',
                    'description' => 'Yes, we can dispose of your old toilet as part of our service. Please let us know when booking if you need this service.',
                ],
            ];
        }

        if (str_contains($subServiceName, 'bath') || str_contains($subServiceName, 'shower')) {
            return [
                [
                    'title' => 'How long does bath/shower installation take?',
                    'description' => 'Installation time varies based on the type of unit and complexity. A standard installation typically takes 2-4 hours.',
                ],
                [
                    'title' => 'Do I need to prepare anything before installation?',
                    'description' => 'We recommend clearing the area around the installation site and ensuring access to water and drainage connections. Our team will handle the rest.',
                ],
                [
                    'title' => 'What materials do you use?',
                    'description' => 'We use high-quality, durable materials from trusted suppliers. All materials come with manufacturer warranties.',
                ],
            ];
        }

        if (str_contains($subServiceName, 'drainage') || str_contains($subServiceName, 'blockage')) {
            return [
                [
                    'title' => 'How do you clear blocked drains?',
                    'description' => 'We use professional equipment including drain snakes, hydro-jetting machines, and CCTV cameras to identify and clear blockages effectively.',
                ],
                [
                    'title' => 'Will you prevent future blockages?',
                    'description' => 'Yes, after clearing the blockage, we provide recommendations and can apply preventive treatments to reduce the likelihood of future issues.',
                ],
                [
                    'title' => 'How quickly can you respond to drainage emergencies?',
                    'description' => 'We offer 24/7 emergency services and can typically respond within 1-2 hours for urgent drainage issues.',
                ],
            ];
        }

        if (str_contains($subServiceName, 'wiring') || str_contains($subServiceName, 'electrical')) {
            return [
                [
                    'title' => 'Are your electricians licensed and certified?',
                    'description' => 'Yes, all our electricians are fully licensed, certified, and insured. We maintain the highest safety standards.',
                ],
                [
                    'title' => 'Do you provide electrical safety certificates?',
                    'description' => 'Yes, we provide proper certification for all electrical work completed, which is important for insurance and compliance purposes.',
                ],
                [
                    'title' => 'How long does electrical wiring installation take?',
                    'description' => 'The duration depends on the scope of work. A simple wiring job may take a few hours, while a complete rewiring can take several days.',
                ],
            ];
        }

        if (str_contains($subServiceName, 'cleaning')) {
            return [
                [
                    'title' => 'What cleaning products do you use?',
                    'description' => 'We use eco-friendly, professional-grade cleaning products that are safe for your family and pets while being highly effective.',
                ],
                [
                    'title' => 'Do I need to be present during cleaning?',
                    'description' => 'It\'s not necessary, but you\'re welcome to be present. We ensure all our staff are background-checked and trustworthy.',
                ],
                [
                    'title' => 'How often should I schedule cleaning?',
                    'description' => 'This depends on your needs. We offer one-time, weekly, bi-weekly, and monthly cleaning schedules. We can recommend the best option for you.',
                ],
            ];
        }

        if (str_contains($subServiceName, 'consultation')) {
            return [
                [
                    'title' => 'Is the consultation free?',
                    'description' => 'Yes, our initial consultation is completely free. This allows us to assess your needs and provide accurate quotes.',
                ],
                [
                    'title' => 'What happens during a consultation?',
                    'description' => 'Our expert will visit your property, assess the situation, discuss your requirements, and provide professional recommendations and detailed quotes.',
                ],
                [
                    'title' => 'How long does a consultation take?',
                    'description' => 'A typical consultation takes 30-60 minutes, depending on the complexity of your requirements.',
                ],
                [
                    'title' => 'Do I need to book a consultation in advance?',
                    'description' => 'Yes, we recommend booking in advance to ensure we can schedule at a time convenient for you. However, we also accept same-day bookings when available.',
                ],
            ];
        }

        // Default FAQs for other sub-services
        return $commonFaqs;
    }
}
