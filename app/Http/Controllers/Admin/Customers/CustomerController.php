<?php

namespace App\Http\Controllers\Admin\Customers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Repositories\Customer\CustomerRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class CustomerController extends Controller
{
    use Toast;
    protected $customer_repo;

    public function __construct(CustomerRepository $customer_repo)
    {
        $this->customer_repo = $customer_repo;
    }

    public function index()
    {
        return Inertia::render('Admin/Customers/Index', [
            'customers' => $this->customer_repo->getAllCustomers()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Customers/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $customer = $this->customer_repo->store($request->all());
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Customer Successfully Created");
        return redirect()->route('admin:customers.index');
    }

    public function edit(Customer $customer)
    {
        return Inertia::render('Admin/Customers/Edit', [
            'customer' => $customer
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $customer = $this->customer_repo->update($request->all(), $customer);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Customer Successfully Updated");
        return redirect()->route('admin:customers.index');
    }

    public function show(Customer $customer)
    {
        return Inertia::render('Admin/Customers/Show', [
            'customer' => $customer
        ]);
    }

    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            $this->toast('success', "Success", "Customer Successfully Deleted");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }

    public function changeStatus(Customer $customer)
    {
        try {
            $customer->status = !$customer->status;
            $customer->save();
            $this->toast('success', "Success", "Customer Status Updated");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }
}

