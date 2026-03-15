<?php
// shared helper functions, included by processor files that need them

// trims whitespace, strips backslashes, and encodes special chars
function cleanInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
