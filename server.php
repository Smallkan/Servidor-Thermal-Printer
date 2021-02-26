<?php

/*
    Servidor de conexão WebSocket
    Versão 1.0.0
    Desenvolvimento Jhol H
*/

error_reporting(E_ALL ^ E_WARNING);

use ClubeASC\ThermalPrinter\ClubeASCThermalPrinter;

require_once __DIR__ . '/vendor/autoload.php';

try {

    echo "\n", '> Iniciando servidor Clube ASC para impressão...', "\n\n";

    $websocket = new Hoa\Websocket\Server(
        new Hoa\Socket\Server('ws://127.0.0.1:6441')
    );

    $websocket->on('open', function (Hoa\Event\Bucket $bucket) {
        echo '> Conexão estabelecida.', "\n\n";
        return;
    });

    $websocket->on('message', function (Hoa\Event\Bucket $bucket) {

        $data = $bucket->getData();
        $printData = json_decode($data['message']);

        if (!isset($printData->data->printerData->connector_descriptor) || empty($printData->data->printerData->connector_descriptor)) {
            echo '> As configurações da impressora estão incorretas...', "\n\n";
            return;

        } else {

            $connector_type = $printData->data->printerData->connector_type;
            $connector_descriptor = $printData->data->printerData->connector_descriptor;
            try {

                $printer = new ClubeASCThermalPrinter();
                $printer->load($connector_type, $connector_descriptor);
                $printer->printInvoice($printData->data);

                if ($printData->data->print_type == null) {

                    //Se nulo, então imprime para ambos (dependendo se o KIT está habilitado ou não)
                    echo '---------------------------------------------------------', "\n";
                    echo '| Número do pedido: ' . $printData->data->order->unique_pedido_id . ' |', "\n";

                    if (empty($printData->data->printerData->print_kot)) {
                        echo '---------------------------------------------------------', "\n\n";
                    } else {
                        echo '---------------------------------------------------------', "\n";
                        echo '| KOT númedo do Pedido: ' . $printData->data->order->unique_pedido_id . ' |', "\n";
                        echo '-----------------------------------------------------', "\n\n";
                    }

                } elseif ($printData->data->print_type == 'invoice') {

                    echo '---------------------------------------------------------', "\n";
                    echo '| Número do pedido: ' . $printData->data->order->unique_pedido_id . ' |', "\n";
                    echo '---------------------------------------------------------', "\n\n";

                } elseif ($printData->data->print_type == 'kot') {

                    echo '-----------------------------------------------------', "\n";
                    echo '| KOT númedo do Pedido: ' . $printData->data->order->unique_pedido_id . ' |', "\n";
                    echo '-----------------------------------------------------', "\n\n";

                }

            } catch (Exception $e) {
                $bucket->getSource()->send($e->getMessage());
                echo '> Ocorreu um erro, não é possível imprimir. ', $e->getMessage(), "\n\n";
            }
            return;
        }
        return;

    });

    $websocket->on('close', function (Hoa\Event\Bucket $bucket) {
        echo '> Desconectado.', "\n\n";
        return;
    });

    try {
        echo '> Servidor iniciado.', "\n\n";
        $websocket->run();
    } catch (Exception $e) {
        echo '> Algo deu errado :( ', $e->getMessage(), "\n\n";
    }

} catch (Exception $e) {
    echo '> Error: ', $e->getMessage(), "\n\n";
}
