<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\SubService;
use App\Models\ServiceItem;
use App\Models\ServiceProcess;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plumbing Service
        $plumbing = Service::create([
            'name' => 'Plumbing',
            'description' => 'Professional plumbing services for all your needs',
            'status' => true,
        ]);

        // Plumbing Sub Services
        $toilet = SubService::create([
            'service_id' => $plumbing->id,
            'name' => 'Toilet',
            'description' => 'Toilet installation, repair, and maintenance services',
            'status' => true,
        ]);

        $bathShower = SubService::create([
            'service_id' => $plumbing->id,
            'name' => 'Bath & Shower',
            'description' => 'Bath and shower installation and repair services',
            'status' => true,
        ]);

        $tapMixer = SubService::create([
            'service_id' => $plumbing->id,
            'name' => 'Tap & Mixer',
            'description' => 'Tap and mixer installation and repair services',
            'status' => true,
        ]);

        $drainage = SubService::create([
            'service_id' => $plumbing->id,
            'name' => 'Drainage & Blockage',
            'description' => 'Drainage cleaning and blockage removal services',
            'status' => true,
        ]);

        $bathAccessories = SubService::create([
            'service_id' => $plumbing->id,
            'name' => 'Bath Accessories',
            'description' => 'Bathroom accessories installation and repair',
            'status' => true,
        ]);

        $basinSink = SubService::create([
            'service_id' => $plumbing->id,
            'name' => 'Basin & Sink',
            'description' => 'Basin and sink installation and repair services',
            'status' => true,
        ]);

        $waterTank = SubService::create([
            'service_id' => $plumbing->id,
            'name' => 'Water Tank',
            'description' => 'Water tank installation, repair, and maintenance',
            'status' => true,
        ]);

        $consultation = SubService::create([
            'service_id' => $plumbing->id,
            'name' => 'Book a Consultation',
            'description' => 'Book a consultation with our plumbing experts',
            'status' => true,
        ]);

        // Toilet Items
        ServiceItem::create([
            'sub_service_id' => $toilet->id,
            'name' => 'Jetspray',
            'description' => 'Professional jetspray installation and repair',
            'price' => 150.00,
            'status' => true,
        ]);

        ServiceItem::create([
            'sub_service_id' => $toilet->id,
            'name' => 'Toilet Installation',
            'description' => 'Complete toilet installation service',
            'price' => 300.00,
            'status' => true,
        ]);

        ServiceItem::create([
            'sub_service_id' => $toilet->id,
            'name' => 'Toilet Repair',
            'description' => 'Toilet repair and maintenance service',
            'price' => 100.00,
            'status' => true,
        ]);

        // Toilet Processes
        ServiceProcess::create([
            'sub_service_id' => $toilet->id,
            'title' => 'Initial Assessment',
            'description' => 'Our expert technician will assess your toilet condition and identify any issues',
            'order' => 1,
            'status' => true,
        ]);

        ServiceProcess::create([
            'sub_service_id' => $toilet->id,
            'title' => 'Quote and Approval',
            'description' => 'We provide a detailed quote and wait for your approval before proceeding',
            'order' => 2,
            'status' => true,
        ]);

        ServiceProcess::create([
            'sub_service_id' => $toilet->id,
            'title' => 'Service Execution',
            'description' => 'Professional installation or repair work is carried out by certified technicians',
            'order' => 3,
            'status' => true,
        ]);

        ServiceProcess::create([
            'sub_service_id' => $toilet->id,
            'title' => 'Quality Check',
            'description' => 'Final inspection to ensure everything is working perfectly',
            'order' => 4,
            'status' => true,
        ]);

        // Electrical Service
        $electrical = Service::create([
            'name' => 'Electrical',
            'description' => 'Professional electrical services for residential and commercial',
            'status' => true,
        ]);

        // Electrical Sub Services (example)
        $wiring = SubService::create([
            'service_id' => $electrical->id,
            'name' => 'Wiring',
            'description' => 'Electrical wiring installation and repair',
            'status' => true,
        ]);

        $lighting = SubService::create([
            'service_id' => $electrical->id,
            'name' => 'Lighting',
            'description' => 'Lighting installation and repair services',
            'status' => true,
        ]);

        // Carpentry Service
        $carpentry = Service::create([
            'name' => 'Carpentry',
            'description' => 'Professional carpentry and woodworking services',
            'status' => true,
        ]);

        // Carpentry Sub Services (example)
        $furniture = SubService::create([
            'service_id' => $carpentry->id,
            'name' => 'Furniture',
            'description' => 'Furniture installation and repair',
            'status' => true,
        ]);

        $doors = SubService::create([
            'service_id' => $carpentry->id,
            'name' => 'Doors & Windows',
            'description' => 'Doors and windows installation and repair',
            'status' => true,
        ]);

        // Cleaning Service
        $cleaning = Service::create([
            'name' => 'Cleaning',
            'description' => 'Professional cleaning services for homes and offices',
            'status' => true,
        ]);

        // Cleaning Sub Services (example)
        $houseCleaning = SubService::create([
            'service_id' => $cleaning->id,
            'name' => 'House Cleaning',
            'description' => 'Complete house cleaning service',
            'status' => true,
        ]);

        $officeCleaning = SubService::create([
            'service_id' => $cleaning->id,
            'name' => 'Office Cleaning',
            'description' => 'Professional office cleaning service',
            'status' => true,
        ]);

        // Microwave Oven Service
        $microwave = Service::create([
            'name' => 'Microwave Oven',
            'description' => 'Microwave oven installation, repair, and maintenance',
            'status' => true,
        ]);

        // Microwave Oven Sub Services (example)
        $microwaveInstallation = SubService::create([
            'service_id' => $microwave->id,
            'name' => 'Installation',
            'description' => 'Microwave oven installation service',
            'status' => true,
        ]);

        $microwaveRepair = SubService::create([
            'service_id' => $microwave->id,
            'name' => 'Repair',
            'description' => 'Microwave oven repair and maintenance',
            'status' => true,
        ]);

        // Add some items for other sub-services as examples
        ServiceItem::create([
            'sub_service_id' => $bathShower->id,
            'name' => 'Shower Installation',
            'description' => 'Complete shower installation service',
            'price' => 400.00,
            'status' => true,
        ]);

        ServiceItem::create([
            'sub_service_id' => $tapMixer->id,
            'name' => 'Tap Installation',
            'description' => 'Tap and mixer installation service',
            'price' => 200.00,
            'status' => true,
        ]);

        ServiceItem::create([
            'sub_service_id' => $drainage->id,
            'name' => 'Drainage Cleaning',
            'description' => 'Professional drainage cleaning service',
            'price' => 150.00,
            'status' => true,
        ]);

        ServiceItem::create([
            'sub_service_id' => $wiring->id,
            'name' => 'Electrical Wiring',
            'description' => 'Complete electrical wiring installation',
            'price' => 500.00,
            'status' => true,
        ]);

        ServiceItem::create([
            'sub_service_id' => $lighting->id,
            'name' => 'Light Fixture Installation',
            'description' => 'Light fixture installation service',
            'price' => 250.00,
            'status' => true,
        ]);

        // Add processes for other sub-services
        ServiceProcess::create([
            'sub_service_id' => $bathShower->id,
            'title' => 'Site Inspection',
            'description' => 'Thorough inspection of your bathroom space and requirements',
            'order' => 1,
            'status' => true,
        ]);

        ServiceProcess::create([
            'sub_service_id' => $bathShower->id,
            'title' => 'Installation Planning',
            'description' => 'Detailed planning and preparation for installation',
            'order' => 2,
            'status' => true,
        ]);

        ServiceProcess::create([
            'sub_service_id' => $drainage->id,
            'title' => 'Drainage Inspection',
            'description' => 'Comprehensive inspection of drainage system',
            'order' => 1,
            'status' => true,
        ]);

        ServiceProcess::create([
            'sub_service_id' => $drainage->id,
            'title' => 'Cleaning Process',
            'description' => 'Professional cleaning and blockage removal',
            'order' => 2,
            'status' => true,
        ]);

        ServiceProcess::create([
            'sub_service_id' => $wiring->id,
            'title' => 'Safety Assessment',
            'description' => 'Complete electrical safety assessment',
            'order' => 1,
            'status' => true,
        ]);

        ServiceProcess::create([
            'sub_service_id' => $wiring->id,
            'title' => 'Wiring Installation',
            'description' => 'Professional electrical wiring installation',
            'order' => 2,
            'status' => true,
        ]);
    }
}
