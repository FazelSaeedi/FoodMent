<?php


namespace App\Repository\OrderRepository;


use App\Models\Order;
use App\Models\OrderItem;

class EloquentOrderRepository implements OrderRepositoryInterface
{



    public function getNewRestrauntOrders( $restrauntCode )
    {
        return 'this is getNewRestrauntOrders from Eloquent Repository' ;
    }



    public function getAllRestrauntOrders( $restrauntCode )
    {
        $getAllRestrauntOrders = Order::with(['OrderItems' => function($q){

            $q->select(
                'menu.product_id','order_id' , 'menuproductId' , 'count' ,
                'order_items.price' , 'discountrate' , 'totalprice' , 'products.name'
            );
            $q->join('menu', 'menu.id', '=', 'order_items.menuproductId');
            $q->join('products', 'products.id', '=', 'menu.id');
            //INNER JOIN `products` ON `products`.`id` = `menu`.`id`

        }])
            ->where('restraunt_code' , $restrauntCode )
            ->where('isrestrauntconfirmed' , false)
            ->get([
                'id' , 'userid' , 'totalamount' , 'totalprice'
                , 'isuserrequested', 'isrestrauntaccepted', 'isCanceled' ,
                'ispaid' , 'isdelivered' , 'isrestrauntconfirmed'
            ]);

        return $getAllRestrauntOrders ;
    }


    public function getNewOrders($restrauntCode)
    {
        // TODO: Implement getNewOrders() method.
    }

    public function getNewOrderItems($restrauntCode)
    {
        // TODO: Implement getNewOrderItems() method.
    }

    public function getAllOrders($restrauntCode)
    {
        $getAllOrders = Order::where('restraunt_code' , $restrauntCode )
                        ->where('isrestrauntconfirmed' , false)
                        ->get([
                            'id' , 'userid' , 'totalamount' , 'totalprice'
                            , 'isuserrequested', 'isrestrauntaccepted', 'isCanceled' ,
                            'ispaid' , 'isdelivered' , 'isrestrauntconfirmed'
                        ]);

        return $getAllOrders ;
    }

    public function getAllOrderItems($restrauntCode)
    {
        $getAllOrderItems = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
                                       ->where('restraunt_code' , $restrauntCode )
                                       ->get([
                                           'order_id' , 'menuproductId' , 'count' ,
                                           'order_items.price' , 'discountrate' , 'order_items.totalprice'
                                       ]);

        return $getAllOrderItems ;
    }
}
