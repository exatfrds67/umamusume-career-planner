<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupportDeckRequest extends FormRequest
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
            'cards' => ['required', 'array', 'size:6'],
            'cards.*.support_card_id' => ['required', 'integer', 'exists:ucp_support_cards,id'],
            'cards.*.is_friend_card' => ['required', 'boolean'],
            'cards.*.limit_break_level' => ['nullable', 'integer', 'min:0', 'max:4'],
            'cards.*.friendship_level' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cards.required' => 'Support deck cards are required.',
            'cards.size' => 'Support deck must contain exactly 6 cards.',
            'cards.*.support_card_id.required' => 'Each card must have a valid support card ID.',
            'cards.*.support_card_id.exists' => 'One or more support cards do not exist.',
            'cards.*.is_friend_card.required' => 'Friend card status must be specified for each card.',
            'cards.*.limit_break_level.max' => 'Limit break level cannot exceed 4.',
            'cards.*.friendship_level.max' => 'Friendship level cannot exceed 100.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Contracts\Validation\Validator $validator): void {
            $cards = $this->input('cards', []);

            if (! is_array($cards)) {
                return;
            }

            // Check friend card count
            $friendCardCount = collect($cards)->where('is_friend_card', true)->count();
            if ($friendCardCount > 1) {
                $validator->errors()->add('cards', 'Deck can only have 1 friend card.');
            }

            // Check for duplicate owned cards
            $ownedCardIds = collect($cards)
                ->where('is_friend_card', false)
                ->pluck('support_card_id')
                ->toArray();

            if (count($ownedCardIds) !== count(array_unique($ownedCardIds))) {
                $validator->errors()->add('cards', 'Deck cannot contain duplicate cards (except friend cards).');
            }
        });
    }
}
