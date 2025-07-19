<?php
if (function_exists('gd_info')) {
    echo "GD library is installed and enabled.";
    print_r(gd_info());
} else {
    echo "GD library is NOT installed or enabled.";
}
?>