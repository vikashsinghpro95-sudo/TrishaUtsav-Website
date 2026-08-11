<?php
if (file_exists('error_log')) {
    echo file_get_contents('error_log');
} else {
    echo "No error_log found.";
}
