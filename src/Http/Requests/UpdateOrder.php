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

use App\Models\Admin\ShipmentMethod;
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

class UpdateOrder extends FormRequest implements UpdateOrderContract
{
	public function rules()
	{
		$rules = [
			'status' => [Rule::in(OrderStatusProxy::values())]
		];

		if ($this->get('type') == 'user') {
			$rules['user_id'] = ['required', 'exists:users,id'];
		} else if ($this->get('type') == 'addresses') {
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
		} else if ($this->get('type') == 'shipping-method') {
			$rules['shipping.id'] = ['required', 'exists:shipment_methods,id'];

			$shippingMethod = $this->shippingMethod();

			if (null !== $shippingMethod && $shippingMethod->isStorePickup() && count($shippingMethod->stores) > 0) {
				$rules['store.id'] = ['required', 'exists:stores,id'];
			}
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
		return CountryProxy::find($this['shipping_country_id']);
	}

	public function billingCountry(): Country
	{
		return CountryProxy::find($this['billing_country_id']);
	}

	public function client(): User
	{
		return User::find($this->get('user_id'));
	}

	public function shippingMethod(): ?ShipmentMethod
	{
		return ShipmentMethod::find($this->shipping['id']);
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
		];
	}

	public function authorize()
	{
		return true;
	}
}
