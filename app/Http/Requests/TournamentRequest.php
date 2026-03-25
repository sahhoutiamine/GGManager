<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TournamentRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'game' => 'required|string|max:255',
            'start_date' => 'required|date|after:now',
            'max_participants' => 'required|integer|min:2',
            'format' => 'string|in:single elimination',
            'status' => 'string|in:open,closed,in_progress,finished',
        ];
    }
}
