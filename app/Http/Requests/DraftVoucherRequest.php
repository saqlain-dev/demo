<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DraftVoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Amount' => 'required|numeric',
            'Date' => 'required|date',
            'VoucherType' => 'required',
            'VoucherTypeID' => 'required|integer',
            'FinancialYear' => 'required',
            'narration' => 'nullable|string|max:255',
            'Instrument_Id' => 'nullable|string|max:50',
            'payable_to' => 'nullable',
            'bank_account' => 'nullable',
            'VoucherFrom' => 'nullable',
            'VoucherFromID' => 'nullable',
            'project_id' => 'nullable',
            'vendor_id' => 'nullable',
            //'vendor_id' => 'required',
           // 'debits' => 'required|array',
            //'debits.*.account_id' => 'required|integer',
            //'debits.*.amount' => 'required|numeric',
            //'debits.*.description' => 'nullable|string|max:255',
            //'debits.*.NominalClass' => 'nullable|string|max:255',
           // 'debits.*.NominalClassID' => 'nullable|numeric',
           // 'credits' => 'required|array',
            //'credits.*.account_id' => 'required|integer',
            //'credits.*.amount' => 'required|numeric',
            //'credits.*.description' => 'nullable|string|max:255',
            //'credits.*.NominalClass' => 'nullable|string|max:255',
           // 'credits.*.NominalClassID' => 'nullable|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'Amount.required' => 'Voucher amount is required',
            'Date.required' => 'Voucher date is required',
            'VoucherType.required' => 'Voucher type is required',
            'FinancialYear.required' => 'Financial year is required',
            'Date.date' => 'Voucher date must be a valid date',
            //'payable_to' => 'Payable to is required',
            //'bank_account' => 'Bank account is required',
            // 'vendor_id.required' => 'Vendor Id is required',
            //'debits.required' => 'Debits are required',
            //'credits.required' => 'Credits are required',
            //'debits.*.account_id.required' => 'Each debit account ID is required',
            //'debits.*.amount.required' => 'Each debit amount is required',
            //'credits.*.account_id.required' => 'Each credit account ID is required',
            //'credits.*.amount.required' => 'Each credit amount is required',
        ];
    }
}
