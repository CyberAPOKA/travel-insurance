<?php

return [
    'attributes' => [
        'destination' => 'destination',
        'start_date' => 'start date',
        'end_date' => 'end date',
        'travelers' => 'travelers',
        'travelers.*.name' => 'traveler name',
        'travelers.*.birth_date' => 'birth date',
        'travelers.*.add_ons' => 'add-ons',
    ],
    'messages' => [
        'start_date.after_or_equal' => 'The start date must be today or a future date.',
        'end_date.after_or_equal' => 'The end date must be on or after the start date.',
    ],
];
