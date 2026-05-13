<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface {
    protected $model;
    public function __construct(Order $order) {
        $this->model = $order;
    }

    public function getAll(?string $searchTerm = '', int|null $page = null, int|null $limit = null) {
        if ($searchTerm) {
            $scout = $this->model->search($searchTerm);
            $scout->query(function ($query) {
                $query->with(['vendorOrders.items', 'user']);
            });
            $scout->orderBy('created_at', 'desc');

            if ($page && $limit) {
                $paginator = $scout->paginate($limit, 'page', $page);
                $orders = collect($paginator->items());
            } else {
                $orders = $scout->get();
            }
        } else {
            $query = $this->model->query()
                ->with(['vendorOrders.items', 'user'])
                ->orderBy('created_at', 'desc');

            if ($page && $limit) {
                $query->offset(($page - 1) * $limit)->limit($limit);
            }

            $orders = $query->get();
        }

        // flatten the images
        $orders->transform(function ($order) {
            $order->setAttribute('items', $order->vendorOrders->flatMap->items);
            $order->items->transform(function ($item) {
                $item->product->image = $item->product->getFirstMediaUrl('images', 'thumb');
                return $item;
            });
            return $order;
        });
        return $orders->transform(function ($order) {
            $order->setAttribute('items_count', $order->vendorOrders->flatMap->items->count());
            return $order;
        });
    }

    public function getForVendorId(string $vendorId, ?string $searchTerm = '') {
        if ($searchTerm) {
            $scout = $this->model->search($searchTerm);
            $scout->query(function ($query) use ($vendorId) {
                $query->with(['vendorOrders.items', 'user'])
                    ->whereHas('vendorOrders', function ($query) use ($vendorId) {
                        $query->where('vendor_id', $vendorId);
                    });
            });
            $scout->orderBy('created_at', 'desc');
            $orders = $scout->get();
        } else {
            $orders = $this->model->query()
                ->with(['vendorOrders.items', 'user'])
                ->whereHas('vendorOrders', function ($query) use ($vendorId) {
                    $query->where('vendor_id', $vendorId);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $orders->transform(function ($order) {
            $order->setAttribute('items', $order->vendorOrders->flatMap->items);
            $order->items->transform(function ($item) {
                $item->product->image = $item->product->getFirstMediaUrl('images', 'thumb');
                return $item;
            });
            return $order;
        });
        return $orders->transform(function ($order) {
            $order->setAttribute('items_count', $order->vendorOrders->flatMap->items->count());
            return $order;
        });
    }

    public function findById(string $id)
    {
        return $this->model->with('vendorOrders.items')->findOrFail($id);
    }

    public function getPaginatedForUser(string $userId, int $perPage = 15)
    {
        // 1. Eager load the items AND the associated product for each item
        $paginator = $this->model->with('vendorOrders.items.product')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // 2. Flatten the items from the nested vendorOrders so the frontend can read `order.items`
        $paginator->getCollection()->transform(function ($order) {
            $order->setAttribute('items', $order->vendorOrders->flatMap->items);
            return $order;
        });

        return $paginator->all();
    }

    public function getOrderFullDetails(string $orderId)
    {
        $order = $this->model->with('vendorOrders.items.product')->findOrFail($orderId);
        // flatten the items
        $order->setAttribute('items', $order->vendorOrders->flatMap->items);
        $order->items->transform(function ($item) {
            $item->product->image = $item->product->getFirstMediaUrl('images', 'thumb');
            return $item;
        });
        return $order;
    }

    public function create($data) {
       return $this->model->create([
           'user_id' => $data['user_id'],
           'total_price' => $data['total_price'],
           'payment_method' => $data['payment_method'],
           'shipping_address' => $data['shipping_address'],
           'shipping_phone' => $data['shipping_phone'],
           'status' => 'pending',
       ]);
    }

    public function createVendorOrder($order, $data) {
        return $order->vendorOrders()->create($data);
    }

    public function insertVendorOrderItems($vendorOrder, $items) {
        return $vendorOrder->items()->createMany($items);
    }

    public function updateOrderStatus(string $orderId, string $status)
    {
        return $this->model->query()->findOrFail($orderId)->update(['status' => $status]);
    }
}
