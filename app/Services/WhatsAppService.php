<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
    }

    /**
     * Obtiene el estado actual de la conexión de WhatsApp.
     */
    public function getStatus(): array
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/status");
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            Log::warning("WhatsApp Service no responde: " . $e->getMessage());
        }

        return [
            'success' => false,
            'status' => 'offline',
            'connected' => false,
            'message' => 'El microservicio local de WhatsApp no está en ejecución.'
        ];
    }

    /**
     * Obtiene el código QR actual en base64 para vincular la cuenta.
     */
    public function getQr(): array
    {
        try {
            $response = Http::timeout(4)->get("{$this->baseUrl}/qr");
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            Log::warning("Error obteniendo QR de WhatsApp: " . $e->getMessage());
        }

        return [
            'success' => false,
            'status' => 'offline',
            'message' => 'No se pudo conectar con el microservicio de WhatsApp.'
        ];
    }

    /**
     * Envía un mensaje directo en segundo plano a través de Baileys.
     */
    public function sendMessage(string $phone, string $message): array
    {
        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/send", [
                'phone' => $phone,
                'message' => $message
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'error' => $response->json('error') ?? 'Error devuelto por el microservicio de WhatsApp.'
            ];
        } catch (\Throwable $e) {
            Log::error("Fallo al despachar mensaje WhatsApp: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'No se pudo comunicar con el servicio de WhatsApp en segundo plano.'
            ];
        }
    }

    /**
     * Desconecta la sesión activa de WhatsApp.
     */
    public function disconnect(): array
    {
        try {
            $response = Http::timeout(5)->post("{$this->baseUrl}/disconnect");
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {}

        return ['success' => false, 'error' => 'No se pudo desconectar la sesión.'];
    }
}
