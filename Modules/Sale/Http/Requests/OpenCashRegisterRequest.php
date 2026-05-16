<?php

namespace Modules\Sale\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class OpenCashRegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'opening_amount' => 'required|numeric|min:0',
            'opening_note' => 'nullable|string|max:1000',
        ];
    }

    public function authorize(): bool
    {
        return Gate::allows('create_pos_sales');
    }
}
