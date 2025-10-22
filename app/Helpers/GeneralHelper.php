<?php

use App\Models\Site\SiteGroup;
use App\Models\Task\WeekDay;
use App\Repositories\User\UserGroupRepository;
use App\Repositories\User\UserRepository;

if (!function_exists('response_api')) {
    function response_api($data, $status_code = 200, $message = null, $description = null)
    {

        if ($status_code == 200) {
            $status = true;
        } else {
            $status = false;
        }

        $response_data = ['status' => $status, 'status_code' => $status_code, 'message' => $message, 'description' => $description, 'data' => $data, 'now' => now()->format('Y-m-d H:i:s')];

        return response()->json($response_data, $status_code);
    }
}

if (!function_exists('booking_status')) {

    function booking_status()
    {
        return ['pending', 'confirmed', 'cancelled'];
    }
}
