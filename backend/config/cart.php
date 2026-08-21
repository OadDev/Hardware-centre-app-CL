<?php

return [
    // How long a cart item can sit untouched before it counts as
    // abandoned. Kept in the middle of the 30-60 minute range asked for.
    'abandoned_after_minutes' => env('CART_ABANDONED_AFTER_MINUTES', 30),
];
