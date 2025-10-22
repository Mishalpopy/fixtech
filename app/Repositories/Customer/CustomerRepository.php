<?php

namespace App\Repositories\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class CustomerRepository
{

    public function store($data)
    {
        $customerData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'status' => $data['status'] ?? true,
        ];

        // Add password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $customerData['password'] = Hash::make($data['password']);
        }

        $customer = Customer::create($customerData);

        return $customer;
    }

    public function update($data, $customer)
    {
        $customerData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'status' => $data['status'] ?? $customer->status,
        ];

        // Update password only if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $customerData['password'] = Hash::make($data['password']);
        }

        $customer->update($customerData);

        return $customer;
    }

    public function getAllCustomers()
    {
        return Customer::all();
    }

    public function validateData($data, $isUpdate = false)
    {
        $rules = [
            'name' => 'bail|required|string|max:255',
            'email' => 'bail|required|email|unique:customers,email' . ($isUpdate && isset($data['id']) ? ',' . $data['id'] : ''),
            'phone' => 'bail|required|string|max:20',
            'address' => 'bail|required|string',
            'status' => 'nullable|boolean',
        ];

        // Password is required on create, optional on update
        if (!$isUpdate) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        validator($data, $rules)->validate();
    }
}

