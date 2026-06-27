<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\ExpenseEntry;
use App\Models\IncomeCategory;
use App\Models\IncomeEntry;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function incomeCategories(Request $request): JsonResponse
    {
        $user = $request->user();

        $categories = IncomeCategory::query()
            ->where('society_id', $user->society_id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => true,
            'income_categories' => $categories,
        ]);
    }

    public function expenseCategories(Request $request): JsonResponse
    {
        $user = $request->user();

        $categories = ExpenseCategory::query()
            ->where('society_id', $user->society_id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => true,
            'expense_categories' => $categories,
        ]);
    }

    public function vendors(Request $request): JsonResponse
    {
        $user = $request->user();

        $vendors = Vendor::query()
            ->where('society_id', $user->society_id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => true,
            'vendors' => $vendors,
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $incomeEntries = IncomeEntry::query()
            ->with(['category', 'unit'])
            ->where('society_id', $user->society_id)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $expenseEntries = ExpenseEntry::query()
            ->with(['category', 'vendor'])
            ->where('society_id', $user->society_id)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $totalIncome = (float) IncomeEntry::query()
            ->where('society_id', $user->society_id)
            ->sum('amount');

        $totalExpense = (float) ExpenseEntry::query()
            ->where('society_id', $user->society_id)
            ->sum('amount');

        return response()->json([
            'status' => true,
            'summary' => [
                'total_income' => round($totalIncome, 2),
                'total_expense' => round($totalExpense, 2),
                'net_balance' => round($totalIncome - $totalExpense, 2),
            ],
            'income_entries' => $incomeEntries,
            'expense_entries' => $expenseEntries,
        ]);
    }

    public function storeIncomeCategory(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['admin', 'secretary', 'treasurer']), 403, 'Not allowed.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $category = IncomeCategory::create([
            'society_id' => $user->society_id,
            'name' => trim($validated['name']),
            'code' => isset($validated['code']) ? trim($validated['code']) : null,
        ]);

        return response()->json(['status' => true, 'income_category' => $category], 201);
    }

    public function updateIncomeCategory(Request $request, IncomeCategory $incomeCategory): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['admin', 'secretary', 'treasurer']), 403, 'Not allowed.');
        abort_unless($incomeCategory->society_id === $user->society_id, 403, 'Record does not belong to your society.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $incomeCategory->update([
            'name' => trim($validated['name']),
            'code' => isset($validated['code']) ? trim($validated['code']) : null,
        ]);

        return response()->json(['status' => true, 'income_category' => $incomeCategory]);
    }

    public function destroyIncomeCategory(Request $request, IncomeCategory $incomeCategory): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['admin', 'secretary', 'treasurer']), 403, 'Not allowed.');
        abort_unless($incomeCategory->society_id === $user->society_id, 403, 'Record does not belong to your society.');

        $incomeCategory->delete();

        return response()->json(['status' => true, 'message' => 'Income category deleted.']);
    }

    public function storeExpenseCategory(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['admin', 'secretary', 'treasurer']), 403, 'Not allowed.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $category = ExpenseCategory::create([
            'society_id' => $user->society_id,
            'name' => trim($validated['name']),
            'code' => isset($validated['code']) ? trim($validated['code']) : null,
        ]);

        return response()->json(['status' => true, 'expense_category' => $category], 201);
    }

    public function updateExpenseCategory(Request $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['admin', 'secretary', 'treasurer']), 403, 'Not allowed.');
        abort_unless($expenseCategory->society_id === $user->society_id, 403, 'Record does not belong to your society.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $expenseCategory->update([
            'name' => trim($validated['name']),
            'code' => isset($validated['code']) ? trim($validated['code']) : null,
        ]);

        return response()->json(['status' => true, 'expense_category' => $expenseCategory]);
    }

    public function destroyExpenseCategory(Request $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['admin', 'secretary', 'treasurer']), 403, 'Not allowed.');
        abort_unless($expenseCategory->society_id === $user->society_id, 403, 'Record does not belong to your society.');

        $expenseCategory->delete();

        return response()->json(['status' => true, 'message' => 'Expense category deleted.']);
    }

    public function updateIncome(Request $request, IncomeEntry $incomeEntry): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureEntryManager($authUser);
        $this->ensureSameSociety($authUser->society_id, $incomeEntry->society_id);

        // Auto-generated entries from maintenance payments should not be edited here.
        abort_if($incomeEntry->payment_receipt_id !== null, 422, 'This entry was auto-generated from a maintenance payment and cannot be edited here.');

        $validated = $request->validate([
            'income_category_id' => ['required', 'integer', 'exists:income_categories,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'entry_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $category = IncomeCategory::query()->findOrFail($validated['income_category_id']);
        $this->ensureSameSociety($authUser->society_id, $category->society_id);

        $incomeEntry->update([
            'income_category_id' => $validated['income_category_id'],
            'unit_id' => $validated['unit_id'] ?? null,
            'entry_date' => $validated['entry_date'],
            'amount' => $validated['amount'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Income entry updated.',
            'income_entry' => $incomeEntry->load('category', 'unit'),
        ]);
    }

    public function destroyIncome(Request $request, IncomeEntry $incomeEntry): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureEntryManager($authUser);
        $this->ensureSameSociety($authUser->society_id, $incomeEntry->society_id);

        abort_if($incomeEntry->payment_receipt_id !== null, 422, 'This entry was auto-generated from a maintenance payment and cannot be deleted here.');

        $incomeEntry->delete();

        return response()->json(['status' => true, 'message' => 'Income entry deleted.']);
    }

    public function updateExpense(Request $request, ExpenseEntry $expenseEntry): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureEntryManager($authUser);
        $this->ensureSameSociety($authUser->society_id, $expenseEntry->society_id);

        $validated = $request->validate([
            'expense_category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'entry_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'bill_no' => ['nullable', 'string', 'max:255'],
            'payment_mode' => ['nullable', 'string', 'max:50'],
            'reference_no' => ['nullable', 'string', 'max:255'],
        ]);

        $category = ExpenseCategory::query()->findOrFail($validated['expense_category_id']);
        $this->ensureSameSociety($authUser->society_id, $category->society_id);

        $expenseEntry->update([
            'expense_category_id' => $validated['expense_category_id'],
            'vendor_id' => $validated['vendor_id'] ?? null,
            'entry_date' => $validated['entry_date'],
            'amount' => $validated['amount'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'bill_no' => $validated['bill_no'] ?? null,
            'payment_mode' => $validated['payment_mode'] ?? null,
            'reference_no' => $validated['reference_no'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Expense entry updated.',
            'expense_entry' => $expenseEntry->load('category', 'vendor'),
        ]);
    }

    public function destroyExpense(Request $request, ExpenseEntry $expenseEntry): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureEntryManager($authUser);
        $this->ensureSameSociety($authUser->society_id, $expenseEntry->society_id);

        $expenseEntry->delete();

        return response()->json(['status' => true, 'message' => 'Expense entry deleted.']);
    }

    public function storeIncome(Request $request): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureEntryManager($authUser);

        $validated = $request->validate([
            'income_category_id' => ['required', 'integer', 'exists:income_categories,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'entry_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'visibility' => ['nullable', 'string', 'in:member,admin'],
        ]);

        $category = IncomeCategory::query()->findOrFail($validated['income_category_id']);
        $this->ensureSameSociety($authUser->society_id, $category->society_id);

        if (! empty($validated['unit_id'])) {
            $unit = Unit::query()->findOrFail($validated['unit_id']);
            $this->ensureSameSociety($authUser->society_id, $unit->society_id);
        }

        if (! empty($validated['user_id'])) {
            $user = User::query()->findOrFail($validated['user_id']);
            $this->ensureSameSociety($authUser->society_id, $user->society_id);
        }

        $entry = IncomeEntry::create([
            'society_id' => $authUser->society_id,
            'income_category_id' => $validated['income_category_id'],
            'unit_id' => $validated['unit_id'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'payment_receipt_id' => null,
            'entry_date' => $validated['entry_date'],
            'amount' => $validated['amount'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'visibility' => $validated['visibility'] ?? 'member',
            'created_by' => $authUser->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Income entry created successfully.',
            'income_entry' => $entry->load('category', 'unit', 'user'),
        ], 201);
    }

    public function storeExpense(Request $request): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureEntryManager($authUser);

        $validated = $request->validate([
            'expense_category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'entry_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'bill_no' => ['nullable', 'string', 'max:255'],
            'payment_mode' => ['nullable', 'string', 'max:50'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'visibility' => ['nullable', 'string', 'in:member,admin'],
        ]);

        $category = ExpenseCategory::query()->findOrFail($validated['expense_category_id']);
        $this->ensureSameSociety($authUser->society_id, $category->society_id);

        if (! empty($validated['vendor_id'])) {
            $vendor = Vendor::query()->findOrFail($validated['vendor_id']);
            $this->ensureSameSociety($authUser->society_id, $vendor->society_id);
        }

        $entry = ExpenseEntry::create([
            'society_id' => $authUser->society_id,
            'expense_category_id' => $validated['expense_category_id'],
            'vendor_id' => $validated['vendor_id'] ?? null,
            'entry_date' => $validated['entry_date'],
            'amount' => $validated['amount'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'bill_no' => $validated['bill_no'] ?? null,
            'payment_mode' => $validated['payment_mode'] ?? null,
            'reference_no' => $validated['reference_no'] ?? null,
            'visibility' => $validated['visibility'] ?? 'member',
            'created_by' => $authUser->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Expense entry created successfully.',
            'expense_entry' => $entry->load('category', 'vendor'),
        ], 201);
    }

    /**
     * GET /finance/report
     * Query params: from_date, to_date, unit_id (optional), type (income|expense|both, default: both)
     */
    public function report(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date', 'after_or_equal:from_date'],
            'unit_id'   => ['nullable', 'integer', 'exists:units,id'],
            'type'      => ['nullable', 'string', 'in:income,expense,both'],
        ]);

        $type    = $validated['type'] ?? 'both';
        $unitId  = $validated['unit_id'] ?? null;

        $incomeEntries  = collect();
        $expenseEntries = collect();

        if (in_array($type, ['income', 'both'], true)) {
            $query = IncomeEntry::query()
                ->with(['category', 'unit.wing'])
                ->where('society_id', $user->society_id)
                ->whereBetween('entry_date', [$validated['from_date'], $validated['to_date']])
                ->orderByDesc('entry_date')
                ->orderByDesc('id');

            if ($unitId !== null) {
                $query->where('unit_id', $unitId);
            }

            $incomeEntries = $query->get();
        }

        if (in_array($type, ['expense', 'both'], true)) {
            $query = ExpenseEntry::query()
                ->with(['category', 'vendor'])
                ->where('society_id', $user->society_id)
                ->whereBetween('entry_date', [$validated['from_date'], $validated['to_date']])
                ->orderByDesc('entry_date')
                ->orderByDesc('id');

            $expenseEntries = $query->get();
        }

        return response()->json([
            'status'          => true,
            'income_entries'  => $incomeEntries,
            'expense_entries' => $expenseEntries,
            'totals' => [
                'total_income'  => round((float) $incomeEntries->sum('amount'), 2),
                'total_expense' => round((float) $expenseEntries->sum('amount'), 2),
            ],
        ]);
    }

    private function ensureEntryManager(User $user): void
    {
        abort_unless($user->hasAnyRole(['admin', 'secretary', 'treasurer']), 403, 'Only admin, secretary, or treasurer can perform this action.');
    }

    private function ensureSameSociety(?int $expectedSocietyId, ?int $actualSocietyId): void
    {
        abort_unless($expectedSocietyId !== null && $expectedSocietyId === $actualSocietyId, 403, 'Record does not belong to your society.');
    }
}
