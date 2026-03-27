<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Real authorization is handled by MatchPolicy via the controller.
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
        /** @var \App\Models\TournamentMatch $match */
        $match = $this->route('match');

        return [
            'score'     => [
                'required',
                'string',
                'regex:/^[0-9]+-[0-9]+$/',
                function ($attribute, $value, $fail) {
                    [$score1, $score2] = explode('-', $value);
                    if ($score1 === $score2) {
                        $fail('The score cannot be a tie; there must be a clear winner.');
                    }
                },
            ],
            'winner_id' => [
                'required',
                'integer',
                'exists:users,id',
                'in:' . $match->player1_id . ',' . $match->player2_id,
            ],
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'winner_id.in' => 'The winner must be one of the two players in this match.',
        ];
    }
}
