<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreExpenseRequest;
use App\Http\Requests\Api\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends CrudController
{
    protected string $model = Expense::class;
    protected string $resource = ExpenseResource::class;

    public function store(StoreExpenseRequest $request) { return $this->createRecord($request, $request->validated()); }
    public function show(Request $request, Expense $expense) { return $this->showRecord($request, $expense); }
    public function update(UpdateExpenseRequest $request, Expense $expense) { return $this->updateRecord($request, $expense, $request->validated()); }
    public function destroy(Request $request, Expense $expense) { return $this->destroyRecord($request, $expense); }
    public function paid(Request $request, Expense $expense) { return $this->updateRecord($request, $expense, ['status' => 'pago', 'paid_at' => now()->toDateString()]); }
}
