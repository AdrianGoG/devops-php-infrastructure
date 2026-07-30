<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:5000'],
            'requester' => ['required', 'email', 'max:180'],
            'priority' => ['required', Rule::in(Ticket::PRIORITIES)],
            'status' => ['required', Rule::in(Ticket::STATUSES)],
            'assignee' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subject.required' => 'The subject is required.',
            'description.required' => 'Describe the problem.',
            'requester.required' => 'The requester email is required.',
            'requester.email' => 'That email address is not valid.',
            'priority.in' => 'Choose one of the available priorities.',
            'status.in' => 'Choose one of the available statuses.',
        ];
    }
}
