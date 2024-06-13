<?php

return [
    [
        [
            'ruleName' => 'conditional_exist',
            'extend' => "\Domains\Shared\Rules\ValidationAttributes@validateConditionalExist",
            'replacer' => "\Domains\Shared\Rules\ValidationAttributes@validateConditionalExist"
        ]
    ]
];
