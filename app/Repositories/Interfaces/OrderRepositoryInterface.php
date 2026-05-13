<?php

namespace App\Repositories\Interfaces;

interface OrderRepositoryInterface {
    public function getAll(?string $searchTerm = '', int|null $page = null, int|null $limit = null);
    public function getForVendorId(string $vendorId, ?string $searchTerm = '');
    public function findById(string $id);
    public function getPaginatedForUser(string $userId, int $perPage = 15);
    public function getOrderFullDetails(string $orderId);
    public function updateOrderStatus(string $orderId, string $status);
}
