<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Api\app\Resources\Api\Transaction\TransactionPaginateResource;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\Transaction\Enum\TransactionStatusEnum;

class TransactionController extends Controller
{
    use ApiHandlerTrait;
    public function index()
    {
        $user = auth()->user();
        $transactions = $user->appointments()->whereHas('transaction');


        //filter
        if (request()->has('filter')) {
            $filter = request()->get('filter');
            $validStatuses = collect(TransactionStatusEnum::all())->pluck('id')->toArray();
            $validStatuses = array_filter($validStatuses, function($value) {
                return $value !== TransactionStatusEnum::ALL->value;
            });

            if (in_array($filter, $validStatuses)) {
                $transactions->whereHas('transaction', function ($query) use ($filter) {
                    $query->where('status', $filter);
                });
            }
        }
        $transactions = $transactions->paginate();
        return $this->ok(new TransactionPaginateResource($transactions));
    }
}
