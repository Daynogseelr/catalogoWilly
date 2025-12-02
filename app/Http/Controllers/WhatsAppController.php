<?php

namespace App\Http\Controllers;
use App\Models\Bill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function openWhatsAppChat($phone,$status)
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone); // Sanitize phone number
        $countryCode = '58'; // Replace with actual country code
        $formattedPhone = "+{$countryCode}{$phone}";
        $chatLink = "https://api.whatsapp.com/send?phone={$formattedPhone}";
        if ($status=='PENDIENTE') {
            $message='Hola, gracias por visitarnos en https://telematicstech.net/indexStore/TechnoWu, nuestros agentes están evaluando su pedido.';
        } else if ($status=='APROBADO') {
            $message='Hola, gracias por visitarnos en https://telematicstech.net/indexStore/TechnoWu, su pedido ha sido aprobado.';
        } else if ($status=='FINALIZADO') {
            $message='Hola, gracias por visitarnos en https://telematicstech.net/indexStore/TechnoWu, estamos para brindar los mejores productos y servicios';
        } else if ($status=='INCONCLUSO') {
            $message='Hola, gracias por visitarnos en https://telematicstech.net/indexStore/TechnoWu, su pedido se ha cambiado a inconcluso por no culminar la compra.';
        } else if ($status=='RECHAZADO') {
            $message='Hola, gracias por visitarnos en https://telematicstech.net/indexStore/TechnoWu, disculpe su pedido ha sido rechazado, le invitamos a optar por otros productos.';
        }
        if ($message) {
            $sanitizedMessage = urlencode($message); // Sanitize and encode the message
            $chatLink .= "&text={$sanitizedMessage}";
        }
        return redirect($chatLink);
    }
    public function openWhatsAppChatClient($phone)
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone); // Sanitize phone number
        $countryCode = '58'; // Replace with actual country code
        $formattedPhone = "+{$countryCode}{$phone}";
        $chatLink = "https://api.whatsapp.com/send?phone={$formattedPhone}";
        $message='Hola, ';
        if ($message) {
            $sanitizedMessage = urlencode($message); // Sanitize and encode the message
            $chatLink .= "&text={$sanitizedMessage}";
        }
        return redirect($chatLink);
    }
    public function openWhatsAppChatService($phone,$status)
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone); // Sanitize phone number
        $countryCode = '58'; // Replace with actual country code
        $formattedPhone = "+{$countryCode}{$phone}";
        $chatLink = "https://api.whatsapp.com/send?phone={$formattedPhone}";
        if ($status=='RECIBIDO') {
            $message='Hola, visitarnos en www.digicatalogo.com, su equipo ha sido RECIBIDO.';
        } else if ($status=='REVISIÓN') {
            $message='Hola, visitarnos en www.digicatalogo.com, su equipo esta en REVISIÓN.';
        } else if ($status=='REVISADO') {
            $message='Hola, visitarnos en www.digicatalogo.com, su equipo ha sido REVISADO.';
        } else if ($status=='APROBADO') {
            $message='Hola, visitarnos en www.digicatalogo.com, su equipo se ha APROBADO para su reparacion.';
        } else if ($status=='RECHAZADO') {
            $message='Hola, visitarnos en www.digicatalogo.com, gracias por preferirnos.';
        } else if ($status=='TERMINADO') {
            $message='Hola, visitarnos en www.digicatalogo.com, gracias por preferirnos.';
        } else if ($status=='ENTREGADO') {
            $message='Hola, visitarnos en www.digicatalogo.com, gracias por preferirnos.';
        }
        if ($message) {
            $sanitizedMessage = urlencode($message); // Sanitize and encode the message
            $chatLink .= "&text={$sanitizedMessage}";
        }
        return redirect($chatLink);
    }
    public function openWhatsAppChatCredit($phone, $id_client)
    {
        // --- Consulta de créditos y cálculo de amount y días ---
        $creditBills = Bill::where('id_client', $id_client)
            ->where('type', 'CREDITO')
            ->where('payment', '>', 0) // Consider "payment" as the outstanding credit amount for type 'credit'
            ->get();
        $amount = $creditBills->sum('payment'); // Suma todos los 'payment' de los créditos
        $message = '';
        $dias = 0; // Initialize days
        if ($creditBills->isNotEmpty()) {
            // Find the oldest credit to calculate days from its creation date
            $oldestCredit = $creditBills->sortBy('created_at')->first();
            $createdAt = Carbon::parse($oldestCredit->created_at);
            $creditDays = $oldestCredit->creditDays ?? 0; // Get creditDays, default to 0 if null
            $dueDate = $createdAt->addDays($creditDays);
            $now = Carbon::now();
            if ($now->greaterThan($dueDate)) {
                // Crédito vencido
                $dias = floor($now->diffInDays($dueDate));
                $message = 'Hola, usted tiene crédito pendiente vencido hace ' . $dias . ' días por un monto de ' . number_format($amount, 2, ',', '.') . ' $.';
            } else {
                // Crédito por vencer
                $dias = floor($now->diffInDays($dueDate));
                $message = 'Hola, usted tiene crédito pendiente que se vencerá en ' . $dias . ' días por un monto de ' . number_format($amount, 2, ',', '.') . ' $.';
            }
        } else {
            // No hay créditos pendientes para el cliente y compañía. Puedes ajustar este mensaje si lo necesitas.
            $message = 'Hola, actualmente no tiene créditos pendientes con nosotros.';
        }
        // --- Fin de consulta y cálculo ---
        $phone = preg_replace('/[^0-9+]/', '', $phone); // Sanitize phone number
        $countryCode = '58'; // Replace with actual country code for Venezuela
        $formattedPhone = "{$countryCode}{$phone}"; // WhatsApp API expects phone without leading + for country code
        $chatLink = "https://api.whatsapp.com/send?phone={$formattedPhone}";
        if ($message) {
            $sanitizedMessage = urlencode($message); // Sanitize and encode the message
            $chatLink .= "&text={$sanitizedMessage}";
        }
        return redirect($chatLink);
    } 
}