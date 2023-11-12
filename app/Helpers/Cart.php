<?php

namespace App\Helpers;

use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Support\Arr;


class Cart
{

  public static function getCartItemsCount(): int
  {
    $request = \request();
    $user = $request->user;


    if (!$user) {
      $cartItems = self::getCookieCartItems();

      return array_reduce($cartItems, fn($carry, $item) => $carry + $item['quantity'], 0);
    }

    return CartItem::where('user_id', $user->id)->sum('quantity');
  }

  public static function getCartItems()
  {
    $request = \request();
    $user = $request->user;

    if (!$user) {
      return self::getCookieCartItems();
    }

    return CartItem::where('user_id', $user->id)->get()->map(fn($item) => ['product_id' => $item->product_id, 'quantity' => $item->quantity]);
  }

  public static function getCookieCartItems(): array
  {
    $request = \request();

    return json_decode($request->cookie('cart_items', '[]'), true);
  }


  public static function getCountFromItems($cartItems)
  {
    return array_reduce(
      $cartItems,
      fn($carry, $item) => $carry + $item['quantity'],
      0
    );
  }

  public static function moveCartItemsIntoDb()
  {
    $request = \request();
    $cartItems = self::getCookieCartItems();
    $dbCartItems = CartItem::where(['user_id' => $request->user->id])->get()->keyBy('product_id');
    $newCartItems = [];


    foreach ($cartItems as $cartItem) {
      if (isset($dbCartItems[$cartItem['product_id']])) {
        continue;
      }

      $newCartItems[] = [
        'user_id' => $request->user->id,
        'product_id' => $cartItem['product_id'],
        'quantity' => $cartItem['quantity'],
      ];
    }

    if (!empty($newCartItems)) {
      CartItem::insert($newCartItems);
    }
  }


  public static function getProductsAndCartItems(): array|\Illuminate\Database\Eloquent\Collection
  {
    $cartItems = self::getCartItems();
    $ids = Arr::pluck($cartItems, 'product_id');
    $products = Product::query()->whereIn('id', $ids)->get();
    $cartItems = Arr::keyBy($cartItems, 'product_id');

    return [$products, $cartItems];
  }

}