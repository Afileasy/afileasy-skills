<?php

require __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Client;

$asaas = new Client([
    'base_uri' => 'https://api.asaas.com/v3/',
    'headers' => ['access_token' => getenv('ASAAS_API_KEY'), 'Content-Type' => 'application/json'],
]);

$orderId = 'PED-'.date('YmdHis');

$customer = json_decode($asaas->post('customers', ['json' => [
    'name' => $_POST['name'],
    'email' => $_POST['email'],
    'cpfCnpj' => $_POST['cpf'],
]])->getBody(), true);

$payment = json_decode($asaas->post('payments', ['json' => [
    'customer' => $customer['id'],
    'billingType' => 'PIX',
    'value' => 149.90,
    'dueDate' => date('Y-m-d', strtotime('+1 day')),
    'description' => 'Kit Bordado Iniciante',
    'externalReference' => $orderId,
]])->getBody(), true);

header('Location: '.$payment['invoiceUrl']);
