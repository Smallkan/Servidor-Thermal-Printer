<?php

namespace ClubeASC\ThermalPrinter;

use Carbon\Carbon;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class ClubeASCThermalPrinter {

    public $printer;

    public function load($connector_type, $connector_descriptor, $connector_port = 9100)
    {
        switch (strtolower($connector_type)) {
            case 'cups':
                $connector = new CupsPrintConnector($connector_descriptor);
                break;
            case 'windows':
                $connector = new WindowsPrintConnector($connector_descriptor);
                break;
            case 'network':
                $connector = new NetworkPrintConnector($connector_descriptor);
                break;
        }

        if ($connector) {

            $profile = CapabilityProfile::load('default');
            $this->printer = new Printer($connector, $profile);
        } else {
            throw new \Exception('Tipo de conector de impressora inválido. Os valores aceitos são: xícaras');
        }
    }

    /**
     * @param $data
    */

    public function printInvoice($data)
    {
        if ($data->print_type == null) {

            $this->invoicePrinting($data);
            $this->kotPrinting($data);

        } elseif ($data->print_type == 'invoice') {

            $this->invoicePrinting($data);

        } elseif ($data->print_type == 'kot') {

            $this->kotPrinting($data);

        }
    }

    /**
     * @param $data
    */

    private function invoicePrinting($data)
    {
        $store_name = $data->order->estabelecimento->nome_fantasia;
        $store_address = $data->order->estabelecimento->logradouro;
        $order_id = $data->order->unique_pedido_id;

        $this->printer->setJustification(Printer::JUSTIFY_CENTER);

        if (!empty($data->adminData->invoice_title)) {
            $this->printer->feed();
            $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $this->printer->text($data->adminData->invoice_title);
            $this->printer->selectPrintMode();
            $this->printer->feed();
        }

        if (!empty($data->adminData->invoice_subtitle)) {
            $this->printer->selectPrintMode(Printer::MODE_FONT_B);
            $this->printer->text($data->adminData->invoice_subtitle);
            $this->printer->selectPrintMode();
            $this->printer->feed();
        }

        if (!empty($data->adminData->invoice_title) || !empty($data->adminData->invoice_subtitle)) {
            $this->printer->text($this->drawLine($data->char_per_line));
        }

        $this->printer->feed();

        if (!empty($data->printerData->show_store_name)) {
            $this->printer->setEmphasis(true);
            $this->printer->setUnderline(1);
            $this->printer->text($store_name);
            $this->printer->setUnderline(0);
            $this->printer->setEmphasis(false);
            $this->printer->feed();
        }

        if (!empty($data->printerData->show_store_address)) {
            $this->printer->text($store_address);
            $this->printer->feed();
        }

        if (!empty($data->printerData->show_order_id)) {
            $this->printer->text(!empty($data->printerData->order_id_label) ? $data->printerData->order_id_label . ' ' . $order_id : 'Pedido ID: ' . $order_id);
            $this->printer->feed();
        }

        if (!empty($data->printerData->show_order_date)) {
            $created_at = Carbon::parse($data->order->created_at);
            $this->printer->text(!empty($data->printerData->order_date_label) ? $data->printerData->order_date_label . ' ' . $created_at->format('d/m/Y h:i A') : 'Data do pedido: ' . $created_at->format('d/m/Y h:i A'));
            $this->printer->feed();
        }

        $this->printer->feed();

        /* Customer Details */
        if (!empty($data->printerData->customer_details_title)) {
            $this->printer->setEmphasis(true);
            $this->printer->setUnderline(1);
            $this->printer->text($data->printerData->customer_details_title);
            $this->printer->setUnderline(0);
            $this->printer->setEmphasis(false);
            $this->printer->feed();
        }

        if (!empty($data->printerData->show_customer_name)) {
            $this->printer->text($data->order->usuario->nome);
            $this->printer->feed();
        }

        if (!empty($data->printerData->show_customer_phone)) {
            $this->printer->text($data->order->usuario->telefone);
            $this->printer->feed();
        }

        if (!empty($data->printerData->show_delivery_type)) {
            $this->printer->setEmphasis(true);
            //delivery order
            if ($data->order->tipo_entrega == 1) {
                $this->printer->text(empty($data->printerData->delivery_label) ? 'ENTREGA' : $data->printerData->delivery_label);
            } else {
                //selfpickup order
                $this->printer->text(empty($data->printerData->selfpickup_label) ? 'COLETA' : $data->printerData->selfpickup_label);
            }
            $this->printer->feed();
        }

        if (!empty($data->printerData->show_delivery_address) && $data->order->tipo_entrega == 1) {
            $this->printer->text($data->order->endereco);
            $this->printer->feed();
        }

        $this->printer->setEmphasis(false);
        $this->printer->feed();
        /* END Customer Details */

        $this->printer->setJustification();

        $this->printer->setJustification(Printer::JUSTIFY_LEFT);

        //bill item header
        $this->printer->text($this->drawLine($data->char_per_line));
        $string = $this->columnify($this->columnify($this->columnify(!empty($data->printerData->quantity_label) ? $data->printerData->quantity_label : 'QTD', ' ' . !empty($data->printerData->item_label) ? $data->printerData->item_label : 'ITENS', 12, 40, 0, 0, $data->char_per_line), !empty($data->printerData->price_label) ? $data->printerData->price_label : 'PREÇO', 55, 20, 0, 0, $data->char_per_line), ' ' . !empty($data->printerData->total_label) ? $data->printerData->total_label : 'TOTAL', 75, 25, 0, 0, $data->char_per_line);
        $this->printer->setEmphasis(true);
        $this->printer->text(rtrim($string));
        $this->printer->feed();
        $this->printer->setEmphasis(false);
        $this->printer->text($this->drawLine($data->char_per_line));

        // foreach ($data->order->orderitems as $orderitem) {

        //     $itemTotal = ($orderitem->price + $this->calculateAddonTotal($orderitem->order_item_addons)) * $orderitem->quantity;

        //     $orderItemAddons = count($orderitem->order_item_addons);
        //     if ($orderItemAddons > 0) {
        //         $addons = '';
        //         foreach ($orderitem->order_item_addons as $addon) {
        //             $addons .= $addon->addon_name . ', ';
        //         }
        //         $addons = rtrim($addons, ', ');
        //         $orderitem->addon_name = $addons;
        //     }

        //     // //print products/items
        //     if ($orderItemAddons > 0) {
        //         $string = rtrim($this->columnify($this->columnify($this->columnify($orderitem->quantity, $orderitem->name . ' (' . $orderitem->addon_name . ')', 12, 40, 0, 0, $data->char_per_line), floatval($orderitem->price), 55, 20, 0, 0, $data->char_per_line), floatval($itemTotal), 75, 25, 0, 0, $data->char_per_line));
        //     } else {
        //         $string = rtrim($this->columnify($this->columnify($this->columnify($orderitem->quantity, $orderitem->name, 12, 40, 0, 0, $data->char_per_line), floatval($orderitem->price), 55, 20, 0, 0, $data->char_per_line), floatval($itemTotal), 75, 25, 0, 0, $data->char_per_line));
        //     }

        //     $this->printer->text($string);
        //     $this->printer->feed();

        // }

        $this->printer->feed();
        $this->printer->text($this->drawLine($data->char_per_line));

        $this->printer->setJustification(Printer::JUSTIFY_LEFT);

        //delivery charge
        $deliveryCharge = $this->columnify($data->printerData->delivery_charge_label . ' ', floatval($data->order->taxa_entrega), 75, 25, 0, 0, $data->char_per_line);
        $this->printer->text(rtrim($deliveryCharge));
        $this->printer->feed();

        //Order Total

        $this->printer->setJustification(Printer::JUSTIFY_CENTER);
        $this->printer->text($this->drawLine($data->char_per_line));
        $this->printer->setJustification();

        $orderTotal = $this->columnify($data->printerData->total_label . ' ', floatval($data->order->total), 75, 25, 0, 0, $data->char_per_line);
        $this->printer->setEmphasis(true);
        $this->printer->text(rtrim($orderTotal));
        $this->printer->setEmphasis(false);
        $this->printer->feed();

        $this->printer->setJustification();

        $this->printer->setJustification(Printer::JUSTIFY_CENTER);
        $this->printer->text($this->drawLine($data->char_per_line));
        $this->printer->setJustification();

        //admin footer
        if (!empty($data->adminData->footer_title)) {
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->feed();
            $this->printer->setUnderline(1);
            $this->printer->text($data->adminData->footer_title);
            $this->printer->setUnderline(0);
            $this->printer->feed();
            $this->printer->setJustification();
        }

        if (!empty($data->adminData->footer_sub_title)) {
            //break lines in new array
            $subFooters = preg_split("/\r\n|\n|\r/", $data->adminData->footer_sub_title);

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->feed();
            foreach ($subFooters as $subFooter) {
                $this->printer->text($subFooter);
                $this->printer->feed();
            }
            $this->printer->setJustification();
        }

        $this->printer->feed();

        //store footer
        if (!empty($data->printerData->store_footer_title)) {
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->feed();
            $this->printer->setUnderline(1);
            $this->printer->text($data->printerData->store_footer_title);
            $this->printer->setUnderline(0);
            $this->printer->feed();
            $this->printer->setJustification();
        }

        if (!empty($data->printerData->store_footer_subtitle)) {
            //break lines in new array
            $subFooters = preg_split("/\r\n|\n|\r/", $data->printerData->store_footer_subtitle);

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->feed();
            foreach ($subFooters as $subFooter) {
                $this->printer->text($subFooter);
                $this->printer->feed();
            }
            $this->printer->setJustification();
        }

        $this->printer->feed();

        //cut receipt
        $this->printer->cut();

        // close connection if kot not required
        if (empty($data->printerData->print_kot) || $data->print_type == 'invoice') {
            $this->printer->close();
        }
    }

    /**
     * @param $data
     */
    private function kotPrinting($data)
    {
        $store_name = $data->order->restaurant->name;
        $store_address = $data->order->restaurant->address;
        $order_id = $data->order->unique_order_id;

        /* KOT Printing */
        if (!empty($data->printerData->print_kot)) {

            $this->printer->setJustification(Printer::JUSTIFY_CENTER);

            $this->printer->text($data->order->user->name);
            $this->printer->feed();

            $this->printer->text(!empty($data->printerData->order_id_label) ? $data->printerData->order_id_label . ' ' . $order_id : 'N° do Pedido: ' . $order_id);
            $this->printer->feed();
            $created_at = Carbon::parse($data->order->created_at);
            $this->printer->text(!empty($data->printerData->order_date_label) ? $data->printerData->order_date_label . ' ' . $created_at->format('Y-m-d h:i A') : 'Data do Pedido: ' . $created_at->format('Y-m-d h:i A'));
            $this->printer->feed();

            $this->printer->setEmphasis(true);
            //delivery order
            if ($data->order->delivery_type == 1) {
                $this->printer->setUnderline(1);
                $this->printer->text(empty($data->printerData->delivery_label) ? 'ENTREGA' : $data->printerData->delivery_label);
                $this->printer->setUnderline(0);
            } else {
                //selfpickup order
                $this->printer->setUnderline(1);
                $this->printer->text(empty($data->printerData->selfpickup_label) ? 'COLETA' : $data->printerData->selfpickup_label);
                $this->printer->setUnderline(0);
            }
            $this->printer->feed();

            $this->printer->setJustification();

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);

            //bill item header
            $this->printer->text($this->drawLine($data->char_per_line));
            $string = $this->columnify(!empty($data->printerData->quantity_label) ? $data->printerData->quantity_label : 'QTD', ' ' . !empty($data->printerData->item_label) ? $data->printerData->item_label : 'ITENS', 15, 80, 0, 0, $data->char_per_line);
            $this->printer->setEmphasis(true);
            $this->printer->text(rtrim($string));
            $this->printer->feed();
            $this->printer->setEmphasis(false);
            $this->printer->text($this->drawLine($data->char_per_line));

            // foreach ($data->order->orderitems as $orderitem) {

            //     $itemTotal = ($orderitem->price + $this->calculateAddonTotal($orderitem->order_item_addons)) * $orderitem->quantity;

            //     //get addons and add to orderitem->addon_name
            //     $orderItemAddons = count($orderitem->order_item_addons);
            //     if ($orderItemAddons > 0) {
            //         $addons = '';
            //         foreach ($orderitem->order_item_addons as $addon) {
            //             $addons .= $addon->addon_name . ', ';
            //         }
            //         $addons = rtrim($addons, ', ');
            //         $orderitem->addon_name = $addons;
            //     }

            //     // //print products/items
            //     if ($orderItemAddons > 0) {
            //         $string = rtrim($this->columnify($orderitem->quantity, $orderitem->name, 15, 80, 0, 0, $data->char_per_line));

            //         $this->printer->text($string);
            //         $this->printer->feed();
            //         $this->printer->setReverseColors(true);
            //         $addons = rtrim($this->columnify('', $orderitem->addon_name, 0, 100, 0, 0, $data->char_per_line));
            //         $this->printer->text($addons . ' ');
            //         $this->printer->setReverseColors(false);
            //         $this->printer->feed();

            //     } else {
            //         $string = rtrim($this->columnify($orderitem->quantity, $orderitem->name, 15, 100, 0, 0, $data->char_per_line));
            //         $this->printer->text($string);
            //         $this->printer->feed();
            //     }
            //     $this->printer->feed();

            // }

            $this->printer->text($this->drawLine($data->char_per_line));

            $this->printer->setJustification();
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);

            $this->printer->text(Carbon::now($data->timezone)->format('Y-m-d h:i A'));
            $this->printer->feed();
            $this->printer->feed();

            $this->printer->cut();
            $this->printer->close();
        }
        /*END KOT Printing */
    }

    /**
     * @param $char_per_line
     * @return mixed
     */
    public function drawLine($char_per_line)
    {
        $new = '';
        for ($i = 1; $i < $char_per_line; $i++) {
            $new .= '-';
        }
        return $new . "\n";
    }

    /**
     * @param $addons
     * @return mixed
     */
    public function calculateAddonTotal($addons)
    {
        $total = 0;
        foreach ($addons as $addon) {
            $total += $addon->addon_price;
        }
        return $total;
    }

    /**
     * @param $leftCol
     * @param $rightCol
     * @param $leftWidthPercent
     * @param $rightWidthPercent
     * @param $space
     * @param $remove_for_space
     * @param $char_per_line
     */
    public function columnify($leftCol, $rightCol, $leftWidthPercent, $rightWidthPercent, $space = 2, $remove_for_space = 0, $char_per_line)
    {
        $char_per_line = $char_per_line - $remove_for_space;

        $leftWidth = $char_per_line * $leftWidthPercent / 100;
        $rightWidth = $char_per_line * $rightWidthPercent / 100;

        $leftWrapped = wordwrap($leftCol, $leftWidth, "\n", true);
        $rightWrapped = wordwrap($rightCol, $rightWidth, "\n", true);

        $leftLines = explode("\n", $leftWrapped);
        $rightLines = explode("\n", $rightWrapped);
        $allLines = array();
        for ($i = 0; $i < max(count($leftLines), count($rightLines)); $i++) {
            $leftPart = str_pad(isset($leftLines[$i]) ? $leftLines[$i] : '', $leftWidth, ' ');
            $rightPart = str_pad(isset($rightLines[$i]) ? $rightLines[$i] : '', $rightWidth, ' ');
            $allLines[] = $leftPart . str_repeat(' ', $space) . $rightPart;
        }
        return implode($allLines, "\n") . "\n";
    }

}