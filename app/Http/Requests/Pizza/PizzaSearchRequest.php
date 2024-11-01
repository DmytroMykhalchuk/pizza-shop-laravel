<?php

namespace App\Http\Requests\Pizza;

use Illuminate\Foundation\Http\FormRequest;

class PizzaSearchRequest extends FormRequest
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
            'search' => 'nullable|string|max:255',
        ];
    }

    public function getSearch(): string
    {
        return $this->get('search') ?? '';
    }
}
