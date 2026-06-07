<?php

return [
    'attributes' => [
        'destination' => 'destino',
        'start_date' => 'data de início',
        'end_date' => 'data de fim',
        'travelers' => 'viajantes',
        'travelers.*.name' => 'nome do viajante',
        'travelers.*.birth_date' => 'data de nascimento',
        'travelers.*.add_ons' => 'adicionais',
    ],
    'messages' => [
        'start_date.after_or_equal' => 'A data de início deve ser hoje ou uma data futura.',
        'end_date.after_or_equal' => 'A data de fim deve ser igual ou posterior à data de início.',
    ],
];
