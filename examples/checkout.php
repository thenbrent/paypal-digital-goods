<?php
require_once( 'functions.php' );

header('Location: ' . $paypal->get_checkout_url() ); // get_checkout_url() also requests a token
exit();