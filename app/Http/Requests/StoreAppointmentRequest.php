<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Телефон приводим к виду +7XXXXXXXXXX, чтобы мастер видел одинаковый формат.
        $digits = preg_replace('/\D+/', '', (string) $this->input('client_phone'));
        if (strlen($digits) === 11 && in_array($digits[0], ['7', '8'], true)) {
            $digits = '7'.substr($digits, 1);
        }
        $this->merge(['client_phone' => $digits !== '' ? '+'.$digits : '']);
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'master_id' => ['required', 'integer', 'exists:masters,id'],
            'starts_at' => ['required', 'date_format:Y-m-d H:i', 'after:now'],
            'client_name' => ['required', 'string', 'min:2', 'max:80'],
            'client_phone' => ['required', 'regex:/^\+7\d{10}$/'],
            'comment' => ['nullable', 'string', 'max:500'],
            'consent' => ['accepted'],
            'website' => ['prohibited'], // honeypot от ботов
        ];
    }

    public function messages(): array
    {
        return [
            'client_phone.regex' => 'Укажите телефон в формате +7 900 000-00-00.',
            'consent.accepted' => 'Нужно согласие на обработку персональных данных.',
            'starts_at.after' => 'Это время уже прошло, выберите другое.',
        ];
    }
}
