<?php

declare(strict_types=1);
/**
 * Contains the OrderFactory class.
 *
 * @copyright   Copyright (c) 2017 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2017-12-02
 *
 */

namespace Vanilo\Framework\Factories;

use App\Models\Admin\Order;
use Vanilo\Checkout\Contracts\Checkout;
use Vanilo\Contracts\CheckoutSubject;
use Vanilo\Order\Factories\OrderFactory as BaseOrderFactory;
use Vanilo\Order\Models\OrderStatusProxy;

class OrderFactory extends BaseOrderFactory
{
	public function createFromCheckout(Checkout $checkout)
	{
		$cart = $checkout->getCart();

		$orderData = [
			'type'				=> $checkout->getType(),
			'user_id'			=> $checkout->getUserId(),
			'billpayer' 		=> $checkout->getBillpayer(),
			'shippingAddress' 	=> $checkout->getShippingAddress(),
			'customAttributes'	=> $checkout->getCustomAttributes(),
			'total' 			=> 0,
			'vat'               => 0,
			'adjustments'       => null,
		];

		if (null !== $cart) {
			$orderData['total'] = $cart->total();
			$orderData['totalWithCard'] = $cart->totalWithCard();
			$orderData['vat'] = $cart->vatTotal();
			$orderData['adjustments'] = $cart->adjustments();

			$items = $this->convertCartItemsToDataArray($cart);
		}

		return $this->createFromDataArray($orderData, $items ?? []);
	}

	public function createFromOrder(Order $order)
	{
		$orderData = [
			'type'				=> 'backoffice',
			'user_id'			=> $order->user_id,
			'customAttributes'	=> [
				'order_id' => $order->id
			],
			'total' 			=> $order->total(),
			'vat'               => $order->vatTotal(),
			'adjustments'       => $order->adjustments(),
			'status'			=> OrderStatusProxy::PENDING()->value()
		];

		$items = $order->items->map(function ($item) {
			return [
				'id'						=> $item->id,
				'type'			 			=> $item->product_type,
				'product' 		 			=> $item->getBuyable(),
				'adjustments' 	 			=> $item->adjustments(),
				'adjustments_collection' 	=> [],
				'quantity' 		 			=> $item->getQuantity(),
				'original_price' 			=> $item->getOriginalPrice(),
				'mod_price'		 			=> $item->getModifiedPrice(),
				'price'			 			=> $item->getAdjustedPrice(),
				'weight'		 			=> $item->product->weight()
			];
		})->all();

		return $this->createFromDataArray($orderData, $items ?? []);
	}

	protected function convertCartItemsToDataArray(CheckoutSubject $cart)
	{
		return $cart->getItems()->map(function ($item) {
			if ($item->product_type == 'product') {
				return [
					'id'						=> $item->id,
					'type'						=> $item->product_type,
					'product' 					=> $item->getBuyable(),
					'adjustments' 				=> $item->adjustments(),
					'adjustments_collection' 	=> [],
					'quantity' 					=> $item->getQuantity(),
					'price'						=> $item->getAdjustedPrice(),
					'weight'					=> $item->product->weight()
				];
			} else if ($item->product_type == 'prescription') {
				return [
					'type'	=> $item->product_type,
					'id' 	=> $item->product_id
				];
			}
		})->all();
	}
}
