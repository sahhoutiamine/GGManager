<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTournamentRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'game' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date|after:now',
            'max_participants' => 'sometimes|integer|min:2',
            // Notice: status and format are explicitly NOT allowed here
        ];
    }
}
