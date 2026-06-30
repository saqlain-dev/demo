<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AirTravelRequestDetailRequest extends FormRequest
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
            'parent_id' => 'required|integer|exists:air_travel_requests,id',
            'date' => 'required',
            //'departure_from' => 'required',
            'cnic' => 'required',
            'seat_name' => 'required',
            'traveller_name' => 'required',
            'department_id' => 'required',
            //'act_code' => 'required',
            //'donor_code' => 'required',
            'purpose' => 'required',
            'estimated_amount' => 'required',
            'revised_amount' => 'required',
        ];
    }
}
