<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreVoucherRequest;
use App\Http\Requests\UpdateVoucherRequest;
use App\Repositories\Interfaces\VoucherRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VoucherController extends Controller
{
    protected $voucherRepository;

    public function __construct(VoucherRepositoryInterface $voucherRepository)
    {
        $this->voucherRepository = $voucherRepository;
    }

    public function index()
    {
        $vouchers = $this->voucherRepository->getAllForVendor(Auth::id());
        return Inertia::render('Admin/Voucher/Index', [
            'vouchers' => $vouchers
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Voucher/Create');
    }

    public function store(StoreVoucherRequest $request)
    {
        $validated = $request->validated();
        $this->voucherRepository->createForVendor(Auth::id(), $validated);
        
        return redirect()->route('admin.vouchers.index');
    }

    public function edit(string $id)
    {
        $voucher = $this->voucherRepository->findByIdAndVendor($id, Auth::id());
        return Inertia::render('Admin/Voucher/Edit', [
            'voucher' => $voucher
        ]);
    }

    public function update(UpdateVoucherRequest $request, string $id)
    {
        // Ensure the voucher belongs to the vendor before updating
        $this->voucherRepository->findByIdAndVendor($id, Auth::id());
        $this->voucherRepository->update($id, $request->validated());

        return redirect()->route('admin.vouchers.index');
    }

    public function destroy(string $id)
    {
        // Ensure the voucher belongs to the vendor before deleting
        $this->voucherRepository->findByIdAndVendor($id, Auth::id());
        $this->voucherRepository->delete($id);

        return redirect()->route('admin.vouchers.index');
    }
}
