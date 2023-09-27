<?php

declare(strict_types=1);
/**
 * Contains the UpdateSalesFigures class.
 *
 * @copyright   Copyright (c) 2018 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2018-11-11
 *
 */

namespace Vanilo\Framework\Listeners;

use Vanilo\Contracts\Buyable;
use Vanilo\Order\Contracts\OrderAwareEvent;
use Vanilo\Order\Contracts\OrderItem;
use ReflectionClass;
use Vanilo\Order\Models\OrderStatusProxy;

class UpdateSalesFigures
{
    public function handle(OrderAwareEvent $event)
    {
        $order = $event->getOrder();

        if((new ReflectionClass($event))->getShortName() == "OrderWasCreated" || (new ReflectionClass($event))->getShortName() == "OrderWasCancelled" || $order->status == OrderStatusProxy::CANCELLED()) {
            foreach ($order->getItems() as $item) {
                /** @var OrderItem $item */
                if ($item->product instanceof Buyable) {
                    
                    if ($item->quantity >= 0 && (new ReflectionClass($event))->getShortName() != "OrderWasCancelled") {
                        $item->product->addSale($order->created_at, $item->quantity);
                    } else {
                        $item->product->removeSale($item->quantity);
                    }
                }
            }
        }
    }
}
