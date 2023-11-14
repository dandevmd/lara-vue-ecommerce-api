<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderDetail;
use Illuminate\Http\Request;

class OrdersController extends Controller
{

    protected $orderStatuses = ['pending', 'completed', 'cancelled'];
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        //get all users order with order details and with order items
        $orders = Order::where('created_by', $user->id)
            ->with('orderDetails')
            ->with('orderItems')
            ->orderBy('created_at', 'desc')
            ->get();

        if (!$orders) {
            return response()->json(['message' => 'No orders found'], 404);
        }

        return response()->json($orders, 200);

    }

    public function allUsersOrders(Request $request)
    {
        $orders = Order::all()->orderBy('created_at', 'desc');
        if (!$orders) {
            return response()->json(['message' => 'No orders found'], 404);
        }
        return response()->json($orders, 200);
    }

    public function getOrderById(Request $request, $id)
    {
        $user = $request->user();

        $order = Order::where('id', $id)
            ->with('orderDetails')
            ->with('orderItems')
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($user->id === $order->created_by || $user->is_admin === true) {
            return response()->json($order, 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'total_price' => 'required|numeric',
            'order_details' => 'required|array',
            'order_items' => 'required|array'
        ]);



        // Create a new order and set the status to "pending"
        $order = Order::create([
            'total_price' => $validatedData['total_price'],
            'status' => 'pending',
            'created_by' => auth()->user()->id,
        ]);

        // Create order items and associate them with the order
        foreach ($validatedData['order_items'] as $orderItem) {
            $orderItem = OrderItem::create([
                'product_id' => $orderItem['product_id'],
                'order_id' => $order->id,
                'quantity' => $orderItem['quantity'],
                'unit_price' => $orderItem['unit_price'],
            ]);

            // Associate order items with the order
            $order->orderItems()->save($orderItem);
        }

        // Create order details and associate them with the order
        foreach ($validatedData['order_details'] as $orderDetailData) {
            $orderDetail = OrderDetail::create([
                'first_name' => $orderDetailData['first_name'],
                'last_name' => $orderDetailData['last_name'],
                'phone' => $orderDetailData['phone'],
                'address1' => $orderDetailData['address1'],
                'address2' => $orderDetailData['address2'],
                'city' => $orderDetailData['city'],
                'state' => $orderDetailData['state'],
                'zipcode' => $orderDetailData['zipcode'],
                'country_code' => $orderDetailData['country_code'],
            ]);

            // Associate order details with the order
            $order->orderDetails()->save($orderDetail);
        }



        // Return a success response
        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order
        ], 201);
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

    public function delete(Request $request, $id)
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

}