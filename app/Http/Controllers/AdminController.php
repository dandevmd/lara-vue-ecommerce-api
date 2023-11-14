<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{

    protected $orderStatuses = ['pending', 'completed', 'cancelled'];

    public function allUsersOrders(Request $request)
    {
        $orders = Order::all()->orderBy('created_at', 'desc');
        if (!$orders) {
            return response()->json(['message' => 'No orders found'], 404);
        }
        return response()->json($orders, 200);
    }

    public function updateStatus(Request $request, $id)
    {

        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // check if request status in in orDerStatuses return this status
        if (!in_array($request->status, $this->orderStatuses)) {
            return response()->json(['message' => 'Invalid status'], 400);
        }
        $order->status = $this->orderStatuses[$request->status];
        $order->updated_by = auth()->user()->id;
        $order->save();


        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order
        ], 200);
    }

    public function deleteOrder(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        // delete order, order items and order details
        $order->orderDetails()->delete();
        $order->orderItems()->delete();
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully'
        ], 200);
    }

    public function makeAdmin($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->is_admin = 1;
        $user->email_verified_at = new \DateTime(now());
        $user->save();

        return response()->json([
            'message' => 'User has been made admin successfully',
            'user' => $user
        ], 200);
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->is_admin == 1) {
            return response()->json(['message' => 'Admin cannot delete an admin'], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
            'user' => $user
        ], 200);
    }

    public function getStatistics()
    {
        return response()->json([
            'activeCustomers' => Customer::where('status', 'active')->count() ?? 0,
            'paidOrders' => count($this->getPaidOrders()),
            'mostSoldProduct' => $this->theMostSoldProduct(),
            'countryWithMostOrders' => $this->getCountryWithMostOrders(),
            'totalIncome' => $this->getTotalIncome()
        ]);
    }

    public function getPaidOrders()
    {
        return Order::where('status', 'completed')->get();

    }

    public function getCountryWithMostOrders()
    {
        $mostOrderedCountry = OrderDetail::select('country_code', DB::raw('COUNT(*) AS order_count'))
            ->groupBy('country_code')
            ->orderBy('order_count', 'desc')
            ->first();

        if ($mostOrderedCountry) {
            return $mostOrderedCountry->country_code;
        }

        return null;
    }

    public function theMostSoldProduct()
    {
        $orders = $this->getPaidOrders();
        $mostSoldProducts = [];
        if ($orders) {
            foreach ($orders as $order) {
                foreach ($order->orderItems as $item) {
                    if (!isset($mostSoldProducts[$item->product->title])) {
                        $mostSoldProducts[$item->product->title] = $item->quantity;
                    } else {
                        $mostSoldProducts[$item->product->title] += $item->quantity;
                    }
                }
            }
        }

        arsort($mostSoldProducts);
        return key($mostSoldProducts);
    }

    public function getTotalIncome()
    {
        $orders = $this->getPaidOrders();
        $totalIncome = $orders->sum('total_price');

        return $totalIncome;
    }


}