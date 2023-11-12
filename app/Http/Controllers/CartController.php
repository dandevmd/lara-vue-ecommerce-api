<?php

namespace App\Http\Controllers;

use Cookie;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Helpers\Cart;

class CartController extends Controller
{
    public function index()
    {
        [$products, $cartItems] = Cart::getProductsAndCartItems();

        $total = 0;
        foreach ($products as $product) {
            $total += $product->price * $cartItems[$product->id]['quantity'];
        }

        return response()->json([
            'products' => $products,
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }


    public function add(Request $request, Product $product)
    {
        $quantity = $request->input('quantity', 1);
        $user = $request->user;



        // Check if user is logged in
        if (!$user) {
            $cartItems = Cart::getCookieCartItems();
            $productFound = false;

            foreach ($cartItems as &$cartItem) {
                if ($cartItem['product_id'] === $product->id) {
                    $cartItem['quantity'] = $cartItem['quantity'] + $quantity;
                    $productFound = true;
                    break;
                }
            }

            if (!$productFound) {
                $cartItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'user_id' => null
                ];
            }

            setcookie('cart_items', json_encode($cartItems), time() + (60 * 60 * 24 * 30));

            return response(['count' => Cart::getCartItemsCount()]);
        }

        // Check if cart item already exists for logged-in user
        $cartItem = CartItem::where(['user_id' => $user->id, 'product_id' => $product->id])->first();

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $cartItem->quantity + $quantity
            ]);
        } else {
            CartItem::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => $quantity
            ]);
        }

        return response(['count' => Cart::getCartItemsCount()]);
    }


    public function update(Request $request, Product $product)
    {
        $quantity = $request->input('quantity', 1);
        $user = $request->user;

        if (!$user) {
            $cartItems = Cart::getCookieCartItems();

            foreach ($cartItems as &$cartItem) {
                if ($cartItem['product_id'] === $product->id) {
                    $cartItem['quantity'] = $quantity;
                }
            }

            setcookie('cart_items', json_encode($cartItems), time() + (60 * 60 * 24 * 30));

            return response(['count' => Cart::getCartItemsCount()]);
        }

        $cartItem = CartItem::where(['user_id' => $user->id, 'product_id' => $product->id])->first();

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $quantity
            ]);

            return response(['count' => Cart::getCartItemsCount()]);
        }
    }

    public function remove(Request $request, Product $product)
    {
        $user = $request->user;

        if (!$user) {
            $cartItems = self::getCookieCartItems();
            $productFound = false;

            foreach ($cartItems as $key => $cartItem) {
                if ($cartItem['product_id'] == $product->id) {
                    unset($cartItems[$key]);
                    $productFound = true;
                    break;
                }
            }

            setcookie('cart_items', json_encode($cartItems), time() + (60 * 60 * 24 * 30));

            return response(['count' => Cart::getCountFromItems($cartItems)]);
        }

        $cartItem = CartItem::where(['user_id' => $user->id, 'product_id' => $product->id])->first();
        if ($cartItem) {
            $cartItem->delete();
        }

        return response(['cartItems' => CartItem::where('user_id', $user->id)->get()]);
    }
}