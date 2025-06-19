<?php
function formatNumberWithCommas($number) {
    return number_format($number, 0, '.', ' ');
} 