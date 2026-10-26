<?php

namespace App\Http\Requests;

use App\Models\GameSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class GameSessionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['date', 'start_time'])) {
                    return;
                }

                if ($this->startsAt()->isPast()) {
                    $validator->errors()->add('date', 'The session must start in the future.');
                }
            },
        ];
    }

    /**
     * Attributes ready to be saved on a GameSession.
     *
     * @return array{title: string, starts_at: Carbon}
     */
    public function sessionAttributes(): array
    {
        return [
            'title' => $this->validated('title') ?? GameSession::DEFAULT_TITLE,
            'starts_at' => $this->startsAt(),
        ];
    }

    private function startsAt(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i', $this->input('date').' '.$this->input('start_time'));
    }
}
