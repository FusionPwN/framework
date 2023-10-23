<?php

declare(strict_types=1);
/**
 * Contains the UpdateOrder class.
 *
 * @copyright   Copyright (c) 2017 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2017-12-17
 *
 */

namespace Vanilo\Framework\Http\Requests;

use App\Models\Admin\Coupon;
use App\Models\Admin\Order as AdminOrder;
use App\Models\Admin\ShipmentMethod;
use App\Models\Admin\Store;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Vanilo\Framework\Contracts\Requests\UpdateOrder as UpdateOrderContract;
use Vanilo\Order\Contracts\Order;
use Vanilo\Order\Models\OrderStatusProxy;
use App\Rules\IsValidNIF;
use App\Rules\IsValidPostalCodePTC;
use Konekt\Address\Models\Country;
use Konekt\Address\Models\CountryProxy;
use Vanilo\Framework\Models\PaymentMethod;
use Vanilo\Order\Models\OrderProxy;

class UpdateOrder extends FormRequest implements UpdateOrderContract
{
	public function rules()
	{
		$rules = [
			'status' => [Rule::in(OrderStatusProxy::values())]
		];

		switch ($this->get('type')) {
			case 'user':
				$rules['user_id'] = ['required', 'exists:users,id'];
				break;
			case 'addresses':
				$country = $this->shippingCountry();

				$rules['email'] 				= ['required_unless:user_id,null', 'min:2', 'max:255', 'email:rfc,dns'];
				$rules['phone'] 				= ['required', 'phone:' . $country->iso];
				$rules['shipping_firstname'] 	= ['required', 'min:2', 'max:255'];
				$rules['shipping_lastname']  	= ['required', 'min:2', 'max:255'];
				$rules['shipping_address'] 		= ['required', 'min:2', 'max:255'];
				$rules['shipping_city'] 		= ['required', 'min:2', 'max:255'];
				$rules['shipping_country_id'] 	= ['required', 'exists:countries,id'];
				$rules['shipping_postalcode'] 	= ['required', 'postal_code:' . $country->iso, new IsValidPostalCodePTC($country)];

				$country = $this->billingCountry();

				$rules['billing_firstname'] 	= ['required_with:billing_lastname,billing_address,billing_city,billing_postalcode,nif', 'max:255'];
				$rules['billing_lastname']  	= ['required_with:billing_firstname,billing_address,billing_city,billing_postalcode,nif', 'max:255'];
				$rules['billing_address'] 		= ['required_with:billing_firstname,billing_lastname,billing_city,billing_postalcode,nif', 'max:255'];
				$rules['billing_city'] 			= ['required_with:billing_firstname,billing_lastname,billing_address,billing_postalcode,nif', 'max:255'];
				$rules['billing_country_id'] 	= ['required_with:billing_firstname,billing_lastname,billing_address,billing_city,billing_postalcode,nif', 'exists:countries,id'];
				$rules['billing_postalcode'] 	= ['required_with:billing_firstname,billing_lastname,billing_address,billing_city,nif'];
				$rules['nif'] 					= ['required_with:billing_firstname,billing_lastname,billing_address,billing_city,billing_postalcode'];

				if ($this->get('billing_postalcode') != '' && $this->get('billing_postalcode') !== null) {
					array_push($rules['billing_postalcode'], 'postal_code:' . $country->iso, new IsValidPostalCodePTC($country));
				}
				if ($country->iso == 'PT' && $this->get('nif') != '' && $this->get('nif') !== null) {
					array_push($rules['nif'], new IsValidNIF);
				}
				break;
			case 'shipping-method':
				$rules['shipping.id'] = ['required', 'exists:shipment_methods,id'];

				if (isset($this->shipping['id'])) {
					$shippingMethod = $this->shippingMethod();

					if (null !== $shippingMethod && $shippingMethod->isStorePickup() && count($shippingMethod->stores) > 0) {
						$rules['store.id'] = ['required', 'exists:stores,id'];
					}
				}
				break;
			case 'payment-method':
				$rules['payment.id'] = ['required', 'exists:payment_methods,id'];

				if (isset($this->payment['id'])) {
					$paymentMethod = $this->paymentMethod();

					if (null !== $paymentMethod && strtolower($paymentMethod->getConfigurationValue('SERVICE')) == 'mbw') {
						$order = $this->order();
						if ($order->isSimpleBilling()) {
							$country_service = $order->country;
						} else {
							$country_service = $order->billingCountry;
						}

						$rules['mbway_phone'] = ['required', 'phone:' . $country_service->iso ?? ''];
					}
				}
				break;
			case 'order-items':
				$rules['items'] = ['required_without:itemsalt'];
				$rules['itemsalt'] = ['required_without:items'];
				break;
			case 'add-coupon':
				if (isset($this->coupon_code)) {
					$rules['coupon_code'] = ['exists:coupons,code'];
				} else if (isset($this->coupon_select) && $this->coupon_select !== 'no-code') {
					$rules['coupon_select'] = ['exists:coupons,code'];
				} else {
					$rules['coupon_code'] = ['required'];
					$rules['coupon_select'] = ['required'];
				}

				break;

			default:
				break;
		}

		return $rules;
	}

	public function wantsToChangeOrderStatus(Order $order): bool
	{
		return $this->getStatus() !== $order->getStatus()->value() && in_array($this->getStatus(), OrderStatusProxy::values());
	}

	public function getStatus(): string
	{
		return $this->get('status', '');
	}

	public function shippingCountry(): Country
	{
		return CountryProxy::find($this->shipping_country_id);
	}

	public function billingCountry(): Country
	{
		return CountryProxy::find($this->billing_country_id);
	}

	public function client(): User
	{
		return User::find($this->get('user_id'));
	}

	public function shippingMethod(): ?ShipmentMethod
	{
		return ShipmentMethod::find($this->shipping['id']);
	}

	public function paymentMethod(): ?PaymentMethod
	{
		return PaymentMethod::find($this->payment['id']);
	}

	public function store(): ?Store
	{
		return Store::find($this->store['id']);
	}

	public function order(): AdminOrder
	{
		return OrderProxy::find($this->order);
	}

	public function coupon()
	{
		if (isset($this->coupon_code)) {
			return Coupon::where('code', $this->coupon_code)->first();
		} else if (isset($this->coupon_select) && $this->coupon_select !== 'no-code') {
			return Coupon::where('code', $this->coupon_select)->first();
		}
	}

	public function attributes()
	{
		return [
			'status' 				=> __('backoffice.order.status'),
			'email' 				=> __('backoffice.order.email'),
			'phone' 				=> __('backoffice.order.phone'),
			'shipping_firstname' 	=> __('backoffice.order.firstname'),
			'shipping_lastname' 	=> __('backoffice.order.lastnamee'),
			'shipping_address' 		=> __('backoffice.order.address'),
			'shipping_city' 		=> __('backoffice.order.city'),
			'shipping_country_id' 	=> __('backoffice.order.country'),
			'shipping_postalcode' 	=> __('backoffice.order.postalcode'),
			'billing_firstname' 	=> __('backoffice.order.firstname'),
			'billing_lastname' 		=> __('backoffice.order.lastname'),
			'nif' 					=> __('backoffice.order.tax-number'),
			'billing_address' 		=> __('backoffice.order.address'),
			'billing_city' 			=> __('backoffice.order.city'),
			'billing_country_id' 	=> __('backoffice.order.country'),
			'billing_postalcode' 	=> __('backoffice.order.postalcode'),
			'shipping.id' 			=> strtolower(__('frontoffice.shipping_method')),
			'store.id' 				=> strtolower(__('frontoffice.pick-up shop')),
			'mbway_phone' 			=> strtolower(__('frontoffice.phone')),
			'payment.id' 			=> strtolower(__('frontoffice.payment_method')),
			'coupon_code' 			=> strtolower(__('frontoffice.coupon_checkout')),
			'coupon_select' 		=> strtolower(__('frontoffice.coupon_checkout')),
		];
	}

	public function messages(): array
	{
		return [
			'items.required' => __('backoffice.order.edit-items-error')
		];
	}

	public function authorize()
	{
		return true;
	}
}
