<?php

namespace Modules\Sale\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CloseCashRegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'closing_amount_counted' => 'required|numeric|min:0',
            'closing_note' => 'nullable|string|max:1000',
        ];
    }

    public function authorize(): bool
    {
        return Gate::allows('create_pos_sales');
    }
}
