<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class OrdersController extends Controller
{
    public function index()
    {
       return Inertia::render('Orders/Index',[
		   'filters' => Request::all('search', 'trashed'),
		   'orders'	 => Auth::user()->account->orders()
                ->orderBy('name')
                ->filter(Request::only('search', 'trashed'))
                ->paginate(10)
                ->withQueryString()
                ->through(fn ($organization) => [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'phone' => $organization->phone,
                    'city' => $organization->city,
                    'deleted_at' => $organization->deleted_at,
                ]),
		   
	   ]);
		
		
    }

    public function create()
    {
        return Inertia::render('Orders/Create');
    }

    public function store()
    {
        Auth::user()->account->orders()->create(
            Request::validate([
                'name' => ['required', 'max:100'],
				'order_no' => ['required', 'max:100'],
                'email' => ['nullable', 'max:50', 'email'],
                'phone' => ['nullable', 'max:50'],
                'address' => ['nullable', 'max:150']
            ])
        );

        return Redirect::route('orders')->with('success', 'Orders created.');
    }

    public function edit(Organization $organization)
    {
        return Inertia::render('Orders/Edit', [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'email' => $organization->email,
                'phone' => $organization->phone,
                'address' => $organization->address,
                'city' => $organization->city,
                'region' => $organization->region,
                'country' => $organization->country,
                'postal_code' => $organization->postal_code,
                'deleted_at' => $organization->deleted_at,
                'contacts' => $organization->contacts()->orderByName()->get()->map->only('id', 'name', 'city', 'phone'),
            ],
        ]);
    }

    public function update(Organization $organization)
    {
        $organization->update(
            Request::validate([
                'name' => ['required', 'max:100'],
                'email' => ['nullable', 'max:50', 'email'],
                'phone' => ['nullable', 'max:50'],
                'address' => ['nullable', 'max:150'],
                'city' => ['nullable', 'max:50'],
                'region' => ['nullable', 'max:50'],
                'country' => ['nullable', 'max:2'],
                'postal_code' => ['nullable', 'max:25'],
            ])
        );

        return Redirect::back()->with('success', 'Orders updated.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return Redirect::back()->with('success', 'Orders deleted.');
    }

    public function restore(Organization $organization)
    {
        $organization->restore();

        return Redirect::back()->with('success', 'Orders restored.');
    }
}
