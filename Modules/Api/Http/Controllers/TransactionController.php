<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Api\app\Resources\Api\Transaction\TransactionPaginateResource;
use Modules\Api\Trait\ApiHandlerTrait;

class TransactionController extends Controller
{
    use ApiHandlerTrait;
    public function index()
    {
        $user = auth()->user();
        $transactions = $user->appointments()->whereHas('transaction')->paginate();
        return $this->ok(new TransactionPaginateResource($transactions));
    }
}
