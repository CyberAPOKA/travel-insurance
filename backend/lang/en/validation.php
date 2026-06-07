<?php

return [
    'required' => 'The :attribute field is required.',
    'date' => 'The :attribute field must be a valid date.',
    'email' => 'The :attribute field must be a valid email address.',
    'enum' => 'The selected :attribute is invalid.',
    'min' => [
        'array' => 'The :attribute field must have at least :min item.|The :attribute field must have at least :min items.',
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'max' => [
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'string' => 'The :attribute field must be a string.',
    'unique' => 'The :attribute has already been taken.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'after_or_equal' => 'The :attribute field must be a date after or equal to :date.',
    'before_or_equal' => 'The :attribute field must be a date before or equal to :date.',
];
