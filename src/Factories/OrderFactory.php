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

use Vanilo\Checkout\Contracts\Checkout;
use Vanilo\Contracts\CheckoutSubject;
use Vanilo\Order\Factories\OrderFactory as BaseOrderFactory;

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
			'total' 			=> 0,
			'vat'               => 0,
			'adjustments'       => [],
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

	protected function convertCartItemsToDataArray(CheckoutSubject $cart)
	{
		return $cart->getItems()->map(function ($item) {
			return [
				'product' 		=> $item->getBuyable(),
				'adjustments' 	=> $item->adjustments(),
				'quantity' 		=> $item->getQuantity(),
				'price'			=> $item->getAdjustedPrice(),
				'weight'		=> $item->product->weight()
			];
		})->all();
	}
}
