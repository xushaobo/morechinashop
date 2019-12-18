<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OrderItem;

// implements ShouldQueue 代表此监听器是异步执行的
class UpdateProductSoldCount implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderCreated  $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        //从事件对象中取出对应的订单
        $order = $event->getOrder();
        //预加载商品数据
        $order->load('items.product');
        //循环遍历订单的商品
        foreach ($order->items as $item) {
            $product = $item->product;
            //计算对应商品的销量
            $soldCount = OrderItem::query()
                ->where('product_id',$product->id)
                ->whereHas('order',function ($query) {
                    $query->whereNotNull('created_at');
                })->sum('amount');
                //更新商品销量
            $product->update([
                'sold_count' => $soldCount,
            ]);
        }

    }
}
