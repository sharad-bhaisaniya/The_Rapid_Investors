<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EmployeeController extends Controller implements HasMiddleware
{
   public static function middleware(): array
    {
        return [
            // Sirf login users hi access kar sakein
            new Middleware('auth'), 
            
            // Permission based checks
            new Middleware('permission:view employees', only: ['index', 'show']),
            new Middleware('permission:create employees', only: ['create', 'store']),
            new Middleware('permission:edit employees', only: ['edit', 'update']),
            new Middleware('permission:delete employees', only: ['destroy']),
        ];
    }

    // 📌 List Employees
public function index(Request $request)
{
    $search = $request->input('search');
    $employees = User::role('employee')
        ->when($search, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        })
        ->latest()
        ->paginate(15);

    if ($request->header('HX-Request') && $request->has('action')) {
        $selectedEmployee = null; // Naam change kiya
        if ($request->filled('id')) {
            $selectedEmployee = User::role('employee')->findOrFail($request->id);
        }
        // Yahan variable ka naam change karke bhejein
        return view('admin.employees.index', compact('selectedEmployee', 'employees'))->fragment('modal-content');
    }

    if ($request->header('HX-Request')) {
        return view('admin.employees.index', compact('employees'))->fragment('table-body');
    }

    return view('admin.employees.index', compact('employees'));
}

    // 📌 Create Form
    public function create()
    {
        return view('admin.employees.create');
    }

    // 📌 Store Employee
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required',
            // 'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => bcrypt($request->phone),
            'status'   => 'active',
        ]);

        // Assign employee role
        $user->assignRole('employee');

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully');
    }

    // 📌 Edit Form
    public function edit($id)
    {
        $employee = User::findOrFail($id);
        return view('admin.employees.edit', compact('employee'));
    }

    // 📌 Update Employee
    public function update(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $request->validate([
            'name'  => 'required',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'phone' => 'required',
        ]);

        $employee->update($request->only('name', 'email', 'phone', 'status'));

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully');
    }

    // 📌 Delete Employee
    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully');
    }
}
